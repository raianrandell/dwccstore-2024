@extends ('layout.inventory')

@section('content')

<div class="row mt-5">
    <h3 class="mb-4">Inventory Dashboard</h3>
    <!-- Total Items Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Total Items</div>
                        <div class="display-4"><b>&nbsp;{{ $totalItems }}</b></div> <!-- Use dynamic count -->
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-boxes fa-3x"></i> 
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">
                    View Details
                </a>
                <div class="small text-white">
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Total Categories Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Total Categories</div>
                        <div class="display-4"><b>&nbsp;{{ $totalCategories }}</b></div> <!-- Use dynamic count -->
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-list fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">
                    View Details
                </a>
                <div class="small text-white">
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Low Stock Items Card -->
    <div class="col-xl-4 col-md-6">
        <div class="card bg-danger text-white mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Low Stock Items</div>
                        <div class="display-4"><b>&nbsp;{{ $lowStockItems }}</b></div> <!-- Use dynamic count -->
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="#">
                    View Details
                </a>
                <div class="small text-white">
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
    </div>
</div>
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
