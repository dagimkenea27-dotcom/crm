@extends('layouts.app')

@section('title', 'My Performance')

@section('content')
<div class="page-header">
    <h1 class="page-title">My Performance</h1>
    <p class="page-subtitle">Track your customer engagement, conversion rates, and call outcomes</p>
</div>

<!-- Stat cards grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($assigned) }}</div>
            <div class="stat-label">Assigned Leads</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.39 18a19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.92-8.39A2 2 0 0 1 3.46 2h3a2 2 0 0 1 2 1.72"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($contacted) }}</div>
            <div class="stat-label">Contacted</div>
            <div class="stat-change up">{{ $contactRate }}% contact rate</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            <svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($orders) }}</div>
            <div class="stat-label">First Orders</div>
            <div class="stat-change up">{{ $convRate }}% conversion</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <svg viewBox="0 0 24 24"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3m-6 9h10l-4-4m0 8l4-4"/></svg>
        </div>
        <div class="stat-body">
            <div class="stat-value">{{ number_format($delivered) }}</div>
            <div class="stat-label">Delivered</div>
            <div class="stat-change up">{{ $deliveryRate }}% delivered</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid-2">
    <!-- Call Results Donut Chart -->
    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 class="card-title">Call Outcomes</h2>
                <p class="card-subtitle">Distribution of all calls you have logged</p>
            </div>
            <span class="badge badge-primary">{{ array_sum($callResults->toArray()) }} total calls</span>
        </div>
        <div style="height:260px; position:relative; display:flex; align-items:center; justify-content:center;">
            <canvas id="callResultsChart"></canvas>
        </div>
    </div>

    <!-- Daily Activity Line Chart -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daily Activity (Last 7 Days)</h2>
            <p class="card-subtitle">Volume of calls made each day</p>
        </div>
        <div style="height:260px; position:relative;">
            <canvas id="dailyActivityChart"></canvas>
        </div>
    </div>
</div>

<!-- Summary Tips & Action Card -->
<div class="card" style="margin-top:20px;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h2 class="card-title">My Action Checklist</h2>
            <p class="card-subtitle">Focus on high-priority customer follow-ups</p>
        </div>
        <a href="{{ route('customers.index', ['tab' => 'followup']) }}" class="btn btn-secondary" style="text-decoration:none;">
            View Follow-ups Due
        </a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
        <div style="background:var(--bg); border:1px solid var(--border); border-radius:var(--radius); padding:16px;">
            <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Coupon Code</div>
            <div style="font-size:18px; font-weight:700; color:var(--primary); font-family:monospace; margin-top:4px;">
                {{ auth()->user()->coupon_code ?: 'NOT SET' }}
            </div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Share with interested buyers for discounts</div>
        </div>

        <div style="background:var(--bg); border:1px solid var(--border); border-radius:var(--radius); padding:16px;">
            <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Next Step</div>
            <div style="font-size:14px; font-weight:600; color:var(--text); margin-top:4px;">Call pending callbacks</div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">Follow-ups scheduled for today should be called first</div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Donut Chart
    const callData = @json($callResults);
    const labels = {
        'interested': 'Interested',
        'callback': 'Callback',
        'not_ready': 'Not Ready',
        'ordered_unverified': 'Ordered (Unverified)',
        'no_answer': 'No Answer',
        'do_not_contact': 'Do Not Contact'
    };
    const colors = {
        'interested': '#1a56e8',
        'callback': '#f59e0b',
        'not_ready': '#8b5cf6',
        'ordered_unverified': '#10b981',
        'no_answer': '#94a3b8',
        'do_not_contact': '#ef4444'
    };

    const keys = Object.keys(callData);
    const hasCalls = keys.length > 0 && keys.some(k => callData[k] > 0);

    new Chart(document.getElementById('callResultsChart'), {
        type: 'doughnut',
        data: {
            labels: hasCalls ? keys.map(k => labels[k] || k) : ['No calls logged yet'],
            datasets: [{
                data: hasCalls ? keys.map(k => callData[k]) : [1],
                backgroundColor: hasCalls ? keys.map(k => colors[k] || '#cbd5e1') : ['#e2e8f0'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Inter', size: 12 } } },
                tooltip: { enabled: hasCalls }
            },
            cutout: '65%'
        }
    });

    // 2. Daily Activity Chart
    const dailyData = @json($dailyActivity);
    const dLabels = dailyData.map(d => d.date);
    const dCounts = dailyData.map(d => d.calls);

    new Chart(document.getElementById('dailyActivityChart'), {
        type: 'line',
        data: {
            labels: dLabels.length ? dLabels : ['Today'],
            datasets: [{
                label: 'Calls Made',
                data: dCounts.length ? dCounts : [0],
                borderColor: '#1a56e8',
                backgroundColor: 'rgba(26, 86, 232, 0.08)',
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#1a56e8',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endpush
@endsection
