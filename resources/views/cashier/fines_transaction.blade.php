@extends('layout.cashier')

@section('title', 'Fines Transaction')

@section('content')

<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Fines Transaction</li>
</ol>

<!-- Session Messages -->
@if(session('message'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
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

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
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
                            @if($borrower->borrowedItems->isNotEmpty())
                                {{ \Carbon\Carbon::parse($borrower->borrowedItems->first()->borrowed_date)->format('m-d-Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($borrower->borrowedItems->isNotEmpty())
                                {{ \Carbon\Carbon::parse($borrower->borrowedItems->first()->return_date)->format('m-d-Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm rounded-circle" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#returnModal" 
                                    data-borrower='@json($borrower)' 
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
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="returnForm" method="POST" action="{{ route('cashier.returnItem') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Return Borrowed Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fst-italic">
                        Note: If the return date exceeds the expected date, a fee of 10 pesos per item per day will be charged.
                        <br>For late, damaged, or lost items, the student must proceed to the cashier for the corresponding fee.
                    </p>
                    <div id="borrowerInfo" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="borrowedItemsTable">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>Item Name</th>
                                    <th>Borrowed Date</th>
                                    <th>Expected Return Date</th>
                                    <th>Days Late</th>
                                    <th>Condition</th>
                                    <th>Fee (Damaged/Lost)</th>
                                    <th>Fee (Days Late)</th>
                                </tr>
                            </thead>
                            <tbody id="borrowedItems"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <strong>Total Fee: PHP <span id="totalFee">0.00</span></strong>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- <!-- Issue Modal for Problematic Items -->
@if(session('error_modal'))
<div class="modal fade" id="issueModal" tabindex="-1" role="dialog" aria-labelledby="issueModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('cashier.processPayment') }}" id="paymentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="issueModalLabel">Item Return Issues</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>The following items have issues:</strong></p>
                    <ul>
                        @foreach(session('problematic_items') as $item)
                            <li>
                                <strong>Item:</strong> {{ $item['item_name'] }}<br>
                                <strong>Condition:</strong> {{ $item['condition'] }}<br>
                                <strong>Fee:</strong> PHP {{ number_format($item['fee'], 2) }}<br>
                                <strong>Days Late:</strong> {{ $item['days_late'] }}<br>
                                <strong>Late Fee:</strong> PHP {{ number_format($item['late_fee'], 2) }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-3">
                        <strong style="color: red;">Total Fee: PHP {{ number_format(collect(session('problematic_items'))->sum(function($item) {
                            return $item['fee'] + ($item['days_late'] * 10);
                        }), 2) }}</strong>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="mt-3">
                        <label for="payment_method">Select Payment Method:</label>
                        <select id="payment_method" class="form-select" onchange="togglePaymentFields()" required>
                            <option value="" disabled selected>Select a method</option>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                        </select>
                    </div>

                    <!-- GCash Reference Number Input -->
                    <div id="gcash_reference" class="mt-3" style="display:none;">
                        <label for="gcash_reference_number">GCash Reference Number:</label>
                        <input type="text" class="form-control" id="gcash_reference_number" name="gcash_reference_number_hidden" placeholder="Enter GCash Reference Number">
                    </div>

                    <!-- Cash Amount Tendered Input -->
                    <div id="cash_amount" class="mt-3" style="display:none;">
                        <label for="cash_tendered">Amount Tendered (in PHP):</label>
                        <input type="number" class="form-control" id="cash_tendered" name="cash_tendered_hidden" placeholder="Enter Cash Tendered" min="0" step="0.01" oninput="calculateChange()" required>
                        <div id="change_amount" class="mt-2 text-danger"></div> <!-- Display change if cash selected -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                    <!-- Hidden inputs for different payment methods -->
                    <input type="hidden" name="total_fee" value="{{ collect(session('problematic_items'))->sum(function($item) {
                        return $item['fee'] + ($item['days_late'] * 10);
                    }) }}">
                    <input type="hidden" name="payment_method" id="selected_payment_method" value="">
                    <input type="hidden" name="gcash_reference_number_hidden" id="gcash_reference_input" value="">
                    <input type="hidden" name="cash_tendered_hidden" id="cash_tendered_input" value="">
                    <input type="hidden" name="change_amount" id="change_amount_input" value="0.00">

                    <button type="submit" class="btn btn-success">Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif --}}

<!-- Late Fee Payment Modal -->
@if(session('late_fee_modal'))
<div class="modal fade" id="lateFeeModal" tabindex="-1" aria-labelledby="lateFeeModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('cashier.payLateFees') }}" id="lateFeePaymentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="lateFeeModalLabel">Pay Fines Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger fst-italic">
                        Please pay the accumulated late fees for the returned items.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Days Late</th>
                                    <th>Late Fee</th>
                                    <th>Condition</th>
                                    <th>Additional Fee (Damage/Lost)</th>
                                    <th>Total Fee (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('late_fee_items') as $item)
                                    <tr>
                                        <td>{{ $item['item_name'] }}</td>
                                        <td>{{ $item['days_late'] }}</td>
                                        <td>₱{{ number_format($item['late_fee'], 2) }}</td>
                                        <td>{{ $item['condition'] ?? 'Good' }}</td>
                                        <td>₱{{ number_format($item['additional_fee'], 2) }}</td>
                                        <td>₱{{ number_format($item['total_fee'], 2) }}</td>
                                        <!-- Hidden inputs to pass data -->
                                        <input type="hidden" name="late_fee_items[{{ $loop->index }}][item_id]" value="{{ $item['item_id'] }}">
                                        <input type="hidden" name="late_fee_items[{{ $loop->index }}][total_fee]" value="{{ $item['total_fee'] }}">
                                        <input type="hidden" name="late_fee_items[{{ $loop->index }}][days_late]" value="{{ $item['days_late'] }}">
                                        <input type="hidden" name="late_fee_items[{{ $loop->index }}][condition]" value="{{ $item['condition'] ?? 'Good' }}">
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <h3>Summary</h3><hr>
                        <b>Late Fee:</b> ₱{{ number_format(collect(session('late_fee_items'))->sum('late_fee'), 2) }}<br>
                        <b>Damage/Lost Fee:</b> ₱{{ number_format(collect(session('late_fee_items'))->sum('additional_fee'), 2) }}<br>
                        <h5 class="mt-2"><strong class="text-success">Total Late Fee: PHP {{ number_format(collect(session('late_fee_items'))->sum('total_fee'), 2) }}</strong></h5>
                    </div>
                    <div class="mt-3">
                        <label for="late_payment_method">Select Payment Method:</label>
                        <select id="late_payment_method" class="form-select" onchange="toggleLatePaymentFields()" required>
                            <option value="" disabled selected>Select a method</option>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                        </select>
                    </div>
                    <div id="late_gcash_reference" class="mt-3" style="display:none;">
                        <label for="late_gcash_reference_number">GCash Reference Number:</label>
                        <input type="text" class="form-control" id="late_gcash_reference_number" name="gcash_reference_number_hidden" placeholder="Enter GCash Reference Number">
                    </div>
                    <div id="late_cash_amount" class="mt-3" style="display:none;">
                        <label for="late_cash_tendered">Amount Tendered (in PHP):</label>
                        <input type="number" class="form-control" id="late_cash_tendered" name="cash_tendered_hidden" placeholder="Enter Cash Tendered" min="0" step="0.01" oninput="calculateLateChange()" required>
                        <div id="late_change_amount" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <input type="hidden" name="payment_method" id="late_selected_payment_method" value="">
                    <input type="hidden" name="change_amount" id="late_change_amount_input" value="0.00">
                    <button type="submit" class="btn btn-success">Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- JavaScript Section -->
<script>
    // Define functions in the global scope to ensure accessibility from inline event handlers
    function togglePaymentFields() {
        var paymentMethod = document.getElementById('payment_method').value;
        var gcashReferenceDiv = document.getElementById('gcash_reference');
        var cashAmountDiv = document.getElementById('cash_amount');

        if(paymentMethod === 'gcash'){
            gcashReferenceDiv.style.display = 'block';
            cashAmountDiv.style.display = 'none';
        } else if(paymentMethod === 'cash') {
            gcashReferenceDiv.style.display = 'none';
            cashAmountDiv.style.display = 'block';
        } else {
            gcashReferenceDiv.style.display = 'none';
            cashAmountDiv.style.display = 'none';
        }

        // Update hidden inputs
        document.getElementById('selected_payment_method').value = paymentMethod;
        document.getElementById('gcash_reference_input').value = paymentMethod === 'gcash' ? document.getElementById('gcash_reference_number').value : '';
        document.getElementById('cash_tendered_input').value = paymentMethod === 'cash' ? document.getElementById('cash_tendered').value : '';
        document.getElementById('change_amount_input').value = paymentMethod === 'cash' ? (parseFloat(document.getElementById('cash_tendered').value || 0) - parseFloat(document.querySelector('input[name="total_fee"]').value || 0)).toFixed(2) : '0.00';
    }

    function calculateChange() {
        var cashTendered = parseFloat(document.getElementById('cash_tendered').value) || 0;
        var totalFee = parseFloat(document.querySelector('input[name="total_fee"]').value) || 0;
        var changeAmount = cashTendered - totalFee;

        if(changeAmount >= 0){
            document.getElementById('change_amount').innerText = `Change: PHP ${changeAmount.toFixed(2)}`;
            document.getElementById('change_amount_input').value = changeAmount.toFixed(2);
        } else {
            document.getElementById('change_amount').innerText = 'Insufficient cash tendered.';
            document.getElementById('change_amount_input').value = '0.00';
        }
    }

    function toggleLatePaymentFields() {
        var paymentMethod = document.getElementById('late_payment_method').value;
        var gcashReferenceDiv = document.getElementById('late_gcash_reference');
        var cashAmountDiv = document.getElementById('late_cash_amount');

        if(paymentMethod === 'gcash'){
            gcashReferenceDiv.style.display = 'block';
            cashAmountDiv.style.display = 'none';
        } else if(paymentMethod === 'cash') {
            gcashReferenceDiv.style.display = 'none';
            cashAmountDiv.style.display = 'block';
        } else {
            gcashReferenceDiv.style.display = 'none';
            cashAmountDiv.style.display = 'none';
        }

        // Update hidden inputs
        document.getElementById('late_selected_payment_method').value = paymentMethod;
        document.getElementById('late_gcash_reference_input').value = paymentMethod === 'gcash' ? document.getElementById('late_gcash_reference_number').value : '';
        document.getElementById('late_change_amount_input').value = paymentMethod === 'cash' ? (parseFloat(document.getElementById('late_cash_tendered').value || 0) - parseFloat(document.querySelector('strong').textContent.replace('PHP ', ''))) : '0.00';
    }

    function calculateLateChange() {
    // Retrieve the amount tendered from the input field
    var cashTendered = parseFloat(document.getElementById('late_cash_tendered').value) || 0;

    // Extract the total late fee from the display text
    var totalFeeText = document.querySelector('#lateFeeModal strong').textContent;
    var totalFee = parseFloat(totalFeeText.replace('Total Late Fee: PHP ', '').replace(',', '')) || 0;

    // Calculate the change
    var changeAmount = cashTendered - totalFee;

    // Update the change amount display and hidden input field
    if (changeAmount >= 0) {
        document.getElementById('late_change_amount').innerText = `Change: PHP ${changeAmount.toFixed(2)}`;
        document.getElementById('late_change_amount_input').value = changeAmount.toFixed(2);
    } else {
        document.getElementById('late_change_amount').innerText = 'Insufficient cash tendered.';
        document.getElementById('late_change_amount_input').value = '0.00';
    }
}


    document.addEventListener('DOMContentLoaded', function() {

        document.getElementById('returnForm').addEventListener('submit', function(event) {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length === 0) {
                event.preventDefault(); // Prevent form submission
                alert('Please select at least one item to return.'); // Display an alert message
                return; // Optionally return false to stop further execution
            }
            // If checkboxes are checked, the form will submit normally
        });
        // Show issueModal if session('error_modal') exists
        @if(session('error_modal'))
            var issueModal = new bootstrap.Modal(document.getElementById('issueModal'));
            issueModal.show();
        @endif

        // Show lateFeeModal if session('late_fee_modal') exists
        @if(session('late_fee_modal'))
            var lateFeeModal = new bootstrap.Modal(document.getElementById('lateFeeModal'));
            lateFeeModal.show();
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

               // Handle Return Modal
               var returnModalElement = document.getElementById('returnModal');
        returnModalElement.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var borrower = JSON.parse(button.getAttribute('data-borrower'));
            var items = JSON.parse(button.getAttribute('data-items'));

            // Borrower Information
            var borrowerInfo = document.getElementById('borrowerInfo');
            borrowerInfo.innerHTML = `<p><strong>Student Name:</strong> ${borrower.student_name} (${borrower.student_number})</p>`;

            var borrowedItemsContainer = document.getElementById('borrowedItems');
            borrowedItemsContainer.innerHTML = '';
            var totalFee = 0;

            // Get today's date in UTC (set time to 00:00:00)
            var today = new Date();
            var utcToday = Date.UTC(today.getFullYear(), today.getMonth(), today.getDate());

            items.forEach(function(item) {
                // Parse dates assuming they are in 'YYYY-MM-DD' format
                var borrowedDateParts = item.borrowed_date.split('-');
                var expectedReturnDateParts = item.return_date.split('-');

                var borrowedDate = new Date(Date.UTC(
                    parseInt(borrowedDateParts[0], 10),
                    parseInt(borrowedDateParts[1], 10) - 1,
                    parseInt(borrowedDateParts[2], 10)
                ));

                var expectedReturnDate = new Date(Date.UTC(
                    parseInt(expectedReturnDateParts[0], 10),
                    parseInt(expectedReturnDateParts[1], 10) - 1,
                    parseInt(expectedReturnDateParts[2], 10)
                ));

                // Calculate days late
                var daysLate = 0;
                if (utcToday > expectedReturnDate.getTime()) {
                    var diffInMs = utcToday - expectedReturnDate.getTime();
                    daysLate = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
                }

                var lateFee = daysLate * 10;
                totalFee += lateFee; // Initialize totalFee with lateFee for good condition items

                borrowedItemsContainer.innerHTML += `
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input item-checkbox" id="itemCheck_${item.id}" name="item_ids[]" value="${item.id}" checked>
                        </td>
                        <td>${item.item.item_name}</td>
                        <td>${borrowedDate.toLocaleDateString('en-US')}</td>
                        <td>${expectedReturnDate.toLocaleDateString('en-US')}</td>
                        <td>${daysLate}</td>
                        <td>
                            <select class="form-select condition-select" id="condition_${item.id}" name="conditions[${item.id}]" data-item-id="${item.id}">
                                <option value="Good" selected>Good</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control fee-input" name="fees[${item.id}]" data-item-id="${item.id}" placeholder="Enter Fee" style="display:none" min="0" step="0.01">
                        </td>
                        <td class="late-fee" data-item-id="${item.id}">₱${lateFee.toFixed(2)}</td>
                    </tr>
                `;
            });

            // Update the total fee display
            document.getElementById('totalFee').textContent = totalFee.toFixed(2);

            // Add event listeners for condition changes
            document.querySelectorAll('.condition-select').forEach(function(select) {
                select.addEventListener('change', function() {
                    var itemId = this.getAttribute('data-item-id');
                    var feeInput = document.querySelector(`.fee-input[data-item-id="${itemId}"]`);

                    if (this.value === 'Good') {
                        feeInput.style.display = 'none';
                        feeInput.value = ''; // Clear any entered value
                        feeInput.removeAttribute('required');
                    } else {
                        feeInput.style.display = 'block';
                        feeInput.setAttribute('required', 'required');
                        feeInput.focus();
                    }
                    updateTotalFee();
                });
            });

            // Add event listeners for fee input changes
            document.querySelectorAll('.fee-input').forEach(function(input) {
                input.addEventListener('input', updateTotalFee);
            });

            // Function to update the total fee
            function updateTotalFee() {
                var newTotalFee = 0;
                document.querySelectorAll('.item-checkbox').forEach(function(checkbox) {
                    if (checkbox.checked) {
                        var row = checkbox.closest('tr');
                        var lateFeeCell = row.querySelector('.late-fee');
                        var feeInput = row.querySelector('.fee-input');
                        var conditionSelect = row.querySelector('.condition-select');
                        var lateFeeAmount = parseFloat(lateFeeCell.textContent.replace('₱', '')) || 0;
                        var additionalFeeAmount = parseFloat(feeInput.value) || 0;

                        if (conditionSelect.value === 'Good') {
                            newTotalFee += lateFeeAmount; // Only late fee for good condition
                        } else {
                            newTotalFee += lateFeeAmount + additionalFeeAmount; // Late fee + additional fee for damaged/lost
                        }
                    }
                });
                document.getElementById('totalFee').textContent = newTotalFee.toFixed(2);
            }
        });
    });

    document.getElementById('lateFeePaymentForm').addEventListener('submit', function (event) {
    // Get the selected payment method
    const paymentMethod = document.getElementById('late_payment_method').value;

    // Proceed only if payment method is cash
    if (paymentMethod === 'cash') {
        // Get cash tendered and total fee
        const cashTendered = parseFloat(document.getElementById('late_cash_tendered').value) || 0;
        const totalFeeText = document.querySelector('#lateFeeModal strong').textContent;
        const totalFee = parseFloat(totalFeeText.replace('Total Late Fee: PHP ', '').replace(',', '')) || 0;

        // Validate cash tendered
        if (cashTendered < totalFee) {
            // Prevent form submission
            event.preventDefault();

            // Show alert
            alert(`Insufficient cash tendered. Please provide at least PHP ${totalFee.toFixed(2)}.`);
            return false;
        }
    }
});

</script>

@endsection
