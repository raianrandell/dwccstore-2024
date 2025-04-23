@extends('layout.accounting')

@section('title', 'Charge Transaction')

@section('content')
<ol class="breadcrumb mb-3 mt-5 p-2 rounded">
    {{-- <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}" class="text-decoration-none">Home</a></li>
    <li class="breadcrumb-item active">Charge Transaction</li> --}}
</ol>

<!-- Alert Placeholder -->
<div id="alertPlaceholder"></div>

<div class="card mb-4 shadow-sm">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Charge List
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="datatablesSimple" class="table table-bordered table-hover">
                <thead>
                    <tr class="bg-secondary text-white">
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
                        <td class="text-center">
                            @if ($transaction->status === 'Paid')
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill" title="Payment Completed" disabled>
                                    <i class="fa-solid fa-hand-holding-heart"></i> Paid
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#viewDetailsModal" title="View Details" onclick="loadTransactionDetails({{ $transaction->id }})">
                                    <i class="fa-solid fa-hand-holding-heart"></i> View Details
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
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailsModalLabel">
                    <i class="fas fa-info-circle"></i> Transaction Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-tabs" id="detailsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="transaction-details-tab" data-bs-toggle="tab" data-bs-target="#transaction-details" type="button" role="tab" aria-controls="transaction-details" aria-selected="true">
                            <i class="fas fa-list me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">
                            <i class="fas fa-money-bill-alt me-1"></i> Payment
                        </button>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="detailsTabsContent">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="transaction-details" role="tabpanel" aria-labelledby="transaction-details-tab">
                        <div class="mb-3">
                            <strong class="fs-5"><i class="fas fa-receipt me-1"></i> Transaction Number:</strong> <span id="transactionNoDisplay"></span>
                        </div>
                        <div id="transactionDetailsContent" class="mb-3"></div>

                    </div>
                    <!-- Payment Tab -->
                    <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                        <div class="mb-3">
                            <h5 class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> TOTAL AMOUNT TO PAY: <span id="paymentTotalAmount"></span></h5>
                        </div>
                        <form id="cashPaymentForm">
                            <div class="mb-3">
                                <label for="cashPayment" class="form-label fw-bold">Enter Cash Payment:</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" id="cashPayment" class="form-control" min="0" step="0.01" placeholder="Enter payment amount">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="changeAmount" class="form-label fw-bold">Change:</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="changeAmount" class="form-control" value="0.00" readonly>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success btn-lg" onclick="processCashPayment()">
                                    <i class="fas fa-check-circle me-1"></i> Submit Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
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

    let currentTotalAmount = 0; // Store the total amount to pay
    let currentTransactionNo = ''; // Store the transaction number

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

                // Store the transaction number
                currentTransactionNo = data.transaction_no;

                // Update the transaction number display in the details tab
                document.getElementById('transactionNoDisplay').innerText = currentTransactionNo;

                // Build Charge To HTML
                let chargeToHTML = '';
                if (data.charge_type === 'Department') {
                    chargeToHTML = `
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-building me-1"></i> CHARGE TO: DEPARTMENT</h4>
                            <p><strong>Department Name:</strong> ${data.department}</p>
                            <p><strong>Full Name:</strong> ${data.full_name}</p>
                            <p><strong>ID Number:</strong> ${data.id_number}</p>
                            <p><strong>Contact Number:</strong> ${data.contact_number}</p>
                        </div>
                    `;
                } else if (data.charge_type === 'Employee') {
                    chargeToHTML = `
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading"><i class="fas fa-user me-1"></i> CHARGE TO: EMPLOYEE</h4>
                            <p><strong>Employee Name:</strong> ${data.faculty_name}</p>
                            <p><strong>ID Number:</strong> ${data.id_number}</p>
                            <p><strong>Contact Number:</strong> ${data.contact_number}</p>
                        </div>
                    `;
                }

                // Build Items Table HTML
                let itemsHTML = '';
                if (!data.serviceItems || data.serviceItems.length === 0) {
                    itemsHTML = `
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-box me-1"></i>
                                Item Credit List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-success text-white">
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
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-concierge-bell me-1"></i>
                                Service Items Credit List
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-success text-white">
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
                        </div>
                    `;
                }

                // Combine all HTML
                let detailsHTML = `
                    ${chargeToHTML}
                    ${itemsHTML}
                    ${serviceItemsHTML}
                `;

                // Update the modal content
                document.getElementById('transactionDetailsContent').innerHTML = detailsHTML;

                // Store the total amount
                currentTotalAmount = parseFloat(data.totalAmount);

                // Update the payment tab with the total amount
                document.getElementById('paymentTotalAmount').innerText = currentTotalAmount.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
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
        const cashPayment = parseFloat(document.getElementById('cashPayment').value);
        const changeAmountInput = document.getElementById('changeAmount');

        if (!cashPayment || cashPayment <= 0) {
            showAlert('Please enter a valid cash payment amount.', 'danger');
            return;
        }

        if (cashPayment < currentTotalAmount) {
            showAlert('Cash payment is less than the total amount.', 'danger');
            return;
        }

        // Calculate change
        const change = cashPayment - currentTotalAmount;

        // Format the change amount
        const formattedChange = change.toLocaleString('en-PH', {
            style: 'currency',
            currency: 'PHP'
        });
        changeAmountInput.value = formattedChange;


        // Disable the button to prevent multiple submissions
        const submitButton = document.querySelector('button.btn-success');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

        // Replace 'TRANSACTION_ID' placeholder with actual transaction ID
        const url = routes.updateTransactionStatus.replace('TRANSACTION_ID', transactionId);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ cashPayment: cashPayment }) // Send the actual cash payment
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
                        statusCell.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid</span>';
                    }

                    const actionCell = row.querySelector('td:nth-child(7)');
                    if (actionCell) {
                        // Replace the Action button with a disabled button
                        actionCell.innerHTML = `
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill" title="Payment Completed" disabled>
                                <i class="fa-solid fa-hand-holding-heart"></i> Paid
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
                changeAmountInput.value = "₱0.00"; // Reset change

            } else {
                showAlert(data.message || 'Error processing payment. Please try again.', 'danger');
            }

            // Re-enable the button
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-check-circle me-1"></i> Submit Payment';
        })
        .catch(error => {
            console.error('Error processing payment:', error);
            showAlert('An unexpected error occurred. Please try again.', 'danger');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-check-circle me-1"></i> Submit Payment';
        });
    }

    // Listen for input changes in cashPayment to update change real-time
    document.getElementById('cashPayment').addEventListener('input', function() {
        const cashPayment = parseFloat(this.value) || 0; // Default to 0 if empty
        const changeAmountInput = document.getElementById('changeAmount');
        let change = cashPayment - currentTotalAmount;

        if (change < 0) {
            changeAmountInput.value = "₱0.00";
        } else {
            const formattedChange = change.toLocaleString('en-PH', {
                style: 'currency',
                currency: 'PHP'
            });
            changeAmountInput.value = formattedChange;
        }
    });


    /**
     * Display Alert Messages
     * @param {string} message - The alert message
     * @param {string} type - The alert type ('success', 'danger', etc.)
     */
    function showAlert(message, type) {
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2 fa-lg"></i>
                <div>${message}</div>
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

    // DataTable Configuration
    $(document).ready(function() {
        $('#datatablesSimple').DataTable({
            responsive: true,
            order: [[0, 'desc']], // Order by date/time column (first column) in descending order
            language: {
                searchPlaceholder: "Search transactions..."
            }
        });
    });
</script>

<!-- Optional: Custom CSS for Disabled Buttons -->
<style>
    .btn-outline-success[disabled] {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table th,
    .table td {
        white-space: nowrap;
    }
</style>

@endsection