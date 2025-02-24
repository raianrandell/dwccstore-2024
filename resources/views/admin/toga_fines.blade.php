@extends('layout.admin')

@section('title', 'Toga & Fines')

@section('content')

<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Toga & Fines</li>
</ol>

@if (session('message'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
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
                    <th>Date Issued</th>
                    <th>Expected Return Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($borrowers as $borrower)
                    <tr>
                        <td>{{ $borrower->student_number }}</td>
                        <td>{{ $borrower->student_name }}</td>
                        <td>
                            <!-- Join borrowed item names into a comma-separated string -->
                            @php
                                $borrowedItemNames = $borrower->borrowedItems
                                    ->map(function ($borrowedItem) {
                                        return $borrowedItem->item->item_name;
                                    })
                                    ->implode(', ');
                            @endphp
                            {{ $borrowedItemNames }}
                        </td>
                        <td>
                            @if ($borrower->borrowedItems->isNotEmpty())
                                {{ \Carbon\Carbon::parse($borrower->borrowedItems->first()->borrowed_date)->format('m-d-Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if ($borrower->borrowedItems->isNotEmpty())
                                {{ \Carbon\Carbon::parse($borrower->borrowedItems->first()->return_date)->format('m-d-Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-circle" data-bs-toggle="modal"
                                data-bs-target="#returnModal" data-borrower='@json($borrower)'
                                data-items='@json($borrower->borrowedItems)'
                                title="Return">
                                <i class="fa-solid fa-arrow-rotate-left"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="returnForm" method="POST" action="{{ route('admin.returnBorrower') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Return Borrowed Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fst-italic">
                        Note: If the return date exceeds the expected date, a fee of 10 pesos per item per day will be charged.
                        <br>For late, damaged or lost items, the student must proceed to the cashier for the corresponding fee.
                    </p>
                    <div id="borrowerInfo" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="borrowedItemsTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Borrowed Date</th>
                                    <th>Expected Return Date</th>
                                    <th>Days Late</th>
                                    <th>Condition</th>
                                    <th>Fee (Days Late)</th>
                                </tr>
                            </thead>
                            <tbody id="borrowedItems"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- <strong>Total Fee: PHP <span id="totalFee">0.00</span></strong> --}}
                    <div id="warningMessage" style="display:none; color: red; font-weight: bold;"></div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (session('error_modal'))
    <div class="modal fade" id="issueModal" tabindex="-1" role="dialog" aria-labelledby="issueModalLabel"
        data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Item Return Issues</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>The following items have issues. Please proceed to the cashier to pay
                            the necessary fees.</strong></p>
                    <ul>
                        @php
                            $totalFees = 0; // Initialize total fees
                            @endphp

                            @foreach (session('problematic_items') as $item)
                                <li>
                                    <strong>Item:</strong> {{ $item['item_name'] }}<br>
                                    <strong>Condition:</strong> {{ $item['condition'] }}<br>
                                    <strong>Days Late:</strong> {{ $item['days_late'] }}<br>
                                    <strong>Late Fee:</strong> PHP {{ number_format($item['late_fee'], 2) }}<br>
                                    @if ($item['condition'] === 'Damaged' || $item['condition'] === 'Lost')
                                        <strong>{{ $item['condition'] }} Fee:</strong> PHP {{ number_format($item['fee'], 2) }}
                                    @endif
                                </li>
                                @php
                                    // Add late fee and damage/lost fee to the total
                                    $totalFees += $item['late_fee'] + ($item['fee'] ?? 0);
                                @endphp
                            @endforeach

                            <hr>
                            <p><strong>Total Fee to Pay:</strong> PHP {{ number_format($totalFees, 2) }}</p>

                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Add Borrower Modal -->
<div class="modal fade" id="addBorrowerModal" tabindex="-1" aria-labelledby="addBorrowerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
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
                                                {{ in_array($item->id, old('item_ids', [])) ? 'checked' : '' }}
                                                {{ ($item->total_quantity - $item->quantity_borrowed - $item->damaged_quantity - $item->lost_quantity) <= 0 ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="item_{{ $item->id }}">
                                                    {{ $item->item_name }}
                                                    @php
                                                        $available = $item->total_quantity - $item->quantity_borrowed - $item->damaged_quantity - $item->lost_quantity;
                                                    @endphp
                                                    @if ($available > 0)
                                                    <span class="text-success">(Available: {{ $available }})</span>
                                                    @endif
                                                    @if ($available <= 0)
                                                        <span class="text-danger">(Out of Stock)</span>
                                                    @endif
                                                </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Borrower Details -->
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="studentId" class="form-label">Student ID</label>
                                <input type="text" class="form-control" id="studentId" name="student_id" value="{{ old('student_id') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="studentName" class="form-label">Student Name</label>
                                <input type="text" class="form-control" id="studentName" name="student_name" value="{{ old('student_name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="dateIssued" class="form-label">Date Issued</label>
                                <input type="date" class="form-control" id="dateIssued" name="date_issued" value="{{ old('date_issued', now()->toDateString()) }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="expectedDateReturned" class="form-label">Expected Return Date</label>
                                <input type="date" class="form-control" id="expectedDateReturned" name="expected_date_returned" value="{{ old('expected_date_returned') }}" required min="{{ now()->toDateString() }}">
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

<!-- JavaScript Section -->
<script>
    $(document).ready(function() {
        // Show issueModal if session('error_modal') is present
        @if (session('error_modal'))
            $('#issueModal').modal('show');
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

        // Reopen Add Borrower Modal if there are validation errors
        @if ($errors->any())
            var addBorrowerModal = new bootstrap.Modal(document.getElementById('addBorrowerModal'));
            addBorrowerModal.show();
        @endif
    });

    // Handle Return Modal
    const returnModal = document.getElementById('returnModal');
    returnModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const borrower = JSON.parse(button.getAttribute('data-borrower'));
        const items = JSON.parse(button.getAttribute('data-items'));

        // Borrower Information
        const borrowerInfo = document.getElementById('borrowerInfo');
        borrowerInfo.innerHTML = `
            <p><strong>Student Name:</strong> ${borrower.student_name} (${borrower.student_number})</p>
        `;

        const borrowedItemsContainer = document.getElementById('borrowedItems');
        borrowedItemsContainer.innerHTML = '';
        let totalFee = 0;

        // Get today's date in UTC (set time to 00:00:00)
        const today = new Date();
        const utcToday = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());

        items.forEach((item) => {
            // Parse dates assuming they are in 'YYYY-MM-DD' format
            const borrowedDateParts = item.borrowed_date.split('-');
            const expectedReturnDateParts = item.return_date.split('-');

            const borrowedDate = new Date(Date.UTC(
                parseInt(borrowedDateParts[0], 10),
                parseInt(borrowedDateParts[1], 10) - 1,
                parseInt(borrowedDateParts[2], 10)
            ));

            const expectedReturnDate = new Date(Date.UTC(
                parseInt(expectedReturnDateParts[0], 10),
                parseInt(expectedReturnDateParts[1], 10) - 1,
                parseInt(expectedReturnDateParts[2], 10)
            ));

            // Calculate days late
            let daysLate = 0;
            if (utcToday > expectedReturnDate.getTime()) {
                const diffInMs = utcToday - expectedReturnDate.getTime();
                daysLate = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
            }

            const lateFee = daysLate * 10;

            borrowedItemsContainer.innerHTML += `
                <tr>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input item-checkbox" id="itemCheck_${item.id}" name="item_ids[]" value="${item.id}" checked>
                            <label class="form-check-label" for="itemCheck_${item.id}">${item.item.item_name}</label>
                        </div>
                    </td>
                    <td>${borrowedDate.toLocaleDateString('en-US')}</td>
                    <td>${expectedReturnDate.toLocaleDateString('en-US')}</td>
                    <td>${daysLate}</td>
                    <td>
                        <select class="form-select condition-select" name="conditions[${item.id}]">
                            <option value="Good" selected>Good</option>
                        </select>
                    </td>
                    <td class="late-fee-cell">
                        ₱${lateFee.toFixed(2)}
                    </td>
                </tr>
            `;
             totalFee += lateFee;
        });

        // Update total fee initially
        updateTotalFee();

         // Disable damage/lost fee input initially if the condition is good
        document.querySelectorAll('.condition-select').forEach(select => {
                const row = select.closest('tr');
                const damageLostFeeInput = row.querySelector('.damage-lost-fee-input');
                if (select.value === 'Good') {
                    damageLostFeeInput.disabled = true;
                } else {
                    damageLostFeeInput.disabled = false;
                }
        });

    });

     // Function to update the total fee
    function updateTotalFee() {
        let totalFee = 0;
        document.querySelectorAll('#borrowedItems tr').forEach(row => {
            const lateFeeCell = row.querySelector('.late-fee-cell');
            const damageLostFeeInput = row.querySelector('.damage-lost-fee-input');

            let lateFee = parseFloat(lateFeeCell.textContent.replace('₱','')) || 0;
            let damageLostFee = parseFloat(damageLostFeeInput.value) || 0;

            totalFee += lateFee + damageLostFee;
        });

        document.getElementById('totalFee').textContent = totalFee.toFixed(2);
    }


     // Event listener to recalculate the total and disable/enable damage/lost fee on change condition
     document.addEventListener('change', function(event) {
        if (event.target.classList.contains('condition-select')) {
              const selectElement = event.target;
              const row = selectElement.closest('tr');
              const damageLostFeeInput = row.querySelector('.damage-lost-fee-input');

              if (selectElement.value === 'Good') {
                  damageLostFeeInput.disabled = true;
              } else {
                  damageLostFeeInput.disabled = false;
                  damageLostFeeInput.focus();
              }
            updateTotalFee();
        }
    });

    // Event listener to recalculate the total on input change in damage/lost fee
    document.addEventListener('input', function(event) {
        if (event.target.classList.contains('damage-lost-fee-input')) {
            updateTotalFee();
        }
    });


</script>

@endsection