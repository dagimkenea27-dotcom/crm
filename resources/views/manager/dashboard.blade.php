@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Overview of remote sales performance — {{ now()->format('F Y') }}</p>
</div>

<!-- Stats row -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($totalAssigned) }}</div>
            <div class="stat-label">Assigned Customers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.39 18a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.92-8.39A2 2 0 0 1 3.46 2h3a2 2 0 0 1 2 1.72"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($totalContacted) }}</div>
            <div class="stat-label">Contacted</div>
            @if($totalAssigned > 0)
            <div class="stat-change up">{{ round($totalContacted/$totalAssigned*100,1) }}% contact rate</div>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($firstOrders) }}</div>
            <div class="stat-label">First Orders</div>
            @if($totalAssigned > 0)
            <div class="stat-change up">{{ round($firstOrders/$totalAssigned*100,1) }}% conversion rate</div>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <svg viewBox="0 0 24 24"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3m-6 9h10l-4-4m0 8l4-4"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($delivered) }}</div>
            <div class="stat-label">Delivered Orders</div>
            @if($firstOrders > 0)
            <div class="stat-change up">{{ round($delivered/$firstOrders*100,1) }}% delivery rate</div>
            @endif
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Performance by Contractor -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Performance by Contractor</div>
                <div class="card-subtitle">This month</div>
            </div>
        </div>
        <div class="chart-wrap">
            <canvas id="contractorChart"></canvas>
        </div>
    </div>

    <!-- Call Results donut -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Call Results (All Contractors)</div>
                <div class="card-subtitle">Current distribution</div>
            </div>
        </div>
        <div class="chart-wrap">
            <canvas id="callResultsChart"></canvas>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Recent First Orders needing verification -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Recent First Orders (Pending Verification)</div>
            <a href="{{ route('customers.index', ['filter' => 'ordered_unverified']) }}" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $c)
                    <tr>
                        <td class="text-muted text-sm">{{ $c->updated_at->format('M d, Y') }}</td>
                        <td class="fw-600">{{ $c->name ?? 'Unnamed' }}</td>
                        <td>{{ $c->phone }}</td>
                        <td>{{ $c->assignedTo?->name ?? '—' }}</td>
                        <td><span class="badge badge-ordered">Unverified</span></td>
                        <td>
                            <button class="btn btn-xs btn-primary" onclick="openCustomer({{ $c->id }})">Verify</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-muted text-sm" style="padding:20px;text-align:center">No pending verifications</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Contractor Performance table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Contractor Performance</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Contractor</th>
                        <th>Assigned</th>
                        <th>Contacted</th>
                        <th>First Orders</th>
                        <th>Conv. Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contractorPerf as $perf)
                    <tr>
                        <td class="fw-600">{{ $perf['name'] }}</td>
                        <td>{{ $perf['assigned'] }}</td>
                        <td>{{ $perf['contacted'] }}</td>
                        <td>{{ $perf['orders'] }}</td>
                        <td><span class="badge badge-interested">{{ $perf['rate'] }}%</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted text-sm" style="padding:20px;text-align:center">No contractors yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const contractorData = @json($contractorPerf);
const callResultsData = @json($callResults);

// Contractor bar chart
new Chart(document.getElementById('contractorChart'), {
    type: 'bar',
    data: {
        labels: contractorData.map(c => c.name),
        datasets: [
            { label: 'Assigned', data: contractorData.map(c => c.assigned), backgroundColor: '#dbeafe', borderRadius: 4 },
            { label: 'Contacted', data: contractorData.map(c => c.contacted), backgroundColor: '#1a56e8', borderRadius: 4 },
            { label: 'First Orders', data: contractorData.map(c => c.orders), backgroundColor: '#f97316', borderRadius: 4 },
            { label: 'Delivered', data: contractorData.map(c => c.delivered), backgroundColor: '#059669', borderRadius: 4 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
        scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
    }
});

// Call results donut
const statusLabels = {
    not_called: 'Not Called', no_answer: 'No Answer', interested: 'Interested',
    not_ready: 'Not Ready', callback: 'Callback', ordered_unverified: 'Order Unverified',
    verified_order: 'Verified', do_not_contact: 'Do Not Contact'
};
const colors = ['#94a3b8','#fbbf24','#34d399','#60a5fa','#a78bfa','#fb923c','#10b981','#f87171'];
const rawLabels = Object.keys(callResultsData).map(k => statusLabels[k] || k);
const rawValues = Object.values(callResultsData);
const hasData   = rawValues.length > 0 && rawValues.some(v => v > 0);

new Chart(document.getElementById('callResultsChart'), {
    type: 'doughnut',
    data: {
        labels: hasData ? rawLabels : ['No calls logged yet'],
        datasets: [{
            data: hasData ? rawValues : [1],
            backgroundColor: hasData ? colors.slice(0, rawValues.length) : ['#e2e8f0'],
            borderWidth: 0,
            hoverOffset: hasData ? 6 : 0
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'right',
                labels: { font: { size: 11 }, boxWidth: 10, padding: 10 }
            },
            tooltip: { enabled: hasData }
        }
    }
});
</script>
@endpush
