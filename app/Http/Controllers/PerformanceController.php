<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CallLog;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $assigned  = $user->assignedCustomers()->count();
        $contacted = $user->assignedCustomers()->whereNotIn('activation_status', ['not_called'])->count();
        $orders    = $user->assignedCustomers()->whereNotNull('verified_order_id')->count();
        $delivered = $user->assignedCustomers()->where('delivered', true)->count();

        $contactRate  = $assigned > 0 ? round($contacted / $assigned * 100, 1) : 0;
        $convRate     = $assigned > 0 ? round($orders / $assigned * 100, 1) : 0;
        $deliveryRate = $orders > 0 ? round($delivered / $orders * 100, 1) : 0;

        // Call results for donut chart
        $callResults = CallLog::where('contractor_id', $user->id)
            ->selectRaw('outcome, COUNT(*) as count')
            ->groupBy('outcome')->get()
            ->pluck('count', 'outcome');

        // Daily activity last 7 days
        $dailyActivity = CallLog::where('contractor_id', $user->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as calls')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')->orderBy('date')->get();

        return view('contractor.performance', compact(
            'assigned','contacted','orders','delivered',
            'contactRate','convRate','deliveryRate',
            'callResults','dailyActivity'
        ));
    }
}
