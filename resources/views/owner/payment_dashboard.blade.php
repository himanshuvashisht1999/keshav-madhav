@extends('owner.layouts.app')

@section('styles')
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: var(--text-muted);
            --slate-400: var(--text-muted);
            --slate-500: var(--text-muted);
            --slate-600: var(--text-main);
            --slate-700: var(--text-main);
            --slate-800: var(--text-main);
            --slate-900: var(--text-main);
            --KM-purple: var(--text-main);
            --KM-purple-dark: #5a32a3;
            --KM-purple-light: var(--text-main);
        }

        /* =========================================
               MOBILE APP STYLES
            ========================================= */
        @media (max-width: 991.98px) {
            .app-hero {
                background: var(--KM-purple);
                padding: 40px 24px 80px;
                color: white;
                position: relative;
                overflow: hidden;
            }

            .app-hero::after {
                content: '';
                position: absolute;
                top: -50px;
                right: -50px;
                width: 150px;
                height: 150px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
            }

            .app-hero-label {
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 2px;
                font-weight: 800;
                opacity: 0.8;
                display: block;
                margin-bottom: 4px;
            }

            .app-hero-title {
                font-size: 28px;
                font-weight: 900;
                margin-bottom: 6px;
                letter-spacing: -1px;
            }

            .app-stats-container {
                padding: 0 20px;
                margin-top: -45px;
                position: relative;
                z-index: 10;
            }

            .app-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }

            .app-stat-card {
                background: white;
                border-radius: 16px;
                padding: 16px 10px;
                text-align: center;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
                border: 1px solid var(--slate-100);
            }

            .app-stat-value {
                display: block;
                font-size: 16px;
                font-weight: 900;
                color: var(--slate-900);
                margin-bottom: 2px;
            }

            .app-stat-label {
                display: block;
                font-size: 8px;
                font-weight: 800;
                color: var(--slate-500);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .app-filter-section {
                padding: 24px;
            }

            .app-card {
                background: white;
                border-radius: 20px;
                padding: 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.03);
                border: 1px solid var(--slate-100);
                margin-bottom: 20px;
            }

            .app-card-title {
                font-size: 14px;
                font-weight: 800;
                color: var(--slate-700);
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .chart-container {
                height: 250px;
                width: 100%;
            }
        }

        /* =========================================
               DESKTOP STYLES
            ========================================= */
        @media (min-width: 992px) {
            .desktop-container {
                padding: 40px;
                max-width: 1200px;
                margin: 0 auto;
            }
            .chart-card {
                background: white;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.05);
                margin-bottom: 30px;
            }
            .chart-container {
                height: 350px;
            }
        }

        .text-received { color: #10b981; }
        .text-paid { color: #ef4444; }
        .text-balance { color: var(--KM-purple); }
    </style>
@endsection

@section('content')

    <!-- ================= MOBILE APP VIEW ================= -->
    <div class="mobile-only">
        <div class="app-hero">
            <span class="app-hero-label">Owner Analytics</span>
            <h1 class="app-hero-title">Financial Flow</h1>
            <p class="app-hero-subtitle small opacity-75">Real-time payment tracking</p>
        </div>

        <div class="app-stats-container">
            <div class="app-stats-grid">
                <div class="app-stat-card">
                    <span class="app-stat-value text-received" id="m-statsReceived">₹0</span>
                    <span class="app-stat-label">Received</span>
                </div>
                <div class="app-stat-card">
                    <span class="app-stat-value text-paid" id="m-statsPaid">₹0</span>
                    <span class="app-stat-label">Paid</span>
                </div>
                <div class="app-stat-card">
                    <span class="app-stat-value text-balance" id="m-statsBalance">₹0</span>
                    <span class="app-stat-label">Balance</span>
                </div>
            </div>
        </div>

        <div class="app-filter-section">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <input type="date" id="m-dateFrom" class="form-control form-control-sm border-0 shadow-sm" style="border-radius:10px;">
                </div>
                <div class="col-6">
                    <input type="date" id="m-dateTo" class="form-control form-control-sm border-0 shadow-sm" style="border-radius:10px;">
                </div>
            </div>
            <select id="m-dashboardFilter" class="form-control form-control-sm border-0 shadow-sm mb-4" style="border-radius:10px;">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>

            <div class="app-card">
                <div class="app-card-title"><i class="fas fa-chart-line"></i> Monthly Trend</div>
                <div class="chart-container">
                    <canvas id="m-trendChart"></canvas>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-title"><i class="fas fa-chart-pie"></i> Payment Mix</div>
                <div class="chart-container">
                    <canvas id="m-typeMixChart"></canvas>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-title"><i class="fas fa-chart-bar"></i> Categories</div>
                <div class="chart-container">
                    <canvas id="m-categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= DESKTOP VIEW ================= -->
    <div class="desktop-only desktop-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="font-weight-bold">Payment Analytics</h2>
            <div class="d-flex gap-2">
                <input type="date" id="d-dateFrom" class="form-control form-control-sm mr-2">
                <input type="date" id="d-dateTo" class="form-control form-control-sm mr-2">
                <select id="d-dashboardFilter" class="form-control form-control-sm" style="width: 150px;">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="app-stat-card py-4">
                    <span class="app-stat-label">Total Received</span>
                    <span class="h4 font-weight-bold text-received" id="d-statsReceived">₹0.00</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="app-stat-card py-4">
                    <span class="app-stat-label">Total Paid</span>
                    <span class="h4 font-weight-bold text-paid" id="d-statsPaid">₹0.00</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="app-stat-card py-4">
                    <span class="app-stat-label">Net Balance</span>
                    <span class="h4 font-weight-bold text-balance" id="d-statsBalance">₹0.00</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="chart-card">
                    <div class="font-weight-bold mb-3">Revenue vs Expense Trend</div>
                    <div class="chart-container">
                        <canvas id="d-trendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <div class="font-weight-bold mb-3">Overall Mix</div>
                    <div class="chart-container">
                        <canvas id="d-typeMixChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="font-weight-bold mb-3">Category Breakdown</div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="d-categoryChart"></canvas>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let charts = {};

    function initChart(id, type, labels, datasets, options = {}) {
        const ctx = document.getElementById(id).getContext('2d');
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(ctx, {
            type: type,
            data: { labels: labels, datasets: datasets },
            options: Object.assign({
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }, options)
        });
    }

    function updateDashboard(filter = 'all') {
        const isMobile = window.innerWidth < 992;
        const prefix = isMobile ? 'm-' : 'd-';
        const dateFrom = $('#' + prefix + 'dateFrom').val();
        const dateTo = $('#' + prefix + 'dateTo').val();

        $.ajax({
            url: "{{ route('owner.payment-dashboard.data') }}",
            type: 'GET',
            data: { filter: filter, date_from: dateFrom, date_to: dateTo },
            success: function(res) {
                // Update stats for both
                ['m-', 'd-'].forEach(p => {
                    $(`#${p}statsReceived`).text('₹' + res.summary.total_received);
                    $(`#${p}statsPaid`).text('₹' + res.summary.total_paid);
                    $(`#${p}statsBalance`).text('₹' + res.summary.balance);
                });

                // Init/Update Trend Charts
                const trendDatasets = [{
                    label: 'Received', data: res.trend.received, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.4
                }, {
                    label: 'Paid', data: res.trend.paid, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.1)', fill: true, tension: 0.4
                }];
                initChart(prefix + 'trendChart', 'line', res.trend.labels, trendDatasets);

                // Mix Charts
                const mixDatasets = [{
                    data: [res.summary.total_received.replace(/,/g, ''), res.summary.total_paid.replace(/,/g, '')],
                    backgroundColor: ['#10b981', '#ef4444'], borderWidth: 0, cutout: '70%'
                }];
                initChart(prefix + 'typeMixChart', 'doughnut', ['Received', 'Paid'], mixDatasets);

                // Category Charts
                const catDatasets = [{
                    label: 'Received', data: res.categories.received, backgroundColor: '#10b981', borderRadius: 6
                }, {
                    label: 'Paid', data: res.categories.paid, backgroundColor: '#ef4444', borderRadius: 6
                }];
                initChart(prefix + 'categoryChart', 'bar', res.categories.labels, catDatasets);
            }
        });
    }

    $(document).ready(function() {
        updateDashboard();

        // Listeners for mobile
        $('#m-dashboardFilter, #m-dateFrom, #m-dateTo').on('change', function() {
            updateDashboard($('#m-dashboardFilter').val());
        });

        // Listeners for desktop
        $('#d-dashboardFilter, #d-dateFrom, #d-dateTo').on('change', function() {
            updateDashboard($('#d-dashboardFilter').val());
        });

        // Sync inputs if user switches view
        window.addEventListener('resize', () => updateDashboard($('#m-dashboardFilter').val()));
    });
</script>
@endsection