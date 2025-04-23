@extends('layout.cashier')

@section('title', 'Credit Transaction')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Credit Transaction</li>
    </ol>

    <!-- Alert Placeholder -->
    <div id="alertPlaceholder"></div>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Credit List
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Transaction Number</th>
                        <th>Charge To</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($creditTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('m-d-Y h:i:s a') }}</td>
                            <td>{{ $transaction->transaction_no }}</td>
                            <td>
                                @if ($transaction->charge_type == 'Department')
                                    Department
                                @elseif($transaction->charge_type == 'Employee')
                                    Employee <!-- Corrected from Employee if using Employee -->
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
                                {{-- Update onclick to pass only the ID --}}
                                <button type="button" class="btn btn-primary btn-sm rounded-circle view_btn"
                                    data-bs-toggle="modal" data-bs-target="#transactionModal"
                                    onclick="showTransactionDetails({{ $transaction->id }})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        {{-- Make modal larger to accommodate tables --}}
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">
                         <i class="fas fa-receipt me-2"></i> Transaction Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Placeholder for loading indicator --}}
                    <div id="modal-loading" class="text-center my-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading details...</p>
                    </div>

                    {{-- Placeholders for dynamic content --}}
                    <div id="modal-content-details">
                        <div id="modal-charge-info" class="mb-3"></div>
                        <div id="modal-status-info" class="mb-3"></div>
                        <div id="modal-items-table" class="mb-3"></div>
                        <div id="modal-service-items-table" class="mb-3"></div>
                        <div id="modal-total-amount" class="text-end"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass the route URL to JavaScript
        const creditDetailsRoute = @json(route('cashier.getCreditTransactionDetails', ['id' => 'TRANSACTION_ID']));

        function showTransactionDetails(transactionId) {
            const modal = document.getElementById('transactionModal');
            const modalBody = modal.querySelector('.modal-body');
            const loadingIndicator = document.getElementById('modal-loading');
            const contentDetailsDiv = document.getElementById('modal-content-details');

            // Clear previous content and show loading
            document.getElementById('modal-charge-info').innerHTML = '';
            document.getElementById('modal-status-info').innerHTML = '';
            document.getElementById('modal-items-table').innerHTML = '';
            document.getElementById('modal-service-items-table').innerHTML = '';
            document.getElementById('modal-total-amount').innerHTML = '';
            contentDetailsDiv.style.display = 'none'; // Hide content area
            loadingIndicator.style.display = 'block'; // Show loading

            // Construct the correct URL
            const url = creditDetailsRoute.replace('TRANSACTION_ID', transactionId);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load transaction details.');
                    }

                    // Build Charge To HTML
                    let chargeToHTML = '';
                    if (data.charge_type === 'Department') {
                        chargeToHTML = `
                            <h6 class="mb-2"><strong><i class="fas fa-building me-2"></i>Charge To: Department</strong></h6>
                            <p class="mb-1 ms-4"><strong>Department:</strong> ${data.department || 'N/A'}</p>
                            <p class="mb-1 ms-4"><strong>Full Name:</strong> ${data.full_name || 'N/A'}</p>
                            <p class="mb-1 ms-4"><strong>ID Number:</strong> ${data.id_number || 'N/A'}</p>
                            <p class="mb-1 ms-4"><strong>Contact:</strong> ${data.contact_number || 'N/A'}</p>
                        `;
                    } else if (data.charge_type === 'Employee') { // Adjusted from Employee
                        chargeToHTML = `
                            <h6 class="mb-2"><strong><i class="fas fa-user-tie me-2"></i>Charge To: Employee</strong></h6>
                            <p class="mb-1 ms-4"><strong>Employee Name:</strong> ${data.employee_name || 'N/A'}</p>
                            <p class="mb-1 ms-4"><strong>ID Number:</strong> ${data.id_number || 'N/A'}</p>
                            <p class="mb-1 ms-4"><strong>Contact:</strong> ${data.contact_number || 'N/A'}</p>
                        `;
                    } else {
                         chargeToHTML = `<p><strong>Charge Type:</strong> N/A</p>`;
                    }
                    document.getElementById('modal-charge-info').innerHTML = chargeToHTML;


                    // Build Status HTML
                    let statusClass = data.status === 'Not Paid' ? 'text-danger' : 'text-success';
                    let statusIcon = data.status === 'Not Paid' ? 'fa-times-circle' : 'fa-check-circle';
                    let statusHTML = `
                        <p class="mb-3"><strong>Status:</strong> <span class="${statusClass}"><i class="fas ${statusIcon} me-1"></i>${data.status}</span></p>
                    `;
                    document.getElementById('modal-status-info').innerHTML = statusHTML;


                    // Build Items Table HTML (if items exist)
                    let itemsHTML = '';
                    if (data.items && data.items.length > 0) {
                        itemsHTML = `
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light">
                                    <i class="fas fa-shopping-cart me-1"></i> Item Credit List
                                </div>
                                <div class="card-body p-0"> {{-- Remove padding for full-width table --}}
                                    <table class="table table-bordered table-striped table-hover mb-0"> {{-- Remove margin bottom --}}
                                        <thead class="table-light">
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
                            </div>`;
                    } else {
                       // itemsHTML = `<p><em>No regular items in this transaction.</em></p>`;
                    }
                     document.getElementById('modal-items-table').innerHTML = itemsHTML;


                    // Build Service Items Table HTML (if service items exist)
                     let serviceItemsHTML = '';
                    if (data.serviceItems && data.serviceItems.length > 0) {
                        serviceItemsHTML = `
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header bg-light">
                                    <i class="fas fa-cogs me-1"></i> Service Credit List
                                </div>
                                 <div class="card-body p-0"> {{-- Remove padding --}}
                                    <table class="table table-bordered table-striped table-hover mb-0"> {{-- Remove margin --}}
                                        <thead class="table-light">
                                            <tr>
                                                <th>Service Name</th>
                                                <th>Type</th>
                                                <th>Copies</th>
                                                <th>Hours</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;
                         data.serviceItems.forEach(service => {
                            serviceItemsHTML += `
                                <tr>
                                    <td>${service.service_name || 'N/A'}</td>
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
                            </div>`;
                    } else {
                        //serviceItemsHTML = `<p><em>No service items in this transaction.</em></p>`;
                    }
                    document.getElementById('modal-service-items-table').innerHTML = serviceItemsHTML;

                    // Display Total Amount
                     let totalAmountHTML = `
                        <h5 class="text-danger fw-bold">TOTAL AMOUNT: ${parseFloat(data.totalAmount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })}</h5>
                    `;
                    document.getElementById('modal-total-amount').innerHTML = totalAmountHTML;


                    // Hide loading indicator and show content
                    loadingIndicator.style.display = 'none';
                    contentDetailsDiv.style.display = 'block';

                })
                .catch(error => {
                    console.error('Error loading transaction details:', error);
                    loadingIndicator.style.display = 'none'; // Hide loading
                    // Display error message in the modal body or using an alert function
                    document.getElementById('modal-content-details').innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                    contentDetailsDiv.style.display = 'block'; // Show the error div
                    // Alternatively, use a dedicated alert function if you have one
                    // showAlert('Failed to load transaction details. Please try again.', 'danger');
                });
        }

         // Optional: Simple Alert Function (if not already present)
        function showAlert(message, type) {
            const alertPlaceholder = document.getElementById('alertPlaceholder');
             if (!alertPlaceholder) {
                console.error("Alert placeholder not found!");
                return;
            }
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            alertPlaceholder.innerHTML = ''; // Clear previous alerts
            alertPlaceholder.append(wrapper);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                const alert = bootstrap.Alert.getInstance(wrapper.querySelector('.alert'));
                if (alert) {
                    alert.close();
                } else {
                     // Fallback if instance not found immediately
                     const alertElement = wrapper.querySelector('.alert');
                     if (alertElement) alertElement.remove();
                }
            }, 5000);
        }

        // Clear modal content when it's hidden to prevent showing old data briefly on next open
        const transactionModalElement = document.getElementById('transactionModal');
        transactionModalElement.addEventListener('hidden.bs.modal', event => {
            document.getElementById('modal-charge-info').innerHTML = '';
            document.getElementById('modal-status-info').innerHTML = '';
            document.getElementById('modal-items-table').innerHTML = '';
            document.getElementById('modal-service-items-table').innerHTML = '';
            document.getElementById('modal-total-amount').innerHTML = '';
            document.getElementById('modal-loading').style.display = 'none';
            document.getElementById('modal-content-details').style.display = 'none'; // Ensure content is hidden initially
        });

    </script>

    {{-- Optional: Add some basic styling --}}
    <style>
        #transactionModal .modal-body p {
            margin-bottom: 0.5rem; /* Adjust spacing */
        }
        #transactionModal .table {
             font-size: 0.9rem; /* Slightly smaller font in tables */
        }
        #transactionModal .card-header {
            font-weight: 500;
        }
         /* Ensure table fits well within card body */
        #transactionModal .card-body {
            overflow-x: auto; /* Add scroll if table is too wide */
        }
    </style>

@endsection