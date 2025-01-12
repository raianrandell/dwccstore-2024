@extends('layout.cashier')

@section('title', 'User Profile')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">User Profile</li>
</ol>
<div class="scale-wrapper" style="transform: scale(0.9); transform-origin: top left; width: 111.11%;">
<div class="container">
    <div class="row mb-4 mt-5">
        <!-- User Information Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="emp_id" class="form-label"><strong>Employee ID/Username:</strong></label>
                        <p class="form-control-plaintext">{{ $user->emp_id }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label"><strong>Full Name:</strong></label>
                        <p class="form-control-plaintext">{{ $user->full_name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<br>
    <div class="row">
        <!-- Change Password Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2 alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> <!-- Error Icon -->
                                @foreach ($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success mt-2 alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> <!-- Success Icon -->
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('cashier.updatePassword') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
