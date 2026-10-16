<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{

    public function show()
    {
        $contractors = User::where('role', 'contractor')->orderBy('name')->get();
        return view('manager.import', compact('contractors'));
    }

    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['error' => 'Please upload a .xlsx, .xls, or .csv file.'], 422);
        }

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cannot read file: ' . $e->getMessage()], 422);
        }

        if (empty($rows)) {
            return response()->json(['error' => 'File is empty.'], 422);
        }

        $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);
        $fieldMap = [
            'phone'    => ['phone', 'mobile', 'phone number'],
            'name'     => ['name', 'customer name', 'full name'],
            'email'    => ['email', 'email address'],
            'joined'   => ['joining date', 'joined on', 'date', 'registration date'],
            'location' => ['location', 'city', 'region'],
            'status'   => ['status', 'account status'],
            'orders'   => ['total orders', 'orders'],
            'order_id' => ['order id', 'source order id'],
            'product'  => ['product name', 'product'],
        ];

        $colIndex = [];
        foreach ($fieldMap as $key => $variants) {
            foreach ($variants as $v) {
                $idx = array_search($v, $headers);
                if ($idx !== false) { $colIndex[$key] = $idx; break; }
            }
        }

        $staged   = [];
        $seen     = [];
        $invalid  = 0;
        $dupes    = 0;
        $lastIdx  = null;

        foreach (array_slice($rows, 1) as $row) {
            $rawPhone = trim((string)($row[$colIndex['phone'] ?? -1] ?? ''));
            $orderId  = trim((string)($row[$colIndex['order_id'] ?? -1] ?? ''));
            if (strcasecmp($orderId, 'no orders') === 0 || strcasecmp($orderId, 'no order') === 0) {
                $orderId = '';
            }
            $product  = trim((string)($row[$colIndex['product'] ?? -1] ?? ''));

            // Check if this is a continuation row for the preceding customer (e.g. multiple items/orders)
            if ($rawPhone === '' && $lastIdx !== null && ($orderId !== '' || $product !== '')) {
                if ($product !== '') {
                    $existingProd = $staged[$lastIdx]['product'];
                    $mergedProd = $existingProd ? "$existingProd | $product" : $product;
                    $staged[$lastIdx]['product'] = mb_substr($mergedProd, 0, 255);
                }
                if ($orderId !== '') {
                    $existingOrder = $staged[$lastIdx]['order_id'];
                    if (!str_contains($existingOrder, $orderId)) {
                        $mergedOrder = $existingOrder ? "$existingOrder, $orderId" : $orderId;
                        $staged[$lastIdx]['order_id'] = mb_substr($mergedOrder, 0, 255);
                    }
                }
                continue;
            }

            $phone = Customer::normalizePhone($rawPhone);

            if (!$phone) {
                // Ignore completely empty rows silently; count populated rows with invalid phone
                $hasData = !empty(array_filter($row, fn($v) => trim((string)$v) !== ''));
                if ($hasData) {
                    $invalid++;
                }
                continue;
            }

            if (isset($seen[$phone])) {
                $dupes++;
                continue;
            }
            $seen[$phone] = true;

            $location = trim((string)($row[$colIndex['location'] ?? -1] ?? ''));
            if ($location === '-') {
                $location = '';
            }

            $staged[] = [
                'phone'    => $phone,
                'name'     => trim((string)($row[$colIndex['name'] ?? -1] ?? '')),
                'email'    => trim((string)($row[$colIndex['email'] ?? -1] ?? '')),
                'joined'   => $this->parseDate($row[$colIndex['joined'] ?? -1] ?? ''),
                'location' => $location,
                'status'   => trim((string)($row[$colIndex['status'] ?? -1] ?? '')),
                'orders'   => max(0, (int)($row[$colIndex['orders'] ?? -1] ?? 0)),
                'order_id' => mb_substr($orderId, 0, 255),
                'product'  => mb_substr($product, 0, 255),
            ];
            $lastIdx = count($staged) - 1;
        }

        // Store in session for the actual import
        $fileName = $request->file('file')->getClientOriginalName();
        session(['import_staged' => $staged, 'import_file' => $fileName]);

        return response()->json([
            'total'   => count($rows) - 1,
            'valid'   => count($staged),
            'invalid' => $invalid,
            'dupes'   => $dupes,
            'preview' => array_slice($staged, 0, 5),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $staged = session('import_staged', []);
        if (empty($staged)) {
            return response()->json(['error' => 'No staged data. Upload a file first.'], 422);
        }

        $assignee = $request->assigned_to;
        $added    = 0;
        $skipped  = 0;
        $failed   = 0;

        foreach ($staged as $c) {
            try {
                $existing = Customer::where('phone', $c['phone'])->exists();
                if ($existing) { $skipped++; continue; }

                Customer::create([
                    'phone'           => $c['phone'],
                    'name'            => $c['name'] ?: null,
                    'email'           => $c['email'] ?: null,
                    'joined_on'       => $c['joined'] ?: null,
                    'location'        => $c['location'] ?: null,
                    'account_status'  => $c['status'] ?: null,
                    'total_orders'    => $c['orders'],
                    'source_order_id' => $c['order_id'] ?: null,
                    'product_name'    => $c['product'] ?: null,
                    'assigned_to'     => $assignee,
                    'activation_status' => 'not_called',
                ]);
                $added++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        session()->forget(['import_staged', 'import_file']);

        return response()->json([
            'added'   => $added,
            'skipped' => $skipped,
            'failed'  => $failed,
            'message' => "Complete: $added new, $skipped existing, $failed errors.",
        ]);
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;
        if (is_numeric($value)) {
            // Excel serial date
            $days = (int)$value - 25569;
            return date('Y-m-d', $days * 86400);
        }
        $d = date_create((string)$value);
        return $d ? date_format($d, 'Y-m-d') : null;
    }
}
