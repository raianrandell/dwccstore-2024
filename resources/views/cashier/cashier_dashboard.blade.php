@extends('layout.cashier')

@section('title', 'Cashier Dashboard')

@section('content')
<div class="row mt-5">
    <!-- Total Sales Today -->
    <div class="col-xl-6 col-md-12">
                <div class="card text-white shadow-lg border-0 rounded-lg h-100 position-relative" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-2">Total Sales Today</h5>
                    <h2 class="fw-bold display-5">₱{{ number_format($totalSalesToday, 2) }}</h2>
                </div>
                <i class="fas fa-cash-register fa-4x opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Sales by Payment Method -->
    <div class="col-xl-6 col-md-12">
        <div class="card bg-secondary text-white shadow-lg rounded-lg h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-2">Sales Today by Payment Method</h5>
                    <ul class="list-unstyled">
                        <li><b>Cash:</b> ₱{{ number_format($cashSalesToday, 2) }}</li>
                        <li><b>Gcash:</b> ₱{{ number_format($gcashSalesToday, 2) }}</li>
                        <li><b>Credit:</b> ₱{{ number_format($creditSalesToday, 2) }}</li>
                    </ul>
                </div>
                <i class="fas fa-credit-card fa-4x opacity-75"></i>
            </div>
        </div>
    </div>
</div>
<br>

<!-- Monthly Sales Line Graph -->
<div class="card mb-4">
    <div class="card-header">
        <h4 class="mt-3">Monthly Sales</h4><br>
        <div class="d-flex align-items-center float-right">
            <label for="monthFilter" class="mr-2 mb-0">Filter by Month:&nbsp;</label>
            <select id="monthFilter" class="form-control mr-2" style="width: 39%;">
                <option value="All">All Months</option>
                @foreach(range(1, 12) as $monthNumber)
                    <option value="{{ $monthNumber }}">{{ \Carbon\Carbon::create()->month($monthNumber)->format('F') }}</option>
                @endforeach
            </select>
            &nbsp;&nbsp;
            <button id="resetButton" class="btn btn-secondary" title="Reset Filter">
                <i class="fas fa-rotate-left"></i></button>
        </div>
        <br>
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
    var months = @json($months); // Month names ["Jan", "Feb", ..., "Dec"]

    // Sum monthly sales data
    var monthlySalesTotals = months.map(function (month, index) {
        return allSalesData[index + 1].reduce((acc, daySales) => acc + daySales, 0);
    });

    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months, // Default: Show months
            datasets: [{
                label: 'Sales',
                data: monthlySalesTotals, 
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false,
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    ticks: { font: { size: 12 } } // Adjust font size
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 12 },
                        callback: function(value) {
                            return '₱' + value.toLocaleString(); // Add Peso sign
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += '₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Month Filter
    document.getElementById('monthFilter').addEventListener('change', function () {
        const selectedMonth = this.value;

        if (selectedMonth === "All") {
            // Show months
            salesChart.data.labels = months;
            salesChart.data.datasets[0].data = monthlySalesTotals;
        } else {
            // Show days with month name (e.g., "Jan 1", "Jan 2", ...)
            let daysInMonth = allSalesData[selectedMonth].length;
            let monthName = months[selectedMonth - 1]; // Get month name (index-based)
            let dayLabels = Array.from({ length: daysInMonth }, (_, i) => `${monthName} ${i + 1}`);

            salesChart.data.labels = dayLabels;
            salesChart.data.datasets[0].data = allSalesData[selectedMonth] || [];
        }

        salesChart.update();
    });

    // Reset Button
    document.getElementById('resetButton').addEventListener('click', function() {
        document.getElementById('monthFilter').value = 'All';
        salesChart.data.labels = months;
        salesChart.data.datasets[0].data = monthlySalesTotals;
        salesChart.update();
    });
});


</script>

@endsection