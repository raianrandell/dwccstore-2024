@extends ('layout.inventory')

@section('content')

<div class="row mt-5">
    <h3 class="mb-4"></h3>
    <!-- Total Items Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #4b6cb7, #182848);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold">Total Items</h5>
                    <p class="display-5 fw-bold"><b>&nbsp;{{ $totalItems }}</b></p> <!-- Use dynamic count -->
                </div>
                <div class="icon-container">
                    <i class="fas fa-cubes fa-4x text-light"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Total Categories Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold">Total Categories</h5>
                    <p class="display-5 fw-bold"><b>&nbsp;{{ $totalCategories }}</b></p> <!-- Use dynamic count -->
                </div>
                <div class="icon-container">
                    <i class="fas fa-list fa-4x text-light"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Low Stock Items Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #ff416c, #ff4b2b);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold">Low Stock Items</h5>
                    <p class="display-5 fw-bold"><b>&nbsp;{{ $lowStockItems }}</b></p> <!-- Use dynamic count -->
                </div>
                <div class="icon-container">
                    <i class="fas fa-exclamation-triangle fa-4x text-light"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<br><br>
<!-- Horizontal Bar Chart Comparing Metrics -->
<div class="row mt-3">
    <div class="col-md-12">
        <br>
        <canvas id="metricsChart" width="400" height="113"></canvas>
    </div>
</div>

@endsection

@section('scripts')
<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3"></script>
<script>
    // Prepare the data
    var labels = ['Total Items', 'Total Categories', 'Low Stock Items'];
    var data = [
        {{ $totalItems }},
        {{ $totalCategories }},
        {{ $lowStockItems }}
    ];

    var ctx = document.getElementById('metricsChart').getContext('2d');
    var metricsChart = new Chart(ctx, {
        type: 'bar', // Use 'bar' for horizontal bar chart
        data: {
            labels: labels,
            datasets: [{
                label: 'Inventory Metrics',
                data: data,
                backgroundColor: [
                    '#3498db', // Total Items - Blue
                    '#2ecc71', // Total Categories - Green
                    '#e74c3c'  // Low Stock Items - Red
                ],
                borderColor: [
                    '#2980b9',
                    '#27ae60',
                    '#c0392b'
                ],
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'x', // Set to 'x' for vertical bars
            responsive: true,
            plugins: {
                tooltip: {
                    enabled: true
                },
                legend: {
                    display: false // Hide legend if not needed
                }
            },
            scales: {
                x: { // Horizontal axis
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                y: { // Vertical axis
                    title: {
                        display: true,
                        text: 'Metrics'
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutBounce'
            }
        }
    });
</script>
@endsection
