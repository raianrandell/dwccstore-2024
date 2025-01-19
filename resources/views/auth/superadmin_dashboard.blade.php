@extends ('layout.superadmin')

@section('content')

    <h1 class="mt-4 mb-5">Dashboard</h1>
    <div class="row">
        <!-- Total Users Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9 text-right">
                            <div class="card-title">Total Users</div>
                            <div class="display-4"><b>&nbsp;{{ $totalUsers }}</b></div>
                        </div>
                        <div class="col-3 mt-4">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    
                </div>
            </div>
        </div>
        <!-- Active Users Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9 text-right">
                            <div class="card-title">Active Users</div>
                            <div class="display-4"><b>&nbsp;{{ $activeUsers }}</b></div>
                        </div>
                        <div class="col-3 mt-4">
                            <i class="fas fa-user-check fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    
                </div>
            </div>
        </div>
        <!-- Inactive Users Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9 text-right">
                            <div class="card-title">Inactive Users</div>
                            <div class="display-4"><b>&nbsp;{{ $inactiveUsers }}</b></div>
                        </div>
                        <div class="col-3 mt-4">
                            <i class="fas fa-user-times fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    
                </div>
            </div>
        </div>
    </div>
@endsection
