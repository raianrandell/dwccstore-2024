@extends('layout.inventory')

@section('title', 'Services')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Services</a></li>
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
<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        <i class="fas fa-plus me-1"></i> Add New Service
    </button>
</div>

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-table me-1"></i>
            Services List
        </div>
    </div>
    <div class="card-body">
        <!-- Services Table -->
        <table id="datatablesSimple" class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td>{{ $service->service_name }}</td>
                        <td>
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-primary rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                                <i class="fas fa-edit"></i> 
                            </button>
                            <!-- Delete Button -->
                            <button type="button" class="btn btn-danger rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#deleteServiceModal{{ $service->id }}">
                                <i class="fas fa-trash"></i> 
                            </button>
                        </td>
                    </tr>
                    <!-- Edit Service Modal -->
                    <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1" aria-labelledby="editServiceModalLabel{{ $service->id }}" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('inventory.update_service', $service->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editServiceModalLabel{{ $service->id }}"><i class="fas fa-edit me-2"></i>Edit Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Service Name Input -->
                                        <div class="mb-3">
                                            <label for="service_name" class="form-label">Service Name</label>
                                            <input type="text" id="service_name" name="service_name" class="form-control @error('service_name') is-invalid @enderror" value="{{ old('service_name', $service->service_name) }}" required>
                                            @error('service_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Service Modal -->
                    <div class="modal fade" id="deleteServiceModal{{ $service->id }}" tabindex="-1" aria-labelledby="deleteServiceModalLabel{{ $service->id }}" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('inventory.delete_service', $service->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteServiceModalLabel{{ $service->id }}"><i class="fas fa-trash me-2"></i>Delete Service</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete the service "<strong>{{ $service->service_name }}</strong>"?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-danger">
                                            Delete
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">No services available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('inventory.store_service') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel"><i class="fas fa-plus me-2"></i>Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Service Name Input -->
                    <div class="mb-3">
                        <label for="service_name" class="form-label">Service Name</label>
                        <input type="text" id="service_name" name="service_name" class="form-control @error('service_name') is-invalid @enderror" value="{{ old('service_name') }}" required>
                        @error('service_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript to focus on the Service Name field when the modal is shown
    $('#addServiceModal').on('shown.bs.modal', function () {
        $('#service_name').trigger('focus');
    });
</script>

@endsection
