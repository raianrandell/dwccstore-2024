@extends('layout.cashier')

@section('title', 'Return Transaction')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Returns</li>
</ol>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div></div>
    <div>
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#returnItemModal">
            <i class="fas fa-plus me-1"></i> Return Item
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Returned Item List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Transaction Number</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity Returned</th>
                    <th>Reason</th>
                    <th>Type</th>
                    <th>Replacement Item</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returnedItems as $item)
                    <tr>
                        <td>{{ $item->transaction_no }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->item->category->category_name ?? 'N/A' }}</td>
                        <td>{{ $item->return_quantity }}</td>
                        <td>{{ $item->reason }}</td>
                        <td>Replacement</td>
                        <td>{{ $item->replacement_item }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Returning Items -->
<div class="modal fade" id="returnItemModal" tabindex="-1" aria-labelledby="returnItemModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Return Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnItemForm" method="POST" action="{{ route('return.processReturn') }}">
                @csrf
                <div class="modal-body">
                    <div id="modalAlert" class="alert d-none" role="alert"></div>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="transactionNo" class="form-label">Transaction Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="transactionNo" name="transaction_no" placeholder="Enter transaction number" required aria-required="true">
                            <div class="invalid-feedback">
                                Please enter a valid transaction number.
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" id="fetchItemsButton" class="btn btn-primary w-100" aria-label="Fetch Items">
                                <i class="fas fa-search me-2"></i> Fetch Items
                            </button>
                        </div>
                    </div>
                    <div id="fetchedItemsSection" class="mt-4 d-none">
                        <h5>Items to Return</h5>
                        <div class="d-flex justify-content-end mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="selectAllItems">
                                <label class="form-check-label" for="selectAllItems">
                                    Select All
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Select</th>
                                        <th>Item Name</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Return Quantity</th>
                                        <th>Reason</th>
                                        <th>Type</th>
                                        <th>Replacement Item</th>
                                    </tr>
                                </thead>
                                <tbody id="fetchedItemsTable">
                                    <!-- Dynamically loaded items -->
                                </tbody>
                            </table>
                        </div>
                        <div id="noItemsMessage" class="alert alert-info d-none" role="alert">
                            No items available for return in this transaction.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle me-2"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="submitReturnButton" disabled>
                        <i class="fas fa-check-circle me-2"></i> Submit Return
                    </button>    
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fetchItemsButton = document.getElementById('fetchItemsButton');
        const transactionNoInput = document.getElementById('transactionNo');
        const fetchedItemsSection = document.getElementById('fetchedItemsSection');
        const fetchedItemsTable = document.getElementById('fetchedItemsTable');
        const submitReturnButton = document.getElementById('submitReturnButton');
        const selectAllItemsCheckbox = document.getElementById('selectAllItems');
        const modalAlert = document.getElementById('modalAlert');
        const noItemsMessage = document.getElementById('noItemsMessage');

        // Function to display alerts within the modal
        function showModalAlert(message, type = 'danger') {
            modalAlert.className = `alert alert-${type}`;
            modalAlert.textContent = message;
            modalAlert.classList.remove('d-none');
        }

        // Function to hide modal alerts
        function hideModalAlert() {
            modalAlert.classList.add('d-none');
            modalAlert.textContent = '';
        }

        // Function to toggle the Submit button based on selections
        function toggleSubmitButton() {
            const anyChecked = document.querySelectorAll('input[name="selected_items[]"]:checked').length > 0;
            submitReturnButton.disabled = !anyChecked;
        }

        // Event listener for fetching items
        fetchItemsButton.addEventListener('click', function () {
            const transactionNo = transactionNoInput.value.trim();

            // Reset previous state
            fetchedItemsTable.innerHTML = '';
            fetchedItemsSection.classList.add('d-none');
            noItemsMessage.classList.add('d-none');
            hideModalAlert();
            submitReturnButton.disabled = true;
            selectAllItemsCheckbox.checked = false;

            transactionNoInput.classList.remove('is-invalid');

            if (!transactionNo) {
                transactionNoInput.classList.add('is-invalid');
                return;
            }

            // Disable the fetch button and show a loading spinner
            fetchItemsButton.disabled = true;
            fetchItemsButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...';

            fetch('{{ route('return.fetch_items') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ transaction_no: transactionNo }),
            })
                .then(response => response.json())
                .then(data => {
                    // Re-enable the fetch button and reset its text
                    fetchItemsButton.disabled = false;
                    fetchItemsButton.innerHTML = '<i class="fas fa-search me-2"></i> Fetch Items';

                    if (data.error) {
                        showModalAlert(data.error, 'danger');
                        return;
                    }

                    if (data.items.length === 0) {
                        noItemsMessage.classList.remove('d-none');
                        return;
                    }

                    data.items.forEach((item, index) => {
                        const row = document.createElement('tr');

                        // Select Checkbox
                        const selectCell = document.createElement('td');
                        selectCell.classList.add('text-center');
                        selectCell.innerHTML = `
                            <input type="checkbox" name="selected_items[]" value="${index}" class="form-check-input">
                        `;
                        row.appendChild(selectCell);

                        // Item Name
                        const nameCell = document.createElement('td');
                        nameCell.textContent = item.item_name;
                        row.appendChild(nameCell);

                        // Quantity
                        const quantityCell = document.createElement('td');
                        quantityCell.classList.add('text-end');
                        quantityCell.textContent = item.quantity;
                        row.appendChild(quantityCell);

                        // Return Quantity
                        const returnQtyCell = document.createElement('td');
                        returnQtyCell.classList.add('text-end');
                        returnQtyCell.innerHTML = `
                            <input type="number" name="return_quantity[${index}]" class="form-control" min="1" max="${item.quantity}" disabled required aria-required="true">
                        `;
                        row.appendChild(returnQtyCell);

                        // Reason
                        const reasonCell = document.createElement('td');
                        reasonCell.innerHTML = `
                            <input type="text" name="reason[${index}]" class="form-control" placeholder="Enter reason" disabled required aria-required="true">
                        `;
                        row.appendChild(reasonCell);

                        // Type
                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.type;
                        row.appendChild(typeCell);

                        // Replacement Item
                        const replacementCell = document.createElement('td');
                        replacementCell.innerHTML = `
                            <input type="text" name="replacement_item[${index}]" class="form-control" value="${item.replacement_item || ''}" disabled required aria-required="true">
                        `;
                        row.appendChild(replacementCell);

                        fetchedItemsTable.appendChild(row);
                    });

                    fetchedItemsSection.classList.remove('d-none');

                    // Add event listeners to checkboxes to enable/disable corresponding inputs
                    document.querySelectorAll('input[name="selected_items[]"]').forEach(checkbox => {
                        checkbox.addEventListener('change', function () {
                            const row = this.closest('tr');
                            const inputs = row.querySelectorAll('input:not([type="checkbox"])');
                            inputs.forEach(input => {
                                input.disabled = !this.checked;
                                if (input.name.includes('replacement_item')) {
                                    // Optionally, set replacement item to the item name if enabled
                                    input.value = this.checked ? row.querySelector('td:nth-child(2)').textContent.trim() : '';
                                }
                            });
                            toggleSubmitButton();
                        });
                    });

                    // Show the fetched items section
                    fetchedItemsSection.classList.remove('d-none');
                })
                .catch(error => {
                    console.error('Error fetching items:', error);
                    fetchItemsButton.disabled = false;
                    fetchItemsButton.innerHTML = '<i class="fas fa-search me-2"></i> Fetch Items';
                    showModalAlert('An error occurred while fetching items. Please try again.', 'danger');
                });
        });

        // Event listener for Select All checkbox
        selectAllItemsCheckbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const row = checkbox.closest('tr');
                const inputs = row.querySelectorAll('input:not([type="checkbox"])');
                inputs.forEach(input => {
                    input.disabled = !checkbox.checked;
                    if (input.name.includes('replacement_item')) {
                        input.value = checkbox.checked ? row.querySelector('td:nth-child(2)').textContent.trim() : '';
                    }
                });
            });
            toggleSubmitButton();
        });

        // Enable Submit button if at least one item is selected on modal load
        const returnItemModal = document.getElementById('returnItemModal');
        returnItemModal.addEventListener('shown.bs.modal', function () {
            transactionNoInput.focus();
        });

        // Reset the modal when it's closed
        returnItemModal.addEventListener('hidden.bs.modal', function () {
            const fetchedItemsSection = document.getElementById('fetchedItemsSection');
            fetchedItemsSection.classList.add('d-none');
            fetchedItemsTable.innerHTML = '';
            noItemsMessage.classList.add('d-none');
            hideModalAlert();
            submitReturnButton.disabled = true;
            selectAllItemsCheckbox.checked = false;
            transactionNoInput.classList.remove('is-invalid');
            transactionNoInput.value = '';
            fetchItemsButton.innerHTML = '<i class="fas fa-search me-2"></i> Fetch Items';
            fetchItemsButton.disabled = false;
        });

        // Optional: Add real-time validation for the form
        const returnItemForm = document.getElementById('returnItemForm');
        returnItemForm.addEventListener('submit', function (event) {
            if (!returnItemForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                returnItemForm.classList.add('was-validated');
                showModalAlert('Please fix the errors in the form before submitting.', 'warning');
            } else {
                // Optionally, add a confirmation step or disable the submit button to prevent multiple submissions
                submitReturnButton.disabled = true;
                submitReturnButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
            }
        });
    });
</script>
@endsection
