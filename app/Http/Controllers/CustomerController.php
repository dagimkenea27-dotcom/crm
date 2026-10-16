<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CallLog;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Customer::with('assignedTo');

        // Contractors only see their own customers
        if ($user->isContractor()) {
            $query->where('assigned_to', $user->id);
        }

        // Filters
        $filter = $request->get('filter', 'all');
        if ($filter === 'queue') {
            $query->where('total_orders', 0)
                  ->whereNotIn('activation_status', ['do_not_contact', 'verified_order']);
        } elseif ($filter !== 'all') {
            $query->where('activation_status', $filter);
        }

        if ($tab = $request->get('tab')) {
            match ($tab) {
                'not_called'  => $query->where('activation_status', 'not_called'),
                'followup'    => $query->whereIn('activation_status', ['callback', 'not_ready', 'interested']),
                'contacted'   => $query->whereNotIn('activation_status', ['not_called']),
                'first_order' => $query->whereNotNull('verified_order_id'),
                default       => null,
            };
        }

        if ($contractor = $request->get('contractor')) {
            $query->where('assigned_to', $contractor);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                   ->orWhere('phone', 'like', "%$q%");
            });
        }

        // Sort: follow_up due first, then not_called, then rest
        $query->orderByRaw("CASE
            WHEN follow_up_on <= CURDATE() THEN 0
            WHEN activation_status = 'not_called' THEN 1
            ELSE 2
        END")
        ->orderBy('follow_up_on');

        $customers   = $query->paginate(50)->withQueryString();
        $contractors = User::where('role', 'contractor')->get();

        if ($request->wantsJson()) {
            return response()->json($customers);
        }

        return view('customers.index', compact('customers', 'contractors', 'filter'));
    }

    public function show(Customer $customer)
    {
        $this->authorizeAccess($customer);
        $callLogs = $customer->callLogs()->with('contractor')
            ->orderByDesc('created_at')->limit(30)->get();

        if (request()->wantsJson()) {
            return response()->json([
                'customer' => $customer->load('assignedTo'),
                'call_logs' => $callLogs,
            ]);
        }

        return view('customers.show', compact('customer', 'callLogs'));
    }

    public function recordCall(Request $request, Customer $customer)
    {
        $this->authorizeAccess($customer);
        $user = auth()->user();

        if (!$customer->isCallable() && !$user->isManager()) {
            return back()->withErrors(['Customer is not eligible for calls.']);
        }

        $data = $request->validate([
            'outcome'      => 'required|in:no_answer,interested,not_ready,callback,ordered_unverified,do_not_contact',
            'barrier'      => 'nullable|string',
            'coupon'       => 'boolean',
            'follow_up_on' => 'nullable|date',
            'notes'        => 'nullable|string|max:1500',
        ]);

        $followupStatuses = ['callback', 'interested', 'not_ready', 'no_answer'];

        $customer->update([
            'activation_status' => $data['outcome'],
            'barrier'           => $data['barrier'] ?? null,
            'coupon_offered'    => $customer->coupon_offered || ($data['coupon'] ?? false),
            'follow_up_on'      => in_array($data['outcome'], $followupStatuses) ? ($data['follow_up_on'] ?? null) : null,
            'notes'             => $data['notes'] ?? null,
        ]);

        CallLog::create([
            'customer_id'   => $customer->id,
            'contractor_id' => $user->id,
            'outcome'       => $data['outcome'],
            'barrier'       => $data['barrier'] ?? null,
            'coupon_offered'=> $data['coupon'] ?? false,
            'follow_up_on'  => $data['follow_up_on'] ?? null,
            'notes'         => $data['notes'] ?? null,
        ]);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Call saved successfully.']);
        }

        return back()->with('success', 'Call saved.');
    }

    public function verify(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()->isManager(), 403);

        $data = $request->validate([
            'verified_order_id' => 'nullable|string',
            'coupon_redeemed'   => 'boolean',
            'delivered'         => 'boolean',
        ]);

        $customer->update([
            'verified_order_id' => $data['verified_order_id'] ?? null,
            'coupon_redeemed'   => $data['coupon_redeemed'] ?? false,
            'delivered'         => $data['delivered'] ?? false,
            'activation_status' => $data['verified_order_id'] ? 'verified_order' : $customer->activation_status,
            'verified_order_at' => $data['verified_order_id'] ? ($customer->verified_order_at ?? now()) : null,
        ]);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Verification saved.']);
        }

        return back()->with('success', 'Verification saved.');
    }

    public function reassign(Request $request, Customer $customer)
    {
        abort_unless(auth()->user()->isManager(), 403);
        $request->validate(['assigned_to' => 'required|exists:users,id']);
        $customer->update(['assigned_to' => $request->assigned_to]);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Reassigned.']);
        }

        return back()->with('success', 'Customer reassigned.');
    }

    private function authorizeAccess(Customer $customer): void
    {
        $user = auth()->user();
        if ($user->isManager()) return;
        abort_unless($customer->assigned_to === $user->id, 403);
    }
}
