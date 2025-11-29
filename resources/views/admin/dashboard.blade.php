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
        height: 250px; /* fixed height for all charts */
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
        <h3>Dashboard Overview</h3>
        <select id="mainFilter" class="form-control shadow border" style="max-width:220px;">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
        </select>
    </div>

    {{-- Responsive Grid: 2 Charts per Row --}}
    <div class="row">

        
        {{-- Total Orders --}}
        {{-- <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card-report">
                <h5 class="mb-3">Total Orders</h5>
                <div class="chart-container">
                    <canvas id="orderChart"></canvas>
                </div>
            </div>
        </div> --}}

        {{-- Stage Wise Status --}}
        {{-- <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card-report">
                <h5 class="mb-3">Stage Wise Status</h5>
                <div class="chart-container">
                    <canvas id="stageChart"></canvas>
                </div>
            </div>
        </div> --}}

        {{-- Fabric Chart --}}
        <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
            <div class="card-report">
                <h5 class="mb-3">Fabric Stock</h5>
                <div class="chart-container">
                    <canvas id="fabricChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Item Stock --}}
        <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
            <div class="card-report">
                <h5 class="mb-3">Item Stock</h5>
                <div class="chart-container">
                    <canvas id="itemStockChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Warehouse Status --}}
        {{-- <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card-report">
                <h5 class="mb-3">Warehouse Status</h5>
                <div class="chart-container">
                    <canvas id="warehouseChart"></canvas>
                </div>
            </div>
        </div> --}}

        {{-- Purchase Orders --}}
        {{-- <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
            <div class="card-report">
                <h5 class="mb-3">Purchase Orders</h5>
                <div class="chart-container">
                    <canvas id="purchaseChart"></canvas>
                </div>
            </div>
        </div> --}}

    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* ----------------------------
   SAMPLE DATA
---------------------------- */
const sampleData = {
    fabric: {
        all: { labels: ['Polyester', 'Cotton', 'Nylon'], data: [1000, 1200, 800] },
        month: { labels: ['Polyester', 'Cotton', 'Nylon'], data: [300, 200, 150] }
    },
    itemStock: {
        all: { labels: ['Zip', 'Button', 'Belt', 'Tapes', 'Zip1', 'Button1', 'Belt1', 'Tapes1', 'Zip', 'Button2', 'Belt2', 'Tapes2', 'Zip3', 'Button3', 'Belt4', 'Tapes4'], data: [490, 244, 122, 133, 490, 244, 122, 133, 490, 244, 122, 133, 490, 244, 122, 133] },
        month: { labels: ['Zip', 'Button', 'Belt', 'Tapes', 'Zip1', 'Button1', 'Belt1', 'Tapes1', 'Zip', 'Button2', 'Belt2', 'Tapes2', 'Zip3', 'Button3', 'Belt4', 'Tapes4'], data: [122, 1344, 322, 133, 122, 1344, 322, 133, 490, 244, 122, 133, 490, 244, 122, 133] }
    },
   //  totalOrders: {
   //      all: { labels: ['Pending', 'Completed', 'In Progress'], data: [35, 120, 15] },
   //      month: { labels: ['Pending', 'Completed', 'In Progress'], data: [12, 333, 43] }
   //  },
   //  stageWise: {
   //      all: { labels: ['Cutting', 'Stitching', 'QC'], data: [10, 5, 2] }
   //  },
   //  warehouse: {
   //      all: { labels: ['Pending', 'Completed', 'In Progress'], data: [12, 50, 8] }
   //  },
   //  purchases: {
   //      all: { labels: ['Fabric PO', 'Item PO'], data: [22, 14] }
   //  }
};

/* ----------------------------
   FUNCTION TO CREATE CHARTS
---------------------------- */
function createBarChart(ctx, labels, data, labelName) {
    return new Chart(ctx, {
         type: 'bar',
         data: {
            labels: labels,
            datasets: [{
               label: labelName,
               data: data,
               backgroundColor: generateColors(data.length),
               borderRadius: 6,
               barThickness: 30,       // fixed width for all bars
               maxBarThickness: 40     // optional: max width if auto-scaling
            }]
         },
         options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
               x: {
                     // prevent bars from stretching too much
                     ticks: {
                        autoSkip: false
                     }
               }
            }
         }
    });
}

/* ----------------------------
   INITIALIZE CHARTS DYNAMICALLY
---------------------------- */
const chartConfigs = {
    fabricChart: { dataKey: 'fabric', labelName: 'Meters' },
    itemStockChart: { dataKey: 'itemStock', labelName: 'Qty' },
    // orderChart: { dataKey: 'totalOrders', labelName: 'Orders' },
    // stageChart: { dataKey: 'stageWise', labelName: 'Lots' },
    // warehouseChart: { dataKey: 'warehouse', labelName: 'Count' },
    // purchaseChart: { dataKey: 'purchases', labelName: 'Count' }
};

const charts = {};

// Initialize charts
for (let id in chartConfigs) {
    const config = chartConfigs[id];
    const ctx = document.getElementById(id);
    const data = sampleData[config.dataKey].all;
    charts[id] = createBarChart(ctx, data.labels, data.data, config.labelName);
}

/* ----------------------------
   DYNAMIC FILTER CHANGE EVENT
---------------------------- */
document.getElementById("mainFilter").addEventListener("change", function () {
    const filter = this.value;

    for (let id in chartConfigs) {
        const config = chartConfigs[id];
        const chartData = sampleData[config.dataKey][filter] || sampleData[config.dataKey].all;

        charts[id].destroy(); // Destroy old chart
        const ctx = document.getElementById(id);
        charts[id] = createBarChart(ctx, chartData.labels, chartData.data, config.labelName);
    }
});

// Function to generate dynamic colors
function generateColors(num) {
   const colors = [];
   const saturation = 70; // keep saturation high for vibrancy
   const lightness = 50;  // medium lightness

   for (let i = 0; i < num; i++) {
      // evenly distribute hues around the color wheel
      const hue = Math.round((360 / num) * i);
      colors.push(`hsl(${hue}, ${saturation}%, ${lightness}%)`);
   }
   return colors;
}


$('#mainFilter').on('change', function () {
    const filter = $(this).val(); // all or month
    let apiUrl = "{{ route('admin.getDashboardData')}}";
    // Optional: AJAX call to fetch data from server
    $.ajax({
        url: apiUrl,       // Your server endpoint
        type: 'GET',
        data: { filter: filter },    // send selected filter
        dataType: 'json',
        success: function(response) {
            // Destroy old chart
            fabricChart.destroy();

            // Create new chart with fetched data
            fabricChart = createBarChart(
                document.getElementById('fabricChart'),
                response.labels,
                response.data,
                'Meters'
            );
        },
        error: function(err) {
            console.error('Error fetching data', err);
        }
    });
});
</script>

@endsection
