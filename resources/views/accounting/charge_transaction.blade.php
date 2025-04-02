@extends('layout.accounting')

@section('title', 'Charge Transaction')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Charge Transaction</li>
</ol>

<!-- Alert Placeholder -->
<div id="alertPlaceholder"></div>

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Charge List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Transaction Number</th>
                    <th>Payment Method</th>
                    <th>Charge To</th>
                    <th>Department/Employee Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                <tr data-transaction-id="{{ $transaction->id }}">
                    <td>{{ $transaction->created_at->format('m-d-Y h:i:s a') }}</td>
                    <td>{{ $transaction->transaction_no }}</td>
                    <td>{{ ucfirst($transaction->payment_method) }}</td>
                    <td>{{ $transaction->charge_type }}</td>
                    <td>
                        @if ($transaction->charge_type === 'Department')
                            {{ $transaction->department }}
                        @elseif ($transaction->charge_type === 'Employee')
                            {{ $transaction->faculty_name }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if ($transaction->status == 'Not Paid')
                            <span class="badge bg-danger">{{ $transaction->status }}</span>
                        @else 
                            <span class="badge bg-success">{{ $transaction->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($transaction->status === 'Paid')
                            <button type="button" class="btn btn-secondary btn-sm rounded-circle" title="Payment Completed" disabled>
                                <i class="fa-solid fa-hand-holding-heart"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#viewDetailsModal" title="View Details" onclick="loadTransactionDetails({{ $transaction->id }})">
                                <i class="fa-solid fa-hand-holding-heart"></i>
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No credit transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="viewDetailsModalLabel">
                    <i class="fas fa-info-circle"></i> Transaction Details
                </h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-tabs" id="detailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="transaction-details-tab" data-bs-toggle="tab" data-bs-target="#transaction-details" type="button" role="tab" aria-controls="transaction-details" aria-selected="true">
                            Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">
                            Payment
                        </button>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="detailsTabsContent">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="transaction-details" role="tabpanel" aria-labelledby="transaction-details-tab">
                        <div id="transactionDetailsContent" class="mb-3"></div>
                        <div id="totalAmount" class="text-end">
                            <h5 class="text-danger">TOTAL AMOUNT TO PAY: <span id="totalAmountValue"></span></h5>
                        </div>
                    </div>
                    <!-- Payment Tab -->
                    <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                        <form id="cashPaymentForm">
                            <div class="mb-3">
                                <label for="cashPayment" class="form-label">Enter Cash Payment:</label>
                                <input type="number" id="cashPayment" class="form-control" min="0" step="0.01" placeholder="Enter payment amount">
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success" onclick="processCashPayment()">
                                    <i class="fas fa-check-circle"></i> Submit Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript Section -->
<script>
    // Pass named route URLs to JavaScript using Blade's route helper
    const routes = {
        getTransactionDetails: @json(route('accounting.getTransactionDetails', ['id' => 'TRANSACTION_ID'])),
        updateTransactionStatus: @json(route('accounting.updateTransactionStatus', ['id' => 'TRANSACTION_ID'])),
    };

    /**
     * Load Transaction Details into the Modal
     * @param {number} transactionId
     */
    function loadTransactionDetails(transactionId) {
        // Replace 'TRANSACTION_ID' placeholder with actual transaction ID
        const url = routes.getTransactionDetails.replace('TRANSACTION_ID', transactionId);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    showAlert(data.message || 'Failed to load transaction details.', 'danger');
                    return;
                }

                console.log(data); // Debugging: Inspect the data structure

                // Build Charge To HTML
                let chargeToHTML = '';
                if (data.chargeType === 'Department') {
                    chargeToHTML = `
                        <h4>CHARGE TO: DEPARTMENT</h4>
                        <strong>Department Name:</strong> ${data.department}<br>
                        <strong>Full Name:</strong> ${data.full_name}<br>
                        <strong>ID Number:</strong> ${data.id_number}<br>
                        <strong>Contact Number:</strong> ${data.contact_number}<br>
                    `;
                } else if (data.chargeType === 'Employee') {
                    chargeToHTML = `
                        <h4>CHARGE TO: EMPLOYEE</h4>
                        <strong>Employee Name:</strong> ${data.faculty_name}<br>
                        <strong>ID Number:</strong> ${data.id_number}<br>
                        <strong>Contact Number:</strong> ${data.contact_number}<br>
                    `;
                }

                // Build Items Table HTML
                let itemsHTML = '';
                if (!data.serviceItems || data.serviceItems.length === 0) {
                    itemsHTML = `
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Item Credit List
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    data.items.forEach(item => {
                        itemsHTML += `
                            <tr>
                                <td>${item.item_name}</td>
                                <td>${item.quantity}</td>
                                <td>${parseFloat(item.price).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</td>
                                <td>${parseFloat(item.total).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</td>
                            </tr>
                        `;
                    });

                    itemsHTML += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    // If service items exist, hide the Item Credit List table
                    itemsHTML = '';
                }

                // Build Service Items Table HTML if there are service items
                let serviceItemsHTML = '';
                if (data.serviceItems && data.serviceItems.length > 0) {
                    serviceItemsHTML = `
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Service Items Credit List
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Service Name</th>
                                            <th>Service Type</th>
                                            <th>Number of Copies</th>
                                            <th>Number of Hours</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    data.serviceItems.forEach(service => {
                        serviceItemsHTML += `
                            <tr>
                                <td>${service.service.service_name}</td>
                                <td>${service.service_type}</td>
                                <td>${service.number_of_copies > 0 ? service.number_of_copies : '-'}</td>
                                <td>${service.number_of_hours > 0 ? service.number_of_hours : '-'}</td>
                                <td>${parseFloat(service.price).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</td>
                                <td>${parseFloat(service.total).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</td>
                            </tr>
                        `;
                    });

                    serviceItemsHTML += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                // Combine all HTML
                let detailsHTML = `
                    ${chargeToHTML}
                    <br>
                    ${itemsHTML}
                    ${serviceItemsHTML} <!-- Insert Service Items Table -->
                `;

                // Update the modal content
                document.getElementById('transactionDetailsContent').innerHTML = detailsHTML;
                document.getElementById('totalAmountValue').innerText = parseFloat(data.totalAmount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
            })
            .catch(error => {
                console.error('Error loading transaction details:', error);
                showAlert('Failed to load transaction details. Please try again.', 'danger');
            });

        // Store the transaction ID in the modal for later use
        document.getElementById('viewDetailsModal').setAttribute('data-transaction-id', transactionId);
    }

    /**
     * Process Cash Payment
     */
    function processCashPayment() {
        const modal = document.getElementById('viewDetailsModal');
        const transactionId = modal.getAttribute('data-transaction-id');
        const cashPayment = document.getElementById('cashPayment').value;

        if (!cashPayment || cashPayment <= 0) {
            showAlert('Please enter a valid cash payment amount.', 'danger');
            return;
        }

        // Disable the button to prevent multiple submissions
        const submitButton = document.querySelector('button.btn-success');
        submitButton.disabled = true;

        // Replace 'TRANSACTION_ID' placeholder with actual transaction ID
        const url = routes.updateTransactionStatus.replace('TRANSACTION_ID', transactionId);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ cashPayment })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Display Success Alert
                showAlert('Payment processed successfully.', 'success');

                // Update the status in the table
                const row = document.querySelector(`tr[data-transaction-id="${transactionId}"]`);
                if (row) {
                    const statusCell = row.querySelector('td:nth-child(6)'); // 6th column is the status cell
                    if (statusCell) {
                        statusCell.innerHTML = '<span class="badge bg-success">Paid</span>';
                    }

                    const actionCell = row.querySelector('td:nth-child(7)');
                    if (actionCell) {
                        // Replace the Action button with a disabled button
                        actionCell.innerHTML = `
                            <button type="button" class="btn btn-secondary btn-sm rounded-circle" title="Payment Completed" disabled>
                                <i class="fa-solid fa-check-circle"></i>
                            </button>
                        `;
                    }
                }

                // Close the modal
                const bootstrapModal = bootstrap.Modal.getInstance(modal);
                if (bootstrapModal) {
                    bootstrapModal.hide();
                }

                // Optionally, reset the payment form
                document.getElementById('cashPaymentForm').reset();

            } else {
                showAlert(data.message || 'Error processing payment. Please try again.', 'danger');
            }

            // Re-enable the button
            submitButton.disabled = false;
        })
        .catch(error => {
            console.error('Error processing payment:', error);
            showAlert('An unexpected error occurred. Please try again.', 'danger');
            submitButton.disabled = false;
        });
    }

    /**
     * Display Alert Messages
     * @param {string} message - The alert message
     * @param {string} type - The alert type ('success', 'danger', etc.)
     */
    function showAlert(message, type) {
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2 fa-lg"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertPlaceholder.append(wrapper);

        // Automatically remove the alert after 2.5 seconds
        setTimeout(() => {
            const alert = bootstrap.Alert.getInstance(wrapper.querySelector('.alert'));
            if (alert) {
                alert.close();
            }
        }, 2500);
    }
</script>

<!-- Optional: Custom CSS for Disabled Buttons -->
<style>
    .btn-secondary[disabled] {
        opacity: 0.65;
        cursor: not-allowed;
    }
</style>

@endsection