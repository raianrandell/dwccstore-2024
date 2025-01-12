@extends('layout.cashier')

@section('title', 'Fines Transaction')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Fines Transaction</li>
</ol>

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Toga Fines List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Item Borrowed</th>
                    <th>Quantity Borrowed</th>
                    <th>Expected Return Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($borrowers as $borrower)
                    <tr>
                        <td>{{ $borrower->student_id }}</td>
                        <td>{{ $borrower->student_name }}</td>
                        <td>{{ $borrower->item_names }}</td>
                        <td>{{ $borrower->quantity }}</td>
                        <td>{{ $borrower->expected_date_returned }}</td>
                        <td>
                            <!-- Button to trigger modal -->
                            <button class="btn btn-danger btn-sm rounded-circle view_btn" title="Return" 
                                    onclick="showFineModal('{{ $borrower->id }}', 
                                                          '{{ $borrower->student_name }}', 
                                                          '{{ $borrower->item_names }}', 
                                                          '{{ $borrower->expected_date_returned }}')">
                                                          <i class="fa-solid fa-arrow-rotate-left"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="fineModal" tabindex="-1" aria-labelledby="fineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="fineModalLabel">Return & Fine Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Student Name:</strong> <span id="modalStudentName"></span></p>
                <p><strong>Item Borrowed:</strong> <span id="modalItemName"></span></p>
                <p><strong>Days Late:</strong> <span id="modalDaysLate"></span></p>
                <p><strong>Total Fine:</strong> ₱<span id="modalFineAmount"></span></p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('processFinePayment') }}" method="POST" id="paymentForm">
                    @csrf
                    <input type="hidden" id="borrowerId" name="borrower_id">

                    <!-- Payment Method Selection -->
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label"><strong>Payment Method:</strong></label>
                        <select id="paymentMethod" name="payment_method" class="form-select" required onchange="togglePaymentFields()">
                            <option value="" selected disabled>Select Payment Method</option>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                        </select>
                    </div>

                    <!-- Cash Payment Fields -->
                    <div id="cashFields" style="display: none;">
                        <div class="mb-3">
                            <label for="amountTendered" class="form-label"><strong>Amount Tendered:</strong></label>
                            <input type="number" id="amountTendered" name="amount_tendered" class="form-control" min="0" step="0.01" onchange="calculateChange()" required>
                        </div>
                        <div class="mb-3">
                            <label for="change" class="form-label"><strong>Change:</strong></label>
                            <input type="text" id="change" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- GCash Payment Fields -->
                    <div id="gcashFields" style="display: none;">
                        <div class="mb-3">
                            <label for="referenceNumber" class="form-label"><strong>GCash Reference Number:</strong></label>
                            <input type="text" id="referenceNumber" name="gcash_reference_number" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Pay</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    function showFineModal(id, studentName, itemName, expectedDate) {
        const currentDate = new Date();
        const expectedReturnDate = new Date(expectedDate);
        const daysLate = Math.floor((currentDate - expectedReturnDate) / (1000 * 60 * 60 * 24));
        const fineAmount = daysLate * 20; // 20 PHP per day

        // Populate modal fields
        document.getElementById('modalStudentName').textContent = studentName;
        document.getElementById('modalItemName').textContent = itemName;
        document.getElementById('modalDaysLate').textContent = daysLate > 0 ? daysLate : 0;
        document.getElementById('modalFineAmount').textContent = daysLate > 0 ? fineAmount : 0;
        document.getElementById('borrowerId').value = id;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('fineModal'));
        modal.show();
    }

    function togglePaymentFields() {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const cashFields = document.getElementById('cashFields');
        const gcashFields = document.getElementById('gcashFields');

        if (paymentMethod === 'cash') {
            cashFields.style.display = 'block';
            gcashFields.style.display = 'none';
            document.getElementById('referenceNumber').removeAttribute('required');
            document.getElementById('amountTendered').setAttribute('required', 'required');
        } else if (paymentMethod === 'gcash') {
            cashFields.style.display = 'none';
            gcashFields.style.display = 'block';
            document.getElementById('amountTendered').removeAttribute('required');
            document.getElementById('referenceNumber').setAttribute('required', 'required');
        } else {
            cashFields.style.display = 'none';
            gcashFields.style.display = 'none';
        }
    }

    function calculateChange() {
        const fineAmount = parseFloat(document.getElementById('modalFineAmount').textContent);
        const amountTendered = parseFloat(document.getElementById('amountTendered').value || 0);
        const change = amountTendered - fineAmount;
        document.getElementById('change').value = change > 0 ? change.toFixed(2) : '0.00';
    }

</script>
@endsection
