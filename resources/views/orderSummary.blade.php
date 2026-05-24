@extends('layouts.app')

@section('title', 'Order Summary')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/orderSummary.css') }}">
<style>
    .period-tabs { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
    .month-selectors {
        display: {{ $period === 'month' || $period === 'year' ? 'flex' : 'none' }};
        gap: 6px;
        align-items: center;
        margin-top: 8px;
    }
    .month-selectors select {
        padding: 5px 10px;
        border: 1.5px solid var(--border, #e2e8f0);
        border-radius: 8px;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        color: var(--ink, #1a202c);
        background: #fff;
        cursor: pointer;
    }
    .month-selectors select:focus { outline: none; border-color: #667eea; }
</style>
@endsection

@section('content')
@php
    $revenueChange     = $revenueChange     ?? null;
    $orderCountChange  = $orderCountChange  ?? null;
    $currentRevenue    = $currentRevenue    ?? 0;
    $currentOrderCount = $currentOrderCount ?? 0;
    $totalOrders       = $totalOrders       ?? 0;
    $totalRevenue      = $totalRevenue      ?? 0;
    $completedOrders   = $completedOrders   ?? 0;
    $avgOrderValue     = $avgOrderValue     ?? 0;
    $period            = $period            ?? 'month';
    $month             = $month             ?? now()->month;
    $year              = $year              ?? now()->year;
    $monthOptions      = $monthOptions      ?? [];
    $chartData         = $chartData         ?? [];
    $statusCounts      = $statusCounts      ?? [];
    $topArtworks       = $topArtworks       ?? collect();
    $completedOrderHistory = $completedOrderHistory ?? collect();
@endphp

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Order Summary</span>
    </div>
</div>

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back
    </a>
</div>

<div class="summary-page">

    {{-- ══ PAGE HEADER ══ --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-title">Order Summary</div>
            <div class="page-sub">Track your sales and order history</div>
        </div>
        <span class="badge-live">Live</span>
    </div>

    {{-- ══ METRIC CARDS ══ --}}
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-label">Total Orders</div>
            <div class="metric-value">{{ number_format($totalOrders) }}</div>
            <div class="metric-sub {{ is_null($orderCountChange) ? 'neutral' : ($orderCountChange >= 0 ? 'up' : 'down') }}">
                @if(is_null($orderCountChange))
                    No previous data
                @else
                    {{ $orderCountChange >= 0 ? '↑' : '↓' }} {{ abs($orderCountChange) }}% vs last period
                @endif
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">RM {{ number_format($totalRevenue, 2) }}</div>
            <div class="metric-sub {{ is_null($revenueChange) ? 'neutral' : ($revenueChange >= 0 ? 'up' : 'down') }}">
                @if(is_null($revenueChange))
                    No previous data
                @else
                    {{ $revenueChange >= 0 ? '↑' : '↓' }} {{ abs($revenueChange) }}% vs last period
                @endif
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Completed Orders</div>
            <div class="metric-value">{{ number_format($completedOrders) }}</div>
            <div class="metric-sub neutral">
                @if($totalOrders > 0)
                    {{ round(($completedOrders / $totalOrders) * 100) }}% completion rate
                @else
                    No orders yet
                @endif
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Avg. Order Value</div>
            <div class="metric-value">RM {{ number_format($avgOrderValue, 2) }}</div>
            <div class="metric-sub neutral">Per completed order</div>
        </div>
    </div>

    {{-- ══ REVENUE CHART ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Revenue Overview
            </div>
            <div>
                <div class="period-tabs" id="periodTabs">
                    <button class="period-tab {{ $period === 'day'   ? 'active' : '' }}" data-period="day">Day</button>
                    <button class="period-tab {{ $period === 'week'  ? 'active' : '' }}" data-period="week">Week</button>
                    <button class="period-tab {{ $period === 'month' ? 'active' : '' }}" data-period="month">Month</button>
                    <button class="period-tab {{ $period === 'year'  ? 'active' : '' }}" data-period="year">Year</button>
                </div>
                <div class="month-selectors" id="monthSelectors">
                    <select id="monthSelect">
                        @foreach($monthOptions as $m => $name)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select id="yearSelect">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <div class="sp-card-body">
            <div class="revenue-strip">
                <span class="revenue-big" id="revTotal">RM {{ number_format($currentRevenue, 2) }}</span>
                <span class="revenue-change-badge {{ is_null($revenueChange) ? '' : ($revenueChange >= 0 ? 'up' : 'down') }}" id="revChange">
                    @if(is_null($revenueChange))
                        — New
                    @else
                        {{ $revenueChange >= 0 ? '↑' : '↓' }} {{ abs($revenueChange) }}%
                    @endif
                </span>
            </div>
            <div class="revenue-sub" id="revSub">
                @if($period === 'day') Revenue today
                @elseif($period === 'week') Revenue this week
                @elseif($period === 'year') Revenue in {{ $year }}
                @else Revenue in {{ Carbon\Carbon::create($year, $month)->format('F Y') }}
                @endif
            </div>

            <div class="chart-legend">
                <div class="chart-legend-item">
                    <div class="legend-sq" style="background:var(--primary);"></div> Revenue (RM)
                </div>
                <div class="chart-legend-item">
                    <div class="legend-sq round" style="background:var(--primary-2);"></div> Orders
                </div>
            </div>

            <div class="chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ══ TWO COLUMN: Donut + Top Artworks ══ --}}
    <div class="two-col">

        {{-- Orders by Status --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left">
                    <div class="hline"></div>
                    Orders by Status
                </div>
                <span class="card-sub-label">{{ $totalOrders }} total</span>
            </div>
            <div class="sp-card-body">
                <div class="donut-wrap">
                    <canvas id="donutChart"></canvas>
                </div>

                @php
                    $statusColors = [
                        'pending_payment' => '#f6ad55',
                        'processing'      => '#667eea',
                        'preparing'       => '#b794f4',
                        'shipped'         => '#4fd1c5',
                        'completed'       => '#68d391',
                        'cancelled'       => '#fc8181',
                    ];
                    $statusLabels = [
                        'pending_payment' => 'Awaiting Payment',
                        'processing'      => 'New Order',
                        'preparing'       => 'Preparing',
                        'shipped'         => 'Shipped',
                        'completed'       => 'Completed',
                        'cancelled'       => 'Cancelled',
                    ];
                @endphp

                <div class="donut-legend">
                    @foreach($statusCounts as $status => $count)
                        @if($count > 0)
                        <div class="donut-legend-item">
                            <div class="legend-sq round" style="background:{{ $statusColors[$status] ?? '#ccc' }};"></div>
                            {{ $statusLabels[$status] ?? ucfirst($status) }} ({{ $count }})
                        </div>
                        @endif
                    @endforeach
                </div>

                <div class="status-list">
                    @foreach($statusCounts as $status => $count)
                        @php $pct = $totalOrders > 0 ? ($count / $totalOrders) * 100 : 0; @endphp
                        <div class="status-row">
                            <div class="status-dot" style="background:{{ $statusColors[$status] ?? '#ccc' }};"></div>
                            <div class="status-label">{{ $statusLabels[$status] ?? ucfirst($status) }}</div>
                            <div class="status-bar-wrap">
                                <div class="status-bar" style="width:{{ $pct }}%; background:{{ $statusColors[$status] ?? '#ccc' }};"></div>
                            </div>
                            <div class="status-count">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Top Selling Artworks --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left">
                    <div class="hline"></div>
                    Top Selling Artworks
                </div>
                <span class="card-sub-label">All time</span>
            </div>
            <div class="sp-card-body">
                @if($topArtworks->isEmpty())
                    <div class="empty-state">
                        <p>No completed sales yet.</p>
                    </div>
                @else
                    <div class="top-list">
                        @foreach($topArtworks as $i => $artwork)
                        <div class="top-item">
                            <div class="top-rank {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                                {{ $i + 1 }}
                            </div>
                            @if($artwork->image)
                                <img src="{{ asset('storage/' . $artwork->image) }}"
                                     class="top-thumb"
                                     alt="{{ $artwork->title }}">
                            @else
                                <div class="top-thumb-placeholder">🎨</div>
                            @endif
                            <div class="top-info">
                                <div class="top-name" title="{{ $artwork->title }}">{{ $artwork->title }}</div>
                                <div class="top-units">{{ number_format($artwork->total_sold) }} sold</div>
                            </div>
                            <div class="top-rev">RM {{ number_format($artwork->total_revenue, 2) }}</div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ══ COMPLETED ORDER HISTORY ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Completed Order History
            </div>
            <span class="card-sub-label">{{ number_format($completedOrders) }} orders</span>
        </div>
        <div class="sp-card-body">
            @if($completedOrderHistory->isEmpty())
                <div class="empty-state">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>No completed orders yet.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer</th>
                                <th>Item(s)</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedOrderHistory as $order)
                            <tr>
                                <td>
                                    <span class="order-id">#ORD-{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <div class="buyer-info">
                                        <div class="buyer-avatar">
                                            {{ strtoupper(substr($order->user->fullname ?? 'B', 0, 1)) }}
                                        </div>
                                        <span>{{ $order->user->fullname ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($order->items->isNotEmpty())
                                        {{ Str::limit($order->items->first()->name, 30) }}
                                        @if($order->items->count() > 1)
                                            <span class="more-items">+{{ $order->items->count() - 1 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="amount">RM {{ number_format($order->total, 2) }}</td>
                                <td class="date">{{ $order->updated_at->format('d M Y') }}</td>
                                <td>
                                    <span class="chip chip-{{ $order->status }}">
                                        {{ $order->getSellerStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($completedOrderHistory->hasPages())
                <div class="pagination-wrap">
                    {{ $completedOrderHistory->appends(['period' => $period])->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const initialData  = @json($chartData);
    const statusCounts = @json($statusCounts);
    const statusColors = {
        pending_payment: '#f6ad55',
        processing:      '#667eea',
        preparing:       '#b794f4',
        shipped:         '#4fd1c5',
        completed:       '#68d391',
        cancelled:       '#fc8181',
    };

    const subLabels = {
        day:   'Revenue today',
        week:  'Revenue this week',
        month: 'Revenue for selected month',
        year:  'Revenue for selected year',
    };

    let currentPeriod = '{{ $period }}';
    let currentMonth  = {{ $month }};
    let currentYear   = {{ $year }};

    /* ── Revenue Chart ── */
    let revenueChart;

    function buildRevenueChart(data) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        if (revenueChart) revenueChart.destroy();

        revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Revenue (RM)',
                        data: data.revenues,
                        backgroundColor: 'rgba(102,126,234,0.14)',
                        borderColor: '#667eea',
                        borderWidth: 2,
                        borderRadius: 6,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders',
                        data: data.orders,
                        type: 'line',
                        borderColor: '#764ba2',
                        backgroundColor: 'rgba(118,75,162,0.06)',
                        pointBackgroundColor: '#764ba2',
                        pointRadius: 4,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label === 'Revenue (RM)'
                                ? ' RM ' + Number(ctx.parsed.y).toLocaleString('en-MY', { minimumFractionDigits: 2 })
                                : ' ' + ctx.parsed.y + ' orders'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#bbb', font: { size: 11 }, maxRotation: 45, autoSkip: true }
                    },
                    y: {
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            color: '#bbb', font: { size: 11 },
                            callback: v => v >= 1000 ? 'RM ' + (v / 1000).toFixed(0) + 'k' : 'RM ' + v
                        }
                    },
                    y1: {
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: '#b794f4', font: { size: 11 }, callback: v => v + ' ord' }
                    }
                }
            }
        });
    }

    buildRevenueChart(initialData);

    /* ── Fetch chart data ── */
    function fetchChartData() {
        const url = `{{ route('artist.order.summary.chart') }}?period=${currentPeriod}&month=${currentMonth}&year=${currentYear}`;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                buildRevenueChart(data.chart);
                document.getElementById('revTotal').textContent =
                    'RM ' + Number(data.currentRevenue).toLocaleString('en-MY', { minimumFractionDigits: 2 });

                const badge = document.getElementById('revChange');
                if (data.revenueChange === null) {
                    badge.textContent = '— New';
                    badge.className = 'revenue-change-badge';
                } else {
                    const up = data.revenueChange >= 0;
                    badge.textContent = (up ? '↑ ' : '↓ ') + Math.abs(data.revenueChange) + '%';
                    badge.className = 'revenue-change-badge ' + (up ? 'up' : 'down');
                }

                // Update sub label
                const monthNames = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                const sub = document.getElementById('revSub');
                if (currentPeriod === 'day')        sub.textContent = 'Revenue today';
                else if (currentPeriod === 'week')  sub.textContent = 'Revenue this week';
                else if (currentPeriod === 'year')  sub.textContent = 'Revenue in ' + currentYear;
                else                                sub.textContent = 'Revenue in ' + monthNames[currentMonth] + ' ' + currentYear;
            })
            .catch(err => console.error('Chart fetch error:', err));
    }

    /* ── Period tabs ── */
    document.getElementById('periodTabs').addEventListener('click', function (e) {
        const btn = e.target.closest('.period-tab');
        if (!btn) return;

        document.querySelectorAll('.period-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        currentPeriod = btn.dataset.period;

        // Show/hide month selectors
        const sel = document.getElementById('monthSelectors');
        sel.style.display = (currentPeriod === 'month' || currentPeriod === 'year') ? 'flex' : 'none';

        // Hide month dropdown for year view
        document.getElementById('monthSelect').style.display = currentPeriod === 'year' ? 'none' : '';

        fetchChartData();
    });

    /* ── Month / Year selectors ── */
    document.getElementById('monthSelect').addEventListener('change', function () {
        currentMonth = parseInt(this.value);
        fetchChartData();
    });

    document.getElementById('yearSelect').addEventListener('change', function () {
        currentYear = parseInt(this.value);
        fetchChartData();
    });

    // Init selector visibility
    const sel = document.getElementById('monthSelectors');
    sel.style.display = (currentPeriod === 'month' || currentPeriod === 'year') ? 'flex' : 'none';
    document.getElementById('monthSelect').style.display = currentPeriod === 'year' ? 'none' : '';

    /* ── Donut Chart ── */
    const donutLabels = Object.keys(statusCounts);
    const donutValues = Object.values(statusCounts);
    const donutColors = donutLabels.map(s => statusColors[s] || '#ddd');

    new Chart(document.getElementById('donutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: donutLabels,
            datasets: [{
                data: donutValues,
                backgroundColor: donutColors,
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + ' orders'
                    }
                }
            }
        }
    });

})();
</script>
@endsection