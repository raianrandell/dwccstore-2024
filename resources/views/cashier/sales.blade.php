@extends('layout.cashier')

@section('title', 'Sales')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>
    <!-- Success and Error Messages -->
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fa-lg"></i>
            <div>
                {{ Session::get('success') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (Session::has('danger'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
            <div>
                {{ Session::get('danger') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (Session::has('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
            <div>
                {{ Session::get('warning') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <!-- Wrapper for scaling -->
    <div class="scale-wrapper" style="transform: scale(0.9); transform-origin: top left; width: 111.11%;">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-cash-register"></i>
                Sales Transaction
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- First Column -->
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <!-- Left Side: Transaction No -->
                            <h4>Transaction No: #TRX{{ time() }}</h4>

                            <!-- Right Side: Date/Time and Services Button -->
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-3">
                                    Date/Time: <span id="currentDateTime">{{ now()->format('m-d-Y h:i:s a') }}</span>
                                </h6>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group">
                                <select class="form-select" id="fetchItem" name="fetchItem" required>
                                    <option value="" selected>Select the Item</option>
                                    @foreach ($items as $item)
                                        @php
                                            // Determine if the item is expired
                                            $isExpired =
                                                $item->expiration_date &&
                                                \Carbon\Carbon::parse($item->expiration_date)->isPast();
                                        @endphp

                                        @if ($isExpired)
                                            @continue <!-- Skip this iteration if the item is expired -->
                                        @endif

                                        <option value="{{ $item->id }}" {{ $item->qtyInStock === 0 ? 'disabled' : '' }}>
                                            @if ($item->qtyInStock === 0)
                                                {{ $item->item_name }} - {{ $item->unit_of_measurement }} (Out of Stock)
                                            @else
                                                {{ $item->item_name }} - {{ $item->unit_of_measurement }} (Stock:
                                                {{ $item->qtyInStock }})
                                                Price: ₱{{ number_format($item->selling_price, 2) }}
                                                @if ($item->expiration_date)
                                                    - Exp:
                                                    {{ \Carbon\Carbon::parse($item->expiration_date)->format('m/d/Y') }}
                                                @endif
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                &nbsp;&nbsp;<button type="button" class="btn btn-outline-success" id="addItemButton"
                                    title="Add Item">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Item List Table -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Order Summary
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="no-items-message">
                                            <td colspan="6" class="text-center">No items found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Second Column -->
                    <div class="col-md-3">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="barcodeInput" class="form-label">Scan Barcode</label>
                                    <input type="text" class="form-control" id="barcodeInput"
                                        placeholder="Enter or scan barcode" maxlength="13" autofocus>
                                </div>

                                <div class="mb-3" style="display:none;">
                                    <label for="quantityInput" class="form-label">Quantity</label>
                                    <input type="number" class="form-control" id="quantityInput" value="1"
                                        min="1">
                                </div>

                                <div class="mb-3">
                                    <label for="discountInput" class="form-label">Discount (%)</label>
                                    <input type="number" class="form-control" id="discountInput" value="0"
                                        min="0" step="1">
                                </div>

                                <!-- Summary Card -->
                                <div class="mb-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h4>Summary</h4>
                                            <div class="d-flex justify-content-between">
                                                <span>Subtotal:</span>
                                                <strong><span id="subtotal">0.00</span></strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Discount:</span>
                                                <strong><span id="discount">0.00</span></strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Items:</span>
                                                <strong><span id="totalItems">0</span></strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total:</span>
                                                <strong><span id="total">0.00</span></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            id="cashOption" value="Cash" checked>
                                        <label class="form-check-label" for="cashOption">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            id="gcashOption" value="GCash">
                                        <label class="form-check-label" for="gcashOption">Gcash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod"
                                            id="creditOption" value="Credit">
                                        <label class="form-check-label" for="creditOption">Credit</label>
                                    </div>
                                </div>

                                <!-- Payment Fields -->
                                <div id="cashFields" class="mb-3">
                                    <label for="cashTendered" class="form-label">Cash Tendered</label>
                                    <input type="number" class="form-control" id="cashTendered"
                                        placeholder="Enter cash amount">
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Change:</span>
                                        <strong><span id="change">0.00</span></strong>
                                    </div>
                                </div>

                                <div id="gcashFields" class="mb-3" style="display:none;">
                                    <label for="gcashReference" class="form-label">Gcash Reference No</label>
                                    <input type="tel" class="form-control" id="gcashReference"
                                        placeholder="Enter Gcash Reference No" maxlength="13">
                                </div>

                                <!-- Credit Transaction Fields -->
                                <div id="creditFields" style="display: none;">
                                    <label class="form-label">Charge Type</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="chargeType"
                                            id="departmentCharge" value="Department">
                                        <label class="form-check-label" for="departmentCharge">Charge to
                                            Department</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="chargeType"
                                            id="facultyCharge" value="Employee">
                                        <label class="form-check-label" for="facultyCharge">Charge to Employee</label>
                                    </div>

                                    <div id="departmentFields" style="display: none;">
                                        <div class="mb-3">
                                            <label for="fullName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="fullName"
                                                placeholder="Enter Full Name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="idNumber" class="form-label">ID Number</label>
                                            <input type="text" class="form-control" id="idNumber"
                                                placeholder="Enter ID Number">
                                        </div>
                                        <div class="mb-3">
                                            <label for="contactNumber" class="form-label">Contact Number</label>
                                            <input type="tel" class="form-control" id="contactNumber"
                                                placeholder="Enter Contact Number" maxlength="11" pattern="\d{11}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Department</label>
                                            <select class="form-control" id="department">
                                                <option value="" disabled selected>Select Department</option>
                                                <option value="Accountancy">Accountancy</option>
                                                <option value="Architecture and Fine Arts">Architecture and Fine Arts
                                                </option>
                                                <option value="SBHTM">Business, Hospitality and Tourism Management
                                                </option>
                                                <option value="Education">Education</option>
                                                <option value="Engineering">Engineering</option>
                                                <option value="Information and Technology">Information and Technology
                                                </option>
                                                <option value="Liberal Arts and Criminal Justice">Liberal Arts and Criminal
                                                    Justice</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div id="facultyFields" style="display: none;">
                                        <div class="mb-3">
                                            <label for="facultyIdNumber" class="form-label">ID Number</label>
                                            <input type="text" class="form-control" id="facultyIdNumber"
                                                placeholder="Enter ID Number">
                                        </div>
                                        <div class="mb-3">
                                            <label for="facultyContactNumber" class="form-label">Contact Number</label>
                                            <input type="tel" class="form-control" id="facultyContactNumber"
                                                placeholder="Enter Contact Number" maxlength="11" pattern="\d{11}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="facultyName" class="form-label">Employee Name</label>
                                            <input type="text" class="form-control" id="facultyName"
                                                placeholder="Enter Employee Name">
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-success w-100 mt-3" type="button" id="saveTransaction"
                                    disabled>Pay</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal for Void Confirmation -->
    <div class="modal fade" id="voidConfirmationModal" tabindex="-1" role="dialog"
        aria-labelledby="voidConfirmationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="voidConfirmationLabel">Confirm Void</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to void this item?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmVoid">VOID</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            // Initialize Select2 for the item dropdown
            $('#fetchItem').select2({
                placeholder: "Select the Item",
                allowClear: true,
                width: '95%',
                theme: 'bootstrap-5'
            });

            let transactionItems = []; // Array to store selected items for the transaction
            let itemIndex = 1; // Index for each row added
            let itemToVoidId; // Variable to store the ID of the item to be voided

            // Load items from local storage on page load
            loadItemsFromLocalStorage();

            // Initialize the visibility and SAVE button state on page load
            computeChange();
            toggleSaveButton();

            $('#addItemButton').click(function() {
                const itemId = $('#fetchItem').val();

                // Check if an item is selected
                if (!itemId) {
                    showAlert('Please select an item first.', 'danger');
                    return; // Exit if no item is selected
                }

                const selectedOption = $('#fetchItem option:selected').text();
                const itemName = selectedOption.split(' - ')[0]; // Extract item name
                const unitOfMeasurement = selectedOption.split(' - ')[1].split(' (')[
                    0]; // Extract unit of measurement

                // Find item details from PHP data
                const item = @json($items).find(item => item.id == itemId);

                if (item) {
                    // Get the quantity from the input field
                    const quantityToAdd = parseInt($('#quantityInput').val());

                    // Validate quantity
                    if (isNaN(quantityToAdd) || quantityToAdd < 1) {
                        showAlert('Please enter a valid quantity.', 'danger');
                        return;
                    }

                    // Check if the quantity to add is available in stock
                    if (quantityToAdd > item.qtyInStock) {
                        showAlert(`Insufficient stock available. Maximum allowed: ${item.qtyInStock}.`,
                            'danger');
                        return; // Exit if quantity exceeds available stock
                    }

                    // Check if the item already exists in the transactionItems array
                    const existingItem = transactionItems.find(i => i.id == item.id);

                    if (existingItem) {
                        // Update quantity and total for the existing item
                        existingItem.quantity += quantityToAdd;
                        existingItem.total = existingItem.price * existingItem.quantity;

                        // Update the row in the table
                        const row = $('#datatablesSimple tbody tr[data-id="' + item.id + '"]');
                        row.find('.quantity').val(existingItem.quantity);
                        row.find('.total').text(existingItem.total.toFixed(2));

                        showAlert('Quantity updated successfully.', 'success');
                    } else {
                        // Create a new row for the item
                        const row = `
            <tr data-id="${item.id}">
                <td>${itemIndex}</td>
                <td>${itemName} - ${unitOfMeasurement}</td>
                <td><input type="number" class="form-control quantity" value="${quantityToAdd}" min="1" max="${item.qtyInStock}"></td>
                <td>${parseFloat(item.selling_price).toFixed(2)}</td>
                <td class="total">${(item.selling_price * quantityToAdd).toFixed(2)}</td>
                <td><button type="button" class="btn btn-outline-danger btn-sm remove-item" title="Void Item">VOID</button></td>
            </tr>
            `;

                        // Append the row to the table body
                        $('#datatablesSimple tbody').append(row);
                        itemIndex++;

                        // Add item to transactionItems array with unit_of_measurement
                        transactionItems.push({
                            id: item.id,
                            name: itemName,
                            unit_of_measurement: unitOfMeasurement,
                            price: parseFloat(item.selling_price),
                            quantity: quantityToAdd,
                            total: parseFloat(item.selling_price) * quantityToAdd
                        });

                        showAlert('Item added successfully.', 'success');
                    }

                    // Save items to local storage
                    saveItemsToLocalStorage();

                    // Check if the "No items found" message is visible and hide it
                    $('#no-items-message').hide();

                    // Update the summary
                    updateSummary();
                    toggleSaveButton();
                }
            });

            // Listen for barcode input changes
            $('#barcodeInput').on('input', function() {
                const barcode = $(this).val().trim();
                if (barcode.length === 13) { // Assuming barcode is 13 characters long
                    fetchItemByBarcode(barcode);
                }
            });

            function fetchItemByBarcode(barcode) {
                $.ajax({
                    url: '{{ route('cashier.fetch_item_by_barcode') }}', // Adjust this route as necessary
                    method: 'GET',
                    data: {
                        barcode: barcode
                    },
                    success: function(response) {
                        if (response.success) {
                            const item = response.item;
                            const itemName = item.item_name;
                            const unitOfMeasurement = item.unit_of_measurement;

                            // Validate stock
                            if (item.qtyInStock < 1) {
                                showAlert('Item is out of stock.', 'danger');
                                $('#barcodeInput').val('');
                                return;
                            }

                            // Check if the item already exists in the transactionItems array
                            const existingItem = transactionItems.find(i => i.id == item.id);

                            if (existingItem) {
                                // Update quantity and total for the existing item
                                existingItem.quantity += 1;
                                existingItem.total = existingItem.price * existingItem.quantity;

                                // Update the row in the table
                                const row = $('#datatablesSimple tbody tr[data-id="' + item.id + '"]');
                                row.find('.quantity').val(existingItem.quantity);
                                row.find('.total').text(existingItem.total.toFixed(2));

                                showAlert('Quantity updated successfully.', 'success');
                            } else {
                                // Create a new row for the item
                                const row = `
                        <tr data-id="${item.id}">
                            <td>${itemIndex}</td>
                            <td>${itemName} - ${unitOfMeasurement}</td>
                            <td><input type="number" class="form-control quantity" value="1" min="1" max="${item.qtyInStock}"></td>
                            <td>${parseFloat(item.selling_price).toFixed(2)}</td>
                            <td class="total">${parseFloat(item.selling_price).toFixed(2)}</td>
                            <td><button type="button" class="btn btn-outline-danger btn-sm remove-item" title="Void Item">VOID</button></td>
                        </tr>
                    `;

                                // Append the row to the table body
                                $('#datatablesSimple tbody').append(row);
                                itemIndex++;

                                // Add item to transactionItems array with unit_of_measurement
                                transactionItems.push({
                                    id: item.id,
                                    name: itemName,
                                    unit_of_measurement: unitOfMeasurement,
                                    price: parseFloat(item.selling_price),
                                    quantity: 1,
                                    total: parseFloat(item.selling_price)
                                });

                                showAlert('Item added successfully.', 'success');
                            }

                            // Save items to local storage
                            saveItemsToLocalStorage();

                            // Check if the "No items found" message is visible and hide it
                            $('#no-items-message').hide();

                            // Reset the barcode input field
                            $('#barcodeInput').val(''); // Clear the input field after adding the item

                            // Update the summary
                            updateSummary();
                            toggleSaveButton();
                        } else {
                            showAlert('Item not found.', 'danger');
                            $('#barcodeInput').val('');
                        }
                    },
                    error: function() {
                        showAlert('Error fetching item. Please try again.', 'danger');
                        $('#barcodeInput').val('');
                    }
                });
            }


            // Listen for click event on the VOID button
            $('#datatablesSimple').on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                itemToVoidId = row.data('id'); // Store the ID of the item to be voided
                $('#voidConfirmationModal').modal('show'); // Show the confirmation modal
            });

            // Confirm void action
            $('#confirmVoid').click(function() {
                const row = $('#datatablesSimple').find(`tr[data-id="${itemToVoidId}"]`);
                const quantity = parseInt(row.find('.quantity').val());
                const itemName = row.find('td:nth-child(2)').text().split(' - ')[0]; // Get the item name
                const price = parseFloat(row.find('td:nth-child(4)').text()); // Get the price

                // Remove item from transactionItems array
                transactionItems = transactionItems.filter(item => item.id != itemToVoidId);

                // Remove the row from the table
                row.remove();

                // Check if there are any items left
                if ($('#datatablesSimple tbody tr').length === 0) {
                    $('#no-items-message').show(); // Show the message if no items left
                }

                // Save the updated items to local storage
                saveItemsToLocalStorage();

                // Update the summary after removing the item
                updateSummary();
                toggleSaveButton(); // Toggle the SAVE button state

                // Get the full name of the logged-in user
                const voidedByFullName =
                    '{{ Auth::guard('cashier')->user()->full_name }}'; // Get the full name of the logged-in user

                // Make AJAX call to save voided item
                $.ajax({
                    url: '{{ route('cashier.save_void_records') }}', // Create this route
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        transaction_no: 'TRX' + new Date()
                            .getTime(), // Use your transaction number logic here
                        item_name: itemName,
                        price: price,
                        user_id: '{{ Auth::guard('cashier')->user()->id }}', // Get the current logged-in user ID
                        voided_by: voidedByFullName // Include the full name of the user
                    },
                    success: function(response) {
                        $('#voidConfirmationModal').modal('hide'); // Hide the modal
                        showAlert('Item voided successfully and recorded.',
                            'success'); // Alert for item voiding
                    },
                    error: function(xhr) {
                        showAlert('Error recording voided item.', 'danger'); // Handle error
                    }
                });
            });


            // Update quantity and recalculate total for each item
            $('#datatablesSimple').on('input', '.quantity', function() {
                const row = $(this).closest('tr');
                const itemId = row.data('id');
                const newQuantity = parseInt($(this).val());

                // Find the item in the transactionItems array
                const item = transactionItems.find(item => item.id == itemId);
                if (item) {
                    const originalQuantity = item.quantity; // Store original quantity
                    const itemDetails = @json($items).find(i => i.id == itemId);

                    // Calculate the new stock available
                    const newStockAvailable = itemDetails.qtyInStock - newQuantity;

                    if (newQuantity > itemDetails.qtyInStock) {
                        showAlert(
                            `Insufficient stock available. Maximum allowed: ${itemDetails.qtyInStock}`,
                            'danger');
                        $(this).val(item.quantity); // Revert to previous quantity
                        return;
                    }

                    // Update the item's quantity and total
                    item.quantity = newQuantity;
                    item.total = item.price * newQuantity;

                    // Update the row's total in the table
                    row.find('.total').text(item.total.toFixed(2));

                    // Save updated items to local storage
                    saveItemsToLocalStorage();

                    // Update summary and button state
                    updateSummary();
                    toggleSaveButton();
                }
            });

            // Function to compute and display change
            function computeChange() {
                const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                const total = parseFloat($('#total').text()) || 0;
                let change = 0;

                if (paymentMethod === 'Cash') {
                    const cashTendered = parseFloat($('#cashTendered').val()) || 0;
                    change = cashTendered - total;

                    // Ensure that change is not negative
                    if (change < 0) {
                        change = 0.00;
                    }

                    $('#change').text(change.toFixed(2));
                } else {
                    // Reset change if payment method is not cash
                    $('#change').text('0.00');
                }
            }

            // Function to update summary (Subtotal, Discount, Total)
            function updateSummary() {
                let subtotal = transactionItems.reduce((acc, item) => acc + item.total, 0);
                let discount = parseFloat($('#discountInput').val()) || 0;
                let discountAmount = (subtotal * discount) / 100;
                let total = subtotal - discountAmount;
                // Calculate total items
                let totalItems = transactionItems.reduce((acc, item) => acc + item.quantity, 0);

                $('#subtotal').text(subtotal.toFixed(2));
                $('#discount').text(discountAmount.toFixed(2));
                $('#total').text(total.toFixed(2));
                $('#totalItems').text(totalItems);

                // Compute change after updating summary
                computeChange();
            }

            // Listen for changes in payment method to show/hide relevant fields
            $('input[name="paymentMethod"]').change(function() {
                const selectedMethod = $(this).val();
                if (selectedMethod === 'Cash') {
                    $('#cashFields').show();
                    $('#gcashFields').hide();
                } else if (selectedMethod === 'GCash') {
                    $('#cashFields').hide();
                    $('#gcashFields').show();
                }
                computeChange();
                toggleSaveButton();
            });

            // Function to toggle the SAVE button based on input validations
            function toggleSaveButton() {
                const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                const chargeType = $('#chargeType').val();
                let enable = false;

                if (paymentMethod === 'Cash') {
                    const cashTendered = parseFloat($('#cashTendered').val()) || 0;
                    const total = parseFloat($('#total').text()) || 0;
                    enable = cashTendered >= total && transactionItems.length > 0;
                } else if (paymentMethod === 'GCash') {
                    const gcashReference = $('#gcashReference').val();
                    enable = gcashReference.trim() !== '' && transactionItems.length > 0;
                } else if (paymentMethod === 'Credit') {
                    if (chargeType === '') {
                        enable = false; // Disable if no charge type is selected
                    } else {
                        enable = transactionItems.length > 0 &&
                            validateChargeTypeInputs(); // Validate charge type inputs
                    }
                }

                $('#saveTransaction').prop('disabled', !enable);
            }


            // Listen for discount input change
            $('#discountInput').on('input', function() {
                updateSummary();
                toggleSaveButton();
            });

            // Listen for changes in the cashTendered input to compute change in real-time
            $('#cashTendered').on('input', function() {
                computeChange();
                toggleSaveButton();
            });

            // Listen for input in gcashReference to toggle SAVE button
            $('#gcashReference').on('input', function() {
                toggleSaveButton();
            });

            function validateChargeTypeInputs() {
                const chargeType = $('#chargeType').val();
                let isValid = true;

                if (chargeType === 'Department') {
                    isValid = $('#fullName').val().trim() !== '' &&
                        $('#idNumber').val().trim() !== '' &&
                        $('#contactNumber').val().trim() !== '' &&
                        $('#department').val().trim() !== '';
                } else if (chargeType === 'Employee') {
                    isValid = $('#idNumber').val().trim() !== '' &&
                        $('#contactNumber').val().trim() !== '' &&
                        $('#facultyName').val().trim() !== '';
                }

                return isValid;
            }

            // Listen for changes in charge type selection
            $('input[name="chargeType"]').change(function() {
                const chargeType = $(this).val();
                $('#departmentFields input').val(''); // Clear department fields
                $('#facultyFields input').val(''); // Clear faculty fields
                if (chargeType === 'Department') {
                    $('#departmentFields').show();
                    $('#facultyFields').hide();
                } else if (chargeType === 'Employee') {
                    $('#facultyFields').show();
                    $('#departmentFields').hide();
                } else {
                    $('#departmentFields').hide();
                    $('#facultyFields').hide();
                }
                toggleSaveButton(); // Check if the Pay button should be enabled/disabled
            });

            // Listen for changes in charge type selection
            $('#chargeType').change(function() {
                toggleSaveButton(); // Check if the Pay button should be enabled/disabled
            });

            // Listen for input changes in charge type fields
            $('#departmentFields input, #facultyFields input').on('input', function() {
                toggleSaveButton(); // Check if the Pay button should be enabled/disabled
            });

            $(document).ready(function() {
                // Show/hide credit fields based on payment method selection
                $('input[name="paymentMethod"]').change(function() {
                    if ($(this).val() === 'Credit') {
                        $('#creditFields').show();
                        $('#cashFields').hide();
                        $('#gcashFields').hide()
                    } else {
                        $('#creditFields').hide();
                        $('#departmentFields').hide();
                        $('#facultyFields').hide();
                    }
                });

                // Show/hide fields based on charge type selection
                $('#chargeType').change(function() {
                    const chargeType = $(this).val();
                    if (chargeType === 'Department') {
                        $('#departmentFields').show();
                        $('#facultyFields').hide();
                    } else if (chargeType === 'Employee') {
                        $('#facultyFields').show();
                        $('#departmentFields').hide();
                    } else {
                        $('#departmentFields').hide();
                        $('#facultyFields').hide();
                    }
                });
            });

            $('#saveTransaction').click(function() {
                const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                // Ensure you have the proper event listener and variable initialization
                document.querySelectorAll('input[name="chargeType"]').forEach(input => {
                    input.addEventListener('change', function() {
                        // Declare and initialize the chargeType variable when the radio button is clicked
                        let chargeType = document.querySelector(
                            'input[name="chargeType"]:checked').value;

                        // Now you can safely use chargeType
                        if (chargeType === 'Employee') {
                            document.getElementById('facultyFields').style.display =
                                'block';
                            document.getElementById('departmentFields').style.display =
                                'none';
                        } else if (chargeType === 'Department') {
                            document.getElementById('departmentFields').style.display =
                                'block';
                            document.getElementById('facultyFields').style.display = 'none';
                        }
                    });
                });

                let isValid = true;
                let errorMessage = '';

                // Initialize the chargeType variable
                let chargeType = $('input[name="chargeType"]:checked').val();

                // Validate based on payment method
                if (paymentMethod === 'Cash') {
                    const cashTendered = parseFloat($('#cashTendered').val()) || 0;
                    const total = parseFloat($('#total').text()) || 0;

                    if (cashTendered < total) {
                        isValid = false;
                        errorMessage = 'Cash tendered is less than the total amount.';
                    }
                } else if (paymentMethod === 'GCash') {
                    const gcashReference = $('#gcashReference').val();
                    if (!gcashReference.trim()) {
                        isValid = false;
                        errorMessage = 'Please enter the Gcash Reference No.';
                    }
                } else if (paymentMethod === 'Credit') {
                    // Ensure chargeType is set
                    if (!chargeType) {
                        isValid = false;
                        errorMessage = 'Please select a charge type.';
                    } else {
                        // Validate fields based on charge type
                        if (chargeType === 'Department') {
                            const fullName = $('#fullName').val();
                            const idNumber = $('#idNumber').val();
                            const contactNumber = $('#contactNumber').val();
                            const department = $('#department').val();
                            if (!fullName || !idNumber || !contactNumber || !department) {
                                isValid = false;
                                errorMessage = 'Please fill out all fields for department charge.';
                            }
                        } else if (chargeType === 'Employee') {
                            const facultyName = $('#facultyName').val();
                            const facultyIdNumber = $('#facultyIdNumber').val();
                            const facultyContactNumber = $('#facultyContactNumber').val();
                            if (!facultyName || !facultyIdNumber || !facultyContactNumber) {
                                isValid = false;
                                errorMessage = 'Please fill out all fields for faculty charge.';
                            }
                        }
                    }
                }

                // Check if there are items in the transaction
                if (transactionItems.length === 0) {
                    isValid = false;
                    errorMessage = 'Please add at least one item to the transaction.';
                }

                if (!isValid) {
                    showAlert(errorMessage, 'danger');
                    return; // Exit the function if validation fails
                }
                // Proceed with AJAX request
                $.ajax({
                    url: '{{ route('cashier.save_transaction') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        items: transactionItems,
                        paymentMethod: paymentMethod,
                        cashTendered: $('#cashTendered').val(),
                        gcashReference: $('#gcashReference').val(),
                        subtotal: $('#subtotal').text(),
                        discount: $('#discount').text(),
                        total: $('#total').text(),
                        chargeType: chargeType, // Send charge type
                        fullName: $('#fullName').val(), // Add Full Name for department
                        idNumber: $('#idNumber').val(), // Add ID Number for department
                        contactNumber: $('#contactNumber')
                            .val(), // Add Contact Number for department
                        department: $('#department').val(), // Add Department for department
                        facultyName: $('#facultyName').val(), // Add Faculty Name for faculty
                        facultyIdNumber: $('#facultyIdNumber').val(), // Add Faculty ID for faculty
                        facultyContactNumber: $('#facultyContactNumber')
                            .val(), // Add Faculty Contact for faculty
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('Transaction saved successfully.', 'success');

                            // Ensure transactionNo and cashierName are properly passed
                            const transactionNo = response.transaction_no ||
                                'N/A'; // Default to 'N/A' if undefined
                            const cashierName =
                                '{{ Auth::guard('cashier')->user()->full_name }}'; // Assuming this is the correct way to get the cashier's name

                            // Trigger invoice generation and printing
                            generateSalesInvoice({
                                transactionNo: transactionNo,
                                cashierName: cashierName,
                                dateTime: new Date().toLocaleString(),
                                items: transactionItems,
                                discount: $('#discount').text(),
                                total: $('#total').text(),
                                paymentMethod: paymentMethod,
                                cashTendered: $('#cashTendered').val(),
                                change: $('#change').text(),
                                gcashReference: $('#gcashReference').val(),
                                chargeType: chargeType,
                                fullName: $('#fullName')
                            .val(), // Use .val() to get the value
                                idNumber: $('#idNumber')
                            .val(), // Use .val() to get the value
                                contactNumber: $('#contactNumber')
                            .val(), // Use .val() to get the value
                                department: $('#department')
                            .val(), // Use .val() to get the value
                                facultyName: $('#facultyName')
                            .val(), // Use .val() to get the value
                                facultyIdNumber: $('#facultyIdNumber')
                            .val(), // Use .val() to get the value
                                facultyContactNumber: $('#facultyContactNumber')
                                .val() // Use .val() to get the value
                            });

                            // Reset the transaction to prepare for the next one
                            resetTransaction();
                        } else {
                            showAlert('Error saving transaction: ' + response.message,
                                'danger');
                        }
                    },
                    error: function(xhr) {
                        showAlert('An unexpected error occurred.', 'danger');
                    }
                });
            });

            function generateSalesInvoice(data) {
                const invoiceNumber = generateSalesInvoiceNumber();
                const {
                    transactionNo,
                    cashierName,
                    dateTime,
                    items,
                    discount,
                    total,
                    paymentMethod,
                    cashTendered,
                    change,
                    gcashReference,
                    chargeType,
                    fullName, // added Full Name for Department
                    idNumber, // added ID Number
                    contactNumber, // added Contact Number
                    department, // added Department for Department charge
                    facultyName, // added Faculty Name for Faculty charge
                    facultyIdNumber, // added Faculty ID for Faculty charge
                    facultyContactNumber // added Faculty Contact for Faculty charge
                } = data;

                let totalQuantity = 0;
                let printContent = `
        <div style="font-family: monospace; width: 280px; margin: 0 auto; padding: 5px; text-align: left; font-size: 10px; line-height: 1.2;">
            <p style="text-align: center; margin-bottom: 5px; font-size: 11px;">
                <strong>Divine Word College of Calapan</strong><br>
                Gov. Infantado St. Calapan City Oriental Mindoro<br>
                TIN 001-000-033-000      NON-VAT<br>
                Accr No. 036-103286608-000508<br>
                Permit No. 1013-063-171588-000<br>
                MIN 130336072
            </p>
            --------------------------------------------------
            <p><strong>Transaction No:</strong> ${transactionNo}</p>
            <p><strong>Cashier:</strong> ${cashierName}</p>
            <p><strong>Date/Time:</strong> ${dateTime}</p>
            --------------------------------------------------
            <table style="width: 100%; text-align: left; margin-bottom: 5px; font-size: 10px; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align: left; width: 50%;">Description</th>
                        <th style="text-align: right; width: 15%;">Qty</th>
                        <th style="text-align: right; width: 15%;">Price</th>
                        <th style="text-align: right; width: 20%;">SubTotal</th>
                    </tr>
                </thead>
                <tbody>
    `;

                items.forEach(item => {
                    totalQuantity += item.quantity;
                    printContent += `
            <tr>
                <td style="padding: 2px 0;">${item.name}</td>
                <td style="text-align: right; padding: 2px 0;">${item.quantity}</td>
                <td style="text-align: right; padding: 2px 0;">${item.price.toFixed(2)}</td>
                <td style="text-align: right; padding: 2px 0;">${item.total.toFixed(2)}</td>
            </tr>
        `;
                });

                printContent += `
                </tbody>
            </table>
             --------------------------------------------------
            <div style="text-align: left;">
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <span><strong>Total Qty:</strong></span><span>${totalQuantity}</span>
                </p>
                 --------------------------------------------------
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <span><strong>Discount:</strong></span><span>₱${parseFloat(discount).toFixed(2)}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <span><strong>Total:</strong></span><span>₱${total}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <span><strong>Payment:</strong></span><span>${paymentMethod}</span>
                </p>
               <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <strong>Status:</strong>
                <span>
                    ${paymentMethod === 'Credit' ? 'Not Paid' : 'Completed'}
                </span>
            </p>

                 --------------------------------------------------
    `;

                if (paymentMethod === 'Cash') {
                    printContent += `
            <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <span><strong>Cash Tendered:</strong></span><span>₱${parseFloat(cashTendered).toFixed(2)}</span>
            </p>
            <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <span><strong>Change:</strong></span><span>₱-${parseFloat(change).toFixed(2)}</span>
            </p>
        `;
                } else if (paymentMethod === 'GCash') { // Add GCash reference condition
                    printContent += `
            <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <strong>GCash Ref:</strong><span>${gcashReference}</span>
            </p>
        `;
                } else if (paymentMethod === 'Credit') { // Add Credit payment method handling
                    printContent += `
            <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <strong>Charge to:</strong><span>${chargeType}</span>
            </p>
        `;

                    // Display charge details based on Charge Type
                    if (chargeType === 'Department') {
                        printContent += `
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>Full Name:</strong><span>${fullName}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>ID Number:</strong><span>${idNumber}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>Contact No:</strong><span>${contactNumber}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>Department:</strong><span>${department}</span>
                </p>
            `;
                    } else if (chargeType === 'Employee') {
                        printContent += `
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>Employee Name:</strong><span>${facultyName}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>ID Number:</strong><span>${facultyIdNumber}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                    <strong>Contact No:</strong><span>${facultyContactNumber}</span>
                </p>
            `;
                    }
                }

                printContent += `
             --------------------------------------------------
            <p style="text-align: center; font-size: 10px; margin-bottom: 5px;">This is your Sales Invoice</p>
            <p style="text-align: center; font-size: 10px;">${invoiceNumber}</p>
            <p style="text-align: center; font-size: 9px;">Thank you for shopping with us!</p>
        </div>
    `;

                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;
                const windowWidth = 300;
                const windowHeight = 600;
                const left = (screenWidth - windowWidth) / 2;
                const top = (screenHeight - windowHeight) / 2;

                const printWindow = window.open('', '',
                    `height=${windowHeight},width=${windowWidth},top=${top},left=${left}`);

                printWindow.document.write('<html><head><title>Sales Invoice</title><style>');
                printWindow.document.write('@media print {');
                printWindow.document.write(
                    'body { margin: 0; padding: 0; text-align: center; font-family: monospace; }');
                printWindow.document.write(
                    'div { width: 280px; margin: 0 auto; padding: 5px; font-size: 10px; text-align: left; line-height: 1.2; }'
                    );
                printWindow.document.write(
                    'table { width: 100%; text-align: left; font-size: 10px; margin-bottom: 5px; border-collapse: collapse;}'
                    );
                printWindow.document.write('th, td { padding: 2px 0; }');
                printWindow.document.write('h3 { font-size: 12px; margin-bottom: 5px; }');
                printWindow.document.write('p { font-size: 10px; margin: 2px 0;}');
                printWindow.document.write('strong { font-weight: bold; }');
                printWindow.document.write('}');
                printWindow.document.write('</style></head><body>');
                printWindow.document.write(printContent);
                printWindow.document.write('</body></html>');

                printWindow.document.close();
                printWindow.print();
            }

            function generateSalesInvoiceNumber() {
                const currentDate = new Date();
                const year = currentDate.getFullYear();
                const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                const randomNum = String(Math.floor(Math.random() * 900000) + 100000);
                return `SI-${year}${month}-${randomNum}`;
            }



            function resetTransaction() {
                // Clear the items table
                $('#datatablesSimple tbody').empty();

                // Show "No items found" message
                $('#no-items-message').show();

                // Reset the item index
                itemIndex = 1;

                // Clear the transactionItems array
                transactionItems = [];

                // Remove transaction data from local storage
                localStorage.removeItem('transactionItems');

                // Reset summary fields
                $('#subtotal').text('0.00');
                $('#discount').text('0.00');
                $('#total').text('0.00');

                // Reset payment fields
                $('input[name="paymentMethod"][value="Cash"]').prop('checked', true);
                $('#cashTendered').val('');
                $('#gcashReference').val('');
                $('#change').text('0.00');
                $('#gcashFields').hide();
                $('#cashFields').show();

                // Disable the SAVE button
                $('#saveTransaction').prop('disabled', true);

                // Reset other optional fields if needed
                $('#barcodeInput').val('');
                $('#quantityInput').val(1);

                // Focus on the barcode input for the next transaction
                $('#barcodeInput').focus();
            }

            // Function to save items to local storage
            function saveItemsToLocalStorage() {
                localStorage.setItem('transactionItems', JSON.stringify(transactionItems));
            }

            // Function to load items from local storage
            function loadItemsFromLocalStorage() {
                const savedItems = localStorage.getItem('transactionItems');
                if (savedItems) {
                    transactionItems = JSON.parse(savedItems);

                    // Loop through the items and add them to the table
                    transactionItems.forEach((item, index) => {
                        const row = `
                            <tr data-id="${item.id}">
                                <td>${index + 1}</td>
                                <td>${item.name} - ${item.unit_of_measurement}</td>
                                <td><input type="number" class="form-control quantity" value="${item.quantity}" min="1" max="${@json($items).find(i => i.id == item.id).qtyInStock}"></td>
                                <td>${item.price.toFixed(2)}</td>
                                <td class="total">${item.total.toFixed(2)}</td>
                                <td><button type="button" class="btn btn-outline-danger btn-sm remove-item" title="Void Item">VOID</button></td>
                            </tr>
                        `;
                        $('#datatablesSimple tbody').append(row);
                    });

                    itemIndex = transactionItems.length + 1;

                    // Check if the "No items found" message is visible and hide it
                    if (transactionItems.length > 0) {
                        $('#no-items-message').hide();
                    } else {
                        $('#no-items-message').show();
                    }

                    // Update the summary based on the loaded items
                    updateSummary();
                    toggleSaveButton();
                }
            }

            // Function to show alert messages with auto-dismiss and optional page reload
            function showAlert(message, type) {
                // Generate a unique ID for each alert to handle multiple alerts
                const alertId = 'alert-' + Date.now();

                // Determine the icon based on alert type
                let icon = '';
                if (type === 'success') {
                    icon = 'check-circle';
                } else if (type === 'danger') {
                    icon = 'exclamation-triangle';
                } else {
                    icon = 'info-circle';
                }

                // Create the alert HTML with the unique ID
                const alertHtml = `
                    <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-${icon} me-2 fa-lg"></i>
                        <div>${message}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                // Insert the alert into the DOM before the .scale-wrapper element
                $('.scale-wrapper').before(alertHtml);

                // Automatically close the alert after 1.5 seconds 
                setTimeout(function() {
                    var alertElement = document.getElementById(alertId);
                    if (alertElement) {
                        // Initialize a Bootstrap Alert instance
                        var alert = new bootstrap.Alert(alertElement);
                        alert.close(); // Close the alert
                    }
                }, 1000);

                // If you want to reload the page after the alert is closed, add an event listener
                $('#' + alertId).on('closed.bs.alert', function() {
                    if (type === 'success') {
                        location.reload();
                    }

                    // Optional: Focus on the barcode input after the alert is closed
                    $('#barcodeInput').focus();
                });
            }

            // Compute change initially in case there are preloaded items
            computeChange();
        });

        // Function to format and update the date/time
        function updateDateTime() {
            const now = new Date();

            // Format components
            const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            const day = String(now.getDate()).padStart(2, '0');
            const year = now.getFullYear();

            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'pm' : 'am';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? String(hours).padStart(2, '0') : '12'; // '0' hour should be '12'

            // Combine into desired format: m-d-Y h:i:s a
            const formattedDateTime = `${month}-${day}-${year} ${hours}:${minutes}:${seconds} ${ampm}`;

            // Update the HTML content
            document.getElementById('currentDateTime').textContent = formattedDateTime;
        }

        // Initial call to display the time immediately upon page load
        updateDateTime();

        // Update the time every second (1000 milliseconds)
        setInterval(updateDateTime, 1000);
    </script>

@endsection
