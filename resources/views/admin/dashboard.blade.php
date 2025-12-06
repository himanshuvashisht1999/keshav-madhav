@extends('admin.layouts.app')
@section('content')

<style>
    /* Dashboard header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }
    .dashboard-header h3 {
        font-size: 26px;
        font-weight: 700;
    }

    /* Card styling */
    .card-report {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        transition: .3s;
        height: 100%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .card-report:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    /* Chart container */
    .chart-container {
        width: 100%;
        height: 250px;
    }

    /* Responsive header */
    @media(max-width: 768px){
        .dashboard-header {
            text-align: center;
            gap: 15px;
        }
        .dashboard-header select {
            width: 100%;
        }
    }
</style>

<div class="content-wrapper p-4">

    {{-- Header --}}
    <div class="dashboard-header">
        <h3>Dashboard</h3>
        <select id="mainFilter" class="form-control shadow border" style="max-width:220px;">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
        </select>
    </div>
   
    <div class="row g-4 col-sm-12" style="display: none;">
        <div class="col-lg-6 col-md-6 ">
            <div class="card-report">
                <h5 class="mb-3">Production</h5>
                <div class="chart-container">
                    <canvas id="productionChart"></canvas>
                </div>
            </div>
        </div>
        {{-- Add more charts here similarly --}}
   
        <div class="col-lg-6 col-md-6 ">
            <div class="card-report">
                <h5 class="mb-3">Stages</h5>
                <div class="chart-container">
                    <canvas id="stagesChart"></canvas>
                </div>
            </div>
        </div>
        {{-- Add more charts here similarly --}}
    </div>
     </br>
    {{-- Responsive Grid: 2 charts per row --}}
    <div class="row g-4 col-sm-12">
        <div class="col-lg-12 col-md-12">
            <div class="card-report">
                <h5 class="mb-3">Fabric Stock</h5>
                <div class="chart-container">
                    <canvas id="fabricChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    </br>
    <div class="row g-4 col-sm-12">
        <div class="col-lg-12 col-md-12">
            <div class="card-report">
                <h5 class="mb-3">Item Stock</h5>
                <div class="chart-container">
                    <canvas id="itemChart"></canvas>
                </div>
            </div>
        </div>
        {{-- Add more charts here similarly --}}
    </div>
    
</div>

{{-- Chart.js & jQuery CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let charts = {}; // Store chart instances

// Generate vibrant colors dynamically
function generateColors(num, color) {
    // Create an array of length `num` with the same color
    return Array(num).fill(color);
}

// Create bar chart
function createBarChart(ctx, labels, data, labelName, hoverLabels = null, color = '#4caf50') {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: labelName,   // completely hide label
                data: data,
                backgroundColor: generateColors(data.length, color),
                borderRadius: 6,
                barThickness: 30,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }, // hide legend
                tooltip: {
                    callbacks: {
                        label: function(context) {

                            // 1️⃣ If hoverLabels exists → show hover label + value
                            if (hoverLabels && hoverLabels[context.dataIndex]) {
                                return hoverLabels[context.dataIndex] + ': ' + context.raw;
                            }

                            // 2️⃣ Else show only labelName + value even if dataset label is empty
                            return labelName + ': ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        display: false   // This hides X axis labels properly
                    },
                    grid: {
                        display: false
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        display: true
                    }
                }
            }

        }
    });
}


// Fetch data from API and render charts
function fetchDashboardData(filter = 'all') {
    $.ajax({
        url: "{{ route('admin.getDashboardData') }}",
        type: 'GET',
        data: { filter: filter },
        dataType: 'json',
        success: function(response) {

            // Fabric Chart
            if(charts.fabricChart) charts.fabricChart.destroy();
            charts.fabricChart = createBarChart(
                document.getElementById('fabricChart'),
                response.fabricStock.labels,
                response.fabricStock.data,
                'in Meters',
                response.fabricStock.hoverLabels,
                '#FF9800'
            );

            if(charts.itemChart) charts.itemChart.destroy();
            charts.itemChart = createBarChart(
                document.getElementById('itemChart'),
                response.itemStock.labels,
                response.itemStock.data,
                'in Quantity',
                response.itemStock.hoverLabels,
                '#2196F3'
            );

            if(charts.productionChart) charts.productionChart.destroy();
            charts.productionChart = createBarChart(
                document.getElementById('productionChart'),
                response.itemStock.labels,
                response.itemStock.data,
                'in Quantity',
                response.itemStock.hoverLabels,
                '#9C27B0'
            );

            if(charts.stagesChart) charts.stagesChart.destroy();
            charts.stagesChart = createBarChart(
                document.getElementById('stagesChart'),
                response.itemStock.labels,
                response.itemStock.data,
                'in Quantity',
                response.itemStock.hoverLabels,
                '#795548'
            );
            
            // You can add more charts here similarly
        },
        error: function(err) {
            console.error('Error fetching dashboard data', err);
        }
    });
}

// Initial load
fetchDashboardData();

// Filter change
$('#mainFilter').on('change', function () {
    fetchDashboardData($(this).val());
});
</script>

@endsection
