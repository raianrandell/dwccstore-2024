@extends('layout.admin')

@section('content')
<br>
<h2 class="mt-4 mb-5">Admin Dashboard</h2>

<div class="row">
    <!-- Total Sales Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9 text-right">
                        <div class="card-title">Total Sales</div>
                        <div class="display-4"><b>₱{{ number_format($grandTotal, 2) }}</b></div>
                    </div>
                    <div class="col-3 mt-4 text-center">
                        <i class="fas fa-dollar-sign fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Items Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9 text-right">
                        <div class="card-title">Total Items</div>
                        <div class="display-4"><b>&nbsp;{{ $totalItems }}</b></div>
                    </div>
                    <div class="col-3 mt-4 text-center">
                        <i class="fas fa-cubes fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Damage Items Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-9 text-right">
                        <div class="card-title">Damage Items</div>
                        <div class="display-4"><b>&nbsp;{{ $damageItems }}</b></div>
                    </div>
                    <div class="col-3 mt-4 text-center">
                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Line Chart for Sales Trends -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Sales Trends</h4>
            </div>
            <div class="card-body">
                <!-- Date Filter Form inside Sales Trends -->
                <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2 mt-4">
                            <button type="submit" class="btn btn-primary mt-2" title="Apply Filters">
                                <i class="fa fa-filter"></i>
                            </button>&nbsp;
                            <button type="button" id="resetFilter" class="btn btn-secondary mt-2 ml-2" title="Reset Filters">
                                <i class="fas fa-rotate-left"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Adjust chart height -->
                <div style="height: 500px;">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // --- Line Chart for Sales Trends --- 
    const lineData = {
        labels: {!! json_encode($dates) !!}, // Dates from PHP
        datasets: [{
            label: 'Sales',
            data: {!! json_encode($sales) !!}, // Sales data from PHP
            fill: false,
            borderColor: 'rgb(75, 192, 192)', // Line color
            pointBorderColor: 'rgb(75, 192, 192)', // Data point border color
            pointBackgroundColor: 'rgb(75, 192, 192)', // Data point background color
            pointRadius: 5, // Size of data points
            pointHoverRadius: 7, // Hover size of data points
            tension: 0.1,
            borderWidth: 2, // Line thickness
        }]
    };

    const lineConfig = {
        type: 'line',
        data: lineData,
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows height customization
            plugins: {
                title: {
                    display: true,
                    text: 'Sales Trends'
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return '₱' + tooltipItem.raw.toLocaleString(); // Format tooltip value
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    },
                    grid: {
                        display: false, // Remove grid lines for a cleaner look
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Amount (₱)'
                    },
                    beginAtZero: true, // Start Y-axis at 0 for consistency
                    grid: {
                        borderDash: [5, 5], // Dotted grid lines for a more stylish look
                    },
                }
            },
            elements: {
                line: {
                    borderWidth: 3, // Line thickness
                    borderColor: 'rgb(75, 192, 192)',
                },
                point: {
                    radius: 6, // Larger data points for visibility
                    hoverRadius: 8,
                    backgroundColor: 'rgb(75, 192, 192)',
                    borderColor: 'white',
                    borderWidth: 2,
                }
            }
        }
    };

    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, lineConfig);

    // Reset Filters using JavaScript
    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        window.location.href = '{{ route('admin.dashboard') }}'; // Reload page without filters
    });
</script>


@endsection
