{{-- resources/views/inventory/section_management.blade.php --}}

@extends('layout.inventory')

@section('title', 'Section Management')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Section Management</li>
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
        <!-- Optional: Add a search bar here -->
    </div>
    <div>
        <!-- Add New Section Button -->
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addSectionModal">
            <i class="fas fa-plus me-1"></i> Add Section
        </button>   
    </div>
</div>

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        List of Sections
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Section Name</th>
                    <th>Date Encoded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $index => $section)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $section->sec_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($section->created_at)->format('m-d-Y') }}</td>
                        <td>
                            <!-- View Button -->
                            <a href="#"
                            class="btn btn-primary btn-sm rounded-circle view_btn" 
                            data-bs-toggle="modal" 
                            data-bs-target="#viewSectionModal"
                            data-section-id="{{ $section->id }}"
                            title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addSectionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addSectionForm" action="{{ route('inventory.addsection') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSectionModalLabel">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Display Validation Errors -->
                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.5rem;"></i>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    <!-- Section Name Input -->
                    <div class="mb-3">
                        <label for="sectionName" class="form-label">Section Name</label>
                        <input type="text" 
                            class="form-control @error('sec_name') is-invalid @enderror" 
                            id="sectionName" 
                            name="sec_name" 
                            value="{{ old('sec_name') }}" 
                            required>
                        @error('sec_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Section Modal -->
<div class="modal fade" id="viewSectionModal" tabindex="-1" aria-labelledby="viewSectionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Section Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6><strong>Section Name:</strong> <span id="viewSectionName"></span></h6>
                <h6><strong>Date Encoded:</strong> <span id="viewSectionDate"></span></h6>
                <hr>
                <h5>Categories under <span id="viewSectionTitle"></span></h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th>Date Encoded</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesList">
                        <!-- Categories will be populated here via JavaScript -->
                        <tr>
                            <td colspan="3" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Reopen the modal if there are validation errors
        @if ($errors->any())
            var addSectionModal = new bootstrap.Modal(document.getElementById('addSectionModal'));
            addSectionModal.show();
        @endif

        // When the modal is hidden, reset the form and remove error messages
        var addSectionModalElement = document.getElementById('addSectionModal');
        addSectionModalElement.addEventListener('hidden.bs.modal', function () {
            // Reset the form, which also clears the input fields
            var form = document.getElementById('addSectionForm');
            form.reset();

            // Remove any displayed validation errors (alert messages)
            var errorAlerts = document.querySelectorAll('.alert.alert-danger');
            errorAlerts.forEach(function(alert) {
                alert.remove();
            });

            // Hide the invalid feedback messages (if any)
            var invalidFeedbacks = document.querySelectorAll('.invalid-feedback');
            invalidFeedbacks.forEach(function(feedback) {
                feedback.style.display = 'none';
            });

            // Remove any 'is-invalid' class added to form inputs
            var invalidInputs = form.querySelectorAll('.is-invalid');
            invalidInputs.forEach(function(input) {
                input.classList.remove('is-invalid');
            });
        });

        // Handle View Section Modal Population
        const viewSectionModal = document.getElementById('viewSectionModal');
        viewSectionModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const sectionId = button.getAttribute('data-section-id'); // Extract info

            // Show loading indicators or clear previous data
            document.getElementById('viewSectionName').textContent = 'Loading...';
            document.getElementById('viewSectionDate').textContent = 'Loading...';
            document.getElementById('viewSectionTitle').textContent = 'Loading...';
            document.getElementById('categoriesList').innerHTML = `
                <tr>
                    <td colspan="3" class="text-center">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            // Fetch section details and categories via AJAX
            fetch(`/inventory/sections/${sectionId}/categories`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Populate the modal fields
                    document.getElementById('viewSectionName').textContent = data.section.sec_name;
                    document.getElementById('viewSectionTitle').textContent = data.section.sec_name;
                    const encodedDate = new Date(data.section.created_at);
                    document.getElementById('viewSectionDate').textContent = encodedDate.toLocaleDateString();

                    // Populate the categories table
                    const categoriesList = document.getElementById('categoriesList');
                    categoriesList.innerHTML = ''; // Clear previous data

                    if(data.categories.length > 0){
                        data.categories.forEach((category, index) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${category.category_name}</td>
                                <td>${new Date(category.created_at).toLocaleDateString()}</td>
                            `;
                            categoriesList.appendChild(row);
                        });
                    } else {
                        categoriesList.innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center">No categories found.</td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching categories:', error);
                    // Display an error message in the modal
                    document.getElementById('viewSectionName').textContent = 'Error';
                    document.getElementById('viewSectionDate').textContent = 'Error';
                    document.getElementById('viewSectionTitle').textContent = 'Error';
                    document.getElementById('categoriesList').innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-danger">Failed to load categories.</td>
                        </tr>
                    `;
                });
        });
    });
</script>

@endsection
