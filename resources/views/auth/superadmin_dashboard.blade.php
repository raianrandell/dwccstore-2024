@extends ('layout.superadmin')

@section('content')
<br>
    <h1 class="mt-4 mb-5"></h1>
    <div class="row">
        <!-- Total Users Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #4b6cb7, #182848);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fw-bold">Total Users</h5>
                        <p class="display-5 fw-bold"><b>&nbsp;{{ $totalUsers }}</b></p>
                    </div>
                    <div class="icon-container">
                        <i class="fas fa-users fa-3x text-light"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Active Users Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fw-bold">Active Users</h5>
                        <p class="display-5 fw-bold"><b>&nbsp;{{ $activeUsers }}</b></p>
                    </div>
                    <div class="icon-container">
                        <i class="fas fa-user-check fa-3x text-light"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Inactive Users Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card text-white shadow-lg border-0 rounded-4 position-relative" style="background: linear-gradient(135deg, #ff416c, #ff4b2b);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fw-bold">Inactive Users</h5>
                        <p class="display-5 fw-bold"><b>&nbsp;{{ $inactiveUsers }}</b></p>
                    </div>
                    <div class="icon-container">
                        <i class="fas fa-user-times fa-3x text-light"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
