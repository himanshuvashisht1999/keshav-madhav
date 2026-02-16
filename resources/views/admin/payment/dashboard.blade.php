@extends('admin.layouts.app')
@section('content')

    <style>
        .payment-dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .payment-dashboard-header h3 {
            font-size: 26px;
            font-weight: 700;
            color: #334155;
        }

        .stats-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .stats-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .stats-label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .chart-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            height: 100%;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #334155;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .bg-received {
            background: #ecfdf5;
            color: #10b981;
        }

        .bg-paid {
            background: #fef2f2;
            color: #ef4444;
        }

        .bg-balance {
            background: #eff6ff;
            color: #3b82f6;
        }
    </style>

    <div class="content-wrapper p-4">
        <div class="payment-dashboard-header">
            <div>
                <h3>Payment Analytics</h3>
                <p class="text-muted small mb-0">Track your incoming and outgoing payments with real-time insights.</p>
            </div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center mr-3 mb-2">
                    <label class="mr-2 mb-0 small text-muted font-weight-bold text-nowrap">From:</label>
                    <input type="date" id="dateFrom" class="form-control form-control-sm shadow-sm border-0"
                        style="border-radius: 8px;">
                </div>
                <div class="d-flex align-items-center mr-3 mb-2">
                    <label class="mr-2 mb-0 small text-muted font-weight-bold text-nowrap">To:</label>
                    <input type="date" id="dateTo" class="form-control form-control-sm shadow-sm border-0"
                        style="border-radius: 8px;">
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-filter mr-2 text-muted"></i>
                    <select id="dashboardFilter" class="form-control form-control-sm shadow-sm border-0"
                        style="min-width: 150px; border-radius: 8px;">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon bg-received">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="stats-value" id="statsReceived">₹0.00</div>
                    <div class="stats-label">Total Received</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon bg-paid">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="stats-value" id="statsPaid">₹0.00</div>
                    <div class="stats-label">Total Paid</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon bg-balance">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stats-value" id="statsBalance">₹0.00</div>
                    <div class="stats-label">Net Balance</div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Trend Chart --}}
            <div class="col-lg-8">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Payment Trends (Last 6 Months)</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
            {{-- Doughnut Chart --}}
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Payment Mix</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="typeMixChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Category Chart --}}
            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Category Breakdown</div>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let trendChart, typeMixChart, categoryChart;

        function initCharts() {
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            trendChart = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Received',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Paid',
                        data: [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            const ctxMix = document.getElementById('typeMixChart').getContext('2d');
            typeMixChart = new Chart(ctxMix, {
                type: 'doughnut',
                data: {
                    labels: ['Received', 'Paid'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 0,
                        cutout: '70%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            const ctxCat = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(ctxCat, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Received',
                        data: [],
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    }, {
                        label: 'Paid',
                        data: [],
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function updateDashboard(filter = 'all') {
            const dateFrom = $('#dateFrom').val();
            const dateTo = $('#dateTo').val();

            $.ajax({
                url: "{{ route('admin.payment.dashboard.getData') }}",
                type: 'GET',
                data: {
                    filter: filter,
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function (res) {
                    // Update Stats
                    $('#statsReceived').text('₹' + res.summary.total_received);
                    $('#statsPaid').text('₹' + res.summary.total_paid);
                    $('#statsBalance').text('₹' + res.summary.balance);

                    if (res.summary.balance_raw < 0) {
                        $('#statsBalance').removeClass('text-primary').addClass('text-danger');
                    } else {
                        $('#statsBalance').removeClass('text-danger').addClass('text-primary');
                    }

                    // Update Trend
                    trendChart.data.labels = res.trend.labels;
                    trendChart.data.datasets[0].data = res.trend.received;
                    trendChart.data.datasets[1].data = res.trend.paid;
                    trendChart.update();

                    // Update Mix
                    typeMixChart.data.datasets[0].data = [
                        res.summary.total_received.replace(/,/g, ''),
                        res.summary.total_paid.replace(/,/g, '')
                    ];
                    typeMixChart.update();

                    // Update Category
                    categoryChart.data.labels = res.categories.labels;
                    categoryChart.data.datasets[0].data = res.categories.received;
                    categoryChart.data.datasets[1].data = res.categories.paid;
                    categoryChart.update();
                }
            });
        }

        $(document).ready(function () {
            initCharts();
            updateDashboard();

            $('#dashboardFilter').on('change', function () {
                updateDashboard($(this).val());
            });

            $('#dateFrom, #dateTo').on('change', function () {
                updateDashboard($('#dashboardFilter').val());
            });
        });
    </script>

@endsection