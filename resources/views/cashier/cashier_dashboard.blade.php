@extends('layout.cashier')

@section('title', 'Dashboard')

@section('content')
<div class="row mt-5 d-flex align-items-stretch">
    <!-- Total Sales Today -->
    <div class="col-xl-6 col-md-12">
        <div class="card bg-info text-white mb-4 h-90">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Total Sales Today</div>
                        <div class="display-4"><b>₱{{ number_format($totalSalesToday, 2) }}</b></div>
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-cash-register fa-3x"></i>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales by Payment Method (Cash, Gcash, Credit) -->
    <div class="col-xl-6 col-md-12">
        <div class="card bg-secondary text-white mb-4 h-90">
            <div class="card-body">
                <div class="row">
                    <div class="col-9 text-right">
                        <div class="card-title">Sales Today by Payment Method</div>
                        <ul class="list-unstyled">
                            <li><b>Cash:</b> ₱{{ number_format($cashSalesToday, 2) }}</li>
                            <li><b>Gcash:</b> ₱{{ number_format($gcashSalesToday, 2) }}</li>
                            <li><b>Credit:</b> ₱{{ number_format($creditSalesToday, 2) }}</li>
                        </ul>
                    </div>
                    <div class="col-3 mt-4">
                        <i class="fas fa-credit-card fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Sales Line Graph -->
<div class="card mb-4">
    <div class="card-header">
        <h4>Monthly Sales</h4>
        <div class="form-inline float-right">
            <label for="monthFilter" class="mr-2">Filter by Month:</label>
            <select id="monthFilter" class="form-control">
                <option value="All">All Months</option>
                @foreach($months as $index => $month)
                    <option value="{{ $index + 1 }}">{{ $month }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="chart-container" style="max-width: 100%; overflow: auto; padding: 10px;">
            <canvas id="salesChart" style="width: 100%; height: 600px;"></canvas>
        </div>
    </div>
</div> 

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('salesChart').getContext('2d');
    var allSalesData = @json($dailySales); // Sales data for all months
    var months = @json($months); // Month labels

    // Sum monthly sales data
    var monthlySalesTotals = months.map(function (month, index) {
        return allSalesData[index + 1].reduce(function (acc, daySales) {
            return acc + daySales;
        }, 0);
    });

    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Sales',
                data: monthlySalesTotals, // Default to monthly total sales
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });

    // Slicer to filter data
    document.getElementById('monthFilter').addEventListener('change', function () {
        const selectedMonth = this.value;
        
        if (selectedMonth === "All") {
            // For "All Months", use the total sales per month
            salesChart.data.labels = months;
            salesChart.data.datasets[0].data = monthlySalesTotals;
        } else {
            // For a specific month, show the daily sales data
            salesChart.data.labels = Array.from({ length: allSalesData[selectedMonth].length }, (_, i) => i + 1); // Days of the selected month
            salesChart.data.datasets[0].data = allSalesData[selectedMonth] || [];
        }

        salesChart.update();
    });
});

</script>

@endsection