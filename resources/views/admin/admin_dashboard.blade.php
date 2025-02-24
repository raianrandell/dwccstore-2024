@extends('layout.admin')

@section('content')
<br>
<h2 class="mt-4 mb-5"></h2>

<div class="row">
    <!-- Total Sales Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #4b6cb7, #182848);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold">Total Sales</h5>
                    <p class="display-5 fw-bold">₱{{ number_format($grandTotal, 2) }}</p>
                </div>
                <div class="icon-container">
                    <i class="fas fa-dollar-sign fa-4x text-light"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Items Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold">Total Items</h5>
                    <p class="display-5 fw-bold">{{ $totalItems }}</p>
                </div>
                <div class="icon-container">
                    <i class="fas fa-cubes fa-4x text-light"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Damage Items Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #ff416c, #ff4b2b);">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title fw-bold">Damaged Items</h5>
                    <p class="display-5 fw-bold">{{ $damageItems }}</p>
                </div>
                <div class="icon-container">
                    <i class="fas fa-exclamation-triangle fa-4x text-light"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        transition: transform 0.3s ease-in-out;
    }

    .icon-container {
        padding: 15px;
    }
</style>
<!-- Line Chart for Daily Sales -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Daily Sales</h4>
            </div>
            <div class="card-body">
                <!-- Date Filter Form -->
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
            label: 'Total quantity of Items Sold',
            data: {!! json_encode($salesQuantities) !!}, // Quantities from PHP
            fill: true,
            borderColor: 'rgb(75, 192, 192)', // Line color
            pointBorderColor: 'rgb(75, 192, 192)', // Data point border color
            pointBackgroundColor: 'rgb(75, 192, 192)', // Data point background color
            tension: 0.1,
            borderWidth: 2, // Line thickness
        }]
    };

    const lineConfig = {
        type: 'line',
        data: lineData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: ''
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Date' },
                    grid: { display: false }
                },
                y: {
                    title: { display: true, text: 'Total quantity of Items Sold' },
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] }
                }
            }
        }
    };

    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, lineConfig);

    // Reset Filters
    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        window.location.href = '{{ route('admin.dashboard') }}';
    });
</script>

@endsection
