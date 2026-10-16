<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\CallLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isManager()) {
            return $this->managerDashboard();
        }

        return redirect()->route('contractor.customers');
    }

    private function managerDashboard()
    {
        $contractors = User::where('role', 'contractor')->get();

        // Top-level stats
        $totalAssigned  = Customer::count();
        $totalContacted = Customer::whereNotIn('activation_status', ['not_called'])->count();
        $firstOrders    = Customer::whereNotNull('verified_order_id')->count();
        $delivered      = Customer::where('delivered', true)->count();

        // Call results distribution
        $callResults = Customer::selectRaw('activation_status, COUNT(*) as count')
            ->groupBy('activation_status')->get()
            ->pluck('count', 'activation_status');

        // Performance by contractor
        $contractorPerf = $contractors->map(function ($c) {
            $assigned  = $c->assignedCustomers()->count();
            $contacted = $c->assignedCustomers()->whereNotIn('activation_status', ['not_called'])->count();
            $orders    = $c->assignedCustomers()->whereNotNull('verified_order_id')->count();
            $delivered = $c->assignedCustomers()->where('delivered', true)->count();
            $rate      = $assigned > 0 ? round($orders / $assigned * 100, 1) : 0;
            return [
                'name'      => $c->name,
                'assigned'  => $assigned,
                'contacted' => $contacted,
                'orders'    => $orders,
                'delivered' => $delivered,
                'rate'      => $rate,
            ];
        });

        // Daily activity last 7 days (for chart)
        $dailyActivity = CallLog::selectRaw('DATE(created_at) as date, COUNT(*) as calls')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')->orderBy('date')->get();

        // Recent first orders (pending verification)
        $recentOrders = Customer::where('activation_status', 'ordered_unverified')
            ->with('assignedTo')
            ->orderByDesc('updated_at')
            ->limit(10)->get();

        return view('manager.dashboard', compact(
            'totalAssigned','totalContacted','firstOrders','delivered',
            'callResults','contractorPerf','dailyActivity','recentOrders','contractors'
        ));
    }
}
