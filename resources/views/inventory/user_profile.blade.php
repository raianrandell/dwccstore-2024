@extends('layout.inventory')

@section('title', 'User Profile')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">User Profile</li>
</ol>
<div class="scale-wrapper" style="transform: scale(0.9); transform-origin: top left; width: 111.11%;">
<div class="container">
    <div class="row mb-4">
        <!-- User Information Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="emp_id" class="form-label">Employee ID:</label>
                        <p class="form-control-plaintext">{{ $user->emp_id }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name:</label>
                        <p class="form-control-plaintext">{{ $user->full_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role:</label>
                        <p class="form-control-plaintext">{{ $user->user_role }}</p>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <p class="form-control-plaintext">{{ $user->username }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Change Password Section -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.updatePassword') }}" method="POST">
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
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success mt-2">{{ session('success') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
