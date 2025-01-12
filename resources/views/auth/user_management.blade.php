@extends('layout.superadmin')

@section('title', 'Super Admin User Management')

@section('content')


    <h1 class="mt-4">User Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">User Management</li>
    </ol>

<!-- Success and Error Messages -->
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        <div>
            {{ Session::get('success') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(Session::has('fail'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
        <div>
            {{ Session::get('fail') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

    <!-- Add button next to the search -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <!-- You can place a search bar here if needed -->
        </div>
        <div>
            <!-- Add New User Button -->
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-1"></i> Add New User
            </button>            
        </div>
    </div>

    <style></style>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Users List
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Employee ID/Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users->where('user_role', '!=', 'Super Admin') as $user)
                        <tr>
                            <td>{{ $user->emp_id }}</td>
                            <td>{{ $user->full_name }}</td>
                            <td>{{ $user->user_role }}</td>
                            <td>
                                @if($user->user_status === 'Active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <!-- Edit Button -->
                                <a href="#"
                                class="btn btn-primary btn-sm rounded-circle edit-user-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editUserModal"
                                data-user-id="{{ $user->id }}"
                                data-employee-id="{{ $user->emp_id }}"
                                data-full-name="{{ $user->full_name }}"
                                data-role="{{ $user->user_role }}"
                                data-username="{{ $user->username }}"
                                data-status="{{ $user->user_status }}"
                                title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Change Password Button -->
                                    <a href="#"
                                    class="btn btn-warning btn-sm rounded-circle change-password-btn"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#changePasswordModal"
                                    data-user-id="{{ $user->id }}"
                                    title="Change Password">
                                    <i class="fas fa-key"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
                
            </table>
        </div>
    </div>

<!-- Modal for Add New User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addUserForm" method="POST" action="{{ route('superadmin.add_user') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                     @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <!-- Add User Form Fields -->
                    <div class="mb-3">
                        <label for="employeeId" class="form-label">Employee ID</label>
                        <input type="text" class="form-control @error('employeeId') is-invalid @enderror" id="employeeId" name="employeeId" value="{{ old('employeeId') }}" required>
                        @error('employeeId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Full Name Field -->
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('fullName') is-invalid @enderror" id="fullName" name="fullName" value="{{ old('fullName') }}" required>
                        @error('fullName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Role Field -->
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select Role</option>
                            <option value="Cashier" {{ old('role') == 'Cashier' ? 'selected' : '' }}>Cashier</option>
                            <option value="Inventory" {{ old('role') == 'Inventory' ? 'selected' : '' }}>Inventory</option>
                            <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Accounting" {{ old('role') == 'Accounting' ? 'selected' : '' }}>Accounting</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Status Field -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Username Field -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
        
                <div class="modal-body">
                    <!-- Employee ID (Read-Only) -->
                    <div class="mb-3">
                        <label for="editEmployeeId" class="form-label">Employee ID</label>
                        <input type="text" class="form-control" id="editEmployeeId" name="employeeId" readonly>
                    </div>
                    <!-- Full Name Field -->
                    <div class="mb-3">
                        <label for="editFullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('fullName') is-invalid @enderror" id="editFullName" name="fullName" required>
                        @error('fullName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Username Field -->
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="editUsername" name="username" required readonly>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Role Field -->
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="editRole" name="role" required>
                            <option value="" disabled>Select Role</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Inventory">Inventory</option>
                            <option value="Admin">Admin</option>
                            <option value="Accounting">Accounting</option>
                            <!-- Add other roles as needed -->
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Status Field -->
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="editStatus" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
        
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changePasswordForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- New Password Field -->
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                    </div>
                    <!-- Confirm New Password Field -->
                    <div class="mb-3">
                        <label for="newPassword_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="newPassword_confirmation" name="newPassword_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="changePasswordSubmitBtn">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Toggle Password Script -->
<script type="text/javascript" src="{{ asset('javascripts/toggle_password.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeIdInput = document.getElementById('employeeId');
        const usernameInput = document.getElementById('username');

        // Update username field when employee ID changes
        employeeIdInput.addEventListener('input', function () {
            usernameInput.value = employeeIdInput.value;
        });

        // Open the modal if there are validation errors
        @if ($errors->any())
            var addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();

            // Focus on the first invalid input
            var firstErrorElement = document.querySelector('.is-invalid');
            if (firstErrorElement) {
                firstErrorElement.focus();
            }
        @endif
    });

    document.addEventListener('DOMContentLoaded', function () {
        const changePasswordSubmitBtn = document.getElementById('changePasswordSubmitBtn');
        const changePasswordForm = document.getElementById('changePasswordForm');
        
        changePasswordSubmitBtn.addEventListener('click', function () {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('newPassword_confirmation').value;

            if (newPassword.length < 5 || newPassword.length > 12) {
                window.alert('Password must be between 5 and 12 characters.');
                return;
            }

            if (newPassword !== confirmPassword) {
                window.alert('Password confirmation does not match.');
                return;
            }

            // If validation passes, submit the form
            changePasswordForm.submit();
        });

        // Edit User Modal
        var editUserModal = document.getElementById('editUserModal')
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget // Button that triggered the modal
            var userId = button.getAttribute('data-user-id')
            var employeeId = button.getAttribute('data-employee-id')
            var fullName = button.getAttribute('data-full-name')
            var role = button.getAttribute('data-role')
            var status = button.getAttribute('data-status')
            var username = button.getAttribute('data-username')

            // Update the modal's content.
            var modalTitle = editUserModal.querySelector('.modal-title')
            var employeeIdInput = editUserModal.querySelector('#editEmployeeId')
            var fullNameInput = editUserModal.querySelector('#editFullName')
            var roleSelect = editUserModal.querySelector('#editRole')
            var statusSelect = editUserModal.querySelector('#editStatus')
            var form = editUserModal.querySelector('#editUserForm')
            var usernameInput = editUserModal.querySelector('#editUsername')

            employeeIdInput.value = employeeId
            fullNameInput.value = fullName
            roleSelect.value = role
            usernameInput.value = username
            statusSelect.value = status

            // Set the form action dynamically
            form.action = '{{ route('superadmin.edit_user', ['id' => '__id__']) }}'.replace('__id__', userId);
        })
    });

    // Clear form fields when modal is hidden
    var addUserModal = document.getElementById('addUserModal');
    addUserModal.addEventListener('hidden.bs.modal', function () {
        // Reset the form
        document.getElementById('addUserForm').reset();
    });
    // Change Password Modal
var changePasswordModal = document.getElementById('changePasswordModal')
changePasswordModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget // Button that triggered the modal
    var userId = button.getAttribute('data-user-id')

    // Set the form action dynamically
    var form = changePasswordModal.querySelector('#changePasswordForm')
    form.action = '{{ route('superadmin.change_password', ['id' => '__id__']) }}'.replace('__id__', userId);
})
</script>

@endsection