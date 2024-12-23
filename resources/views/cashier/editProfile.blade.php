@extends('layout.cashier')

@section('title', 'User Profile')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">User Profile</li>
</ol>

<!-- Display User Profile -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Full Name</th>
            <th>Role</th>
            <th>Username</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ auth()->user()->emp_id }}</td>
            <td>{{ auth()->user()->full_name }}</td>
            <td>{{ auth()->user()->user_role }}</td>
            <td>{{ auth()->user()->username }}</td>
            <td>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
            </td>
        </tr>
    </tbody>
</table>

<!-- Modal for Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('cashier.updateProfile') }}">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Employee ID (Read-Only) -->
                    <div class="mb-3">
                        <label for="employeeId" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="employeeId" value="{{ auth()->user()->emp_id }}" disabled>
                    </div>

                    <!-- Username Field -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', auth()->user()->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password Field (Optional) -->
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password (optional)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="newPassword" name="password" placeholder="Enter new password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="confirmPassword" name="password_confirmation" placeholder="Confirm new password">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
