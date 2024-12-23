@extends('layout.admin')

@section('title', 'Toga & Fines')



@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active"><a href="{{ route('admin.toga_fines') }}">Toga & Fines</a></li>
</ol>

@if(session('message'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <!-- You can place a search bar here if needed -->
    </div>
    <div>
        <a href="#" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addBorrowerModal">
            <i class="fas fa-plus me-1"></i> Add Borrower
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Borrower List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Item Borrowed</th>
                    <th>Quantity</th>
                    <th>Date Issued</th>
                    <th>Expected Return Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($borrowers as $borrower)
                    <tr>
                        <td>{{ $borrower->student_id }}</td>
                        <td>{{ $borrower->student_name }}</td>
                        <td>{{ $borrower->item_names }}</td>
                        <td>{{ $borrower->quantity }}</td> <!-- Display the quantity -->
                        <td>{{ $borrower->date_issued }}</td>
                        <td>{{ $borrower->expected_date_returned }}</td>
                        <td>
                            <button 
                                type="button" 
                                class="btn btn-danger btn-sm rounded-circle view_btn" 
                                data-id="{{ $borrower->id }}" 
                                data-name="{{ $borrower->student_name }}" 
                                data-items="{{ $borrower->item_names }}" 
                                data-quantity="{{ $borrower->quantity }}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#returnConfirmationModal">
                                <i class="fa-solid fa-arrow-rotate-left"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Borrower Modal -->
<div class="modal fade" id="addBorrowerModal" tabindex="-1" aria-labelledby="addBorrowerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg"> <!-- Use modal-lg to make it larger -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBorrowerModalLabel">Add Borrower</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-danger fst-italic">Note: If the return date exceeds the expected date, a fee of 20 pesos per item per day will be charged.</p>

                <form action="{{ route('admin.addBorrower') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Left Column: Item Selection -->
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label for="items" class="form-label">Select Items to Borrow</label>
                                <div id="items">
                                    @foreach ($items as $item)
                                        <div class="form-check">
                                            <input 
                                                class="form-check-input item-checkbox" 
                                                type="checkbox" 
                                                id="item_{{ $item->id }}" 
                                                name="item_ids[]" 
                                                value="{{ $item->id }}" 
                                                {{ in_array($item->id, old('item_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="item_{{ $item->id }}">
                                                {{ $item->item_name }} (Available: {{ $item->total_quantity - $item->quantity_borrowed }})
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Form Inputs -->
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="studentId" class="form-label">Student ID</label>
                                <input type="text" class="form-control" id="studentId" name="student_id" 
                                    value="{{ old('student_id') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentName" class="form-label">Student Name</label>
                                <input type="text" class="form-control" id="studentName" name="student_name" 
                                    value="{{ old('student_name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="dateIssued" class="form-label">Date Issued</label>
                                <input type="date" class="form-control" id="dateIssued" name="date_issued" 
                                    value="{{ old('date_issued', now()->toDateString()) }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="expectedDateReturned" class="form-label">Expected Return Date</label>
                                <input type="date" class="form-control" id="expectedDateReturned" name="expected_date_returned" 
                                    value="{{ old('expected_date_returned') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Borrower</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Return Confirmation Modal -->
<div class="modal fade" id="returnConfirmationModal" tabindex="-1" aria-labelledby="returnConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnConfirmationModalLabel">Confirm Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Borrower Name:</strong> <span id="borrowerName"></span></p>
                <hr>
                <form id="returnForm" method="POST" action="">
                    @csrf
                    <div id="itemConditionsContainer">
                        <!-- Checkboxes and dropdowns will be populated here dynamically -->
                    </div>
                    <div class="text-danger fst-italic mt-3">
                        Note: If an item is not checked, it will not be marked as returned.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
   document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2
        $('#item').select2({
            allowClear: true,
            placeholder: "Select Item To Be Borrowed",
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: $('#addBorrowerModal')
        });

        // Reopen modal if there are validation errors
        @if ($errors->any())
            var addBorrowerModal = new bootstrap.Modal(document.getElementById('addBorrowerModal'));
            addBorrowerModal.show();
        @endif

        // Auto-hide success alert after 2 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.classList.add('fade'); // Add fade class for smooth transition
                setTimeout(() => {
                    successAlert.remove(); // Remove from DOM after fade transition
                }, 500); // Match the Bootstrap fade duration (0.5 seconds)
            }, 2000); // Wait for 2 seconds
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const returnModal = document.getElementById('returnConfirmationModal');
        const borrowerName = document.getElementById('borrowerName');
        const itemConditionsContainer = document.getElementById('itemConditionsContainer');
        const returnForm = document.getElementById('returnForm');

        document.querySelectorAll('.view_btn').forEach(button => {
            button.addEventListener('click', function () {
                // Fetch data attributes from button
                const borrowerId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const items = this.getAttribute('data-items').split(','); // Split item names into an array
                const quantity = this.getAttribute('data-quantity');

                // Set borrower name in modal
                borrowerName.textContent = name;

                // Populate items in modal
                itemConditionsContainer.innerHTML = '';
                items.forEach((item, index) => {
                    const itemHtml = `
                        <div class="d-flex align-items-center mb-3">
                            <div class="form-check me-3">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="item_${index}" 
                                    name="items[${index}][returned]" 
                                    value="${item.trim()}">
                                <label class="form-check-label" for="item_${index}">
                                    ${item.trim()} (Qty: ${quantity})
                                </label>
                            </div>
                            <div class="flex-grow-1">
                                <select name="items[${index}][condition]" class="form-select" disabled>
                                    <option value="">Select Condition</option>
                                    <option value="Good">Good</option>
                                    <option value="Damaged">Damaged</option>
                                    <option value="Lost">Lost</option>
                                </select>
                            </div>
                        </div>
                    `;
                    itemConditionsContainer.insertAdjacentHTML('beforeend', itemHtml);
                });

                // Enable dropdown when checkbox is checked
                itemConditionsContainer.querySelectorAll('.form-check-input').forEach((checkbox, idx) => {
                    checkbox.addEventListener('change', function () {
                        const dropdown = itemConditionsContainer.querySelectorAll('.form-select')[idx];
                        dropdown.disabled = !this.checked;
                    });
                });

                // Update form action dynamically
                returnForm.action = `/admin/returnBorrower/${borrowerId}`;
            });
        });
    });


</script>

@endsection
