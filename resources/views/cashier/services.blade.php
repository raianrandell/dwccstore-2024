{{-- resources/views/services.blade.php --}}
@extends('layout.cashier')

@section('title', 'Services')

@section('content')

    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Services</li>
    </ol>

    <!-- Success and Error Messages -->
    @if (Session::has('success'))
        @include('components.alert', [
            'type' => 'success',
            'icon' => 'check-circle',
            'message' => Session::get('success'),
        ])
    @endif
    @if (Session::has('danger'))
        @include('components.alert', [
            'type' => 'danger',
            'icon' => 'exclamation-triangle',
            'message' => Session::get('danger'),
        ])
    @endif
    @if (Session::has('warning'))
        @include('components.alert', [
            'type' => 'warning',
            'icon' => 'exclamation-circle',
            'message' => Session::get('warning'),
        ])
    @endif

    <!-- Wrapper for scaling (optional) -->
    <div class="scale-wrapper">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-cash-register"></i>
                Services Transaction
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Main Column -->
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <!-- Left Side: Transaction No -->
                            <h4>Transaction No: #TRX{{ time() }}</h4>

                            <!-- Right Side: Date/Time and Services Button -->
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 me-3">
                                    Date/Time: <span id="currentDateTime">{{ now()->format('m-d-Y h:i:s a') }}</span>
                                </h6>
                                <button type="button" class="btn btn-outline-success btn-md" id="servicesButton"
                                    data-bs-toggle="modal" data-bs-target="#servicesModal">Add Service</button>
                            </div>
                        </div>
                        <!-- Services Summary Table -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Services Summary
                            </div>
                            <div class="card-body">
                                <table id="servicesDatatables" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Service Name</th>
                                            <th>Service Type</th>
                                            <th>Number of Copies</th>
                                            <th>Number of Hours</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="no-services-message">
                                            <td colspan="9" class="text-center">No services added.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar Column -->
                    <div class="col-md-3">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
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
                                                <span>Total Services:</span>
                                                <strong><span id="totalServices">0</span></strong>
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
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="cashOption"
                                            value="Cash" checked>
                                        <label class="form-check-label" for="cashOption">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="gcashOption"
                                            value="GCash">
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
                                            id="facultyCharge" value="Faculty">
                                        <label class="form-check-label" for="facultyCharge">Charge to Faculty</label>
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
                                            <input type="text" class="form-control" id="department"
                                                placeholder="Enter Department">
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
                                            <label for="facultyName" class="form-label">Faculty Name</label>
                                            <input type="text" class="form-control" id="facultyName"
                                                placeholder="Enter Faculty Name">
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

        <!-- Modal for Services Selection -->
        <div class="modal fade" id="servicesModal" tabindex="-1" aria-labelledby="servicesModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select a Service</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="servicesForm">
                            <!-- Service Selection -->
                            <div class="mb-3">
                                <label for="serviceSelect" class="form-label">Choose a Service</label>
                                <select class="form-select" id="serviceSelect" name="serviceSelect" required>
                                    <option value="" selected>Select a Service</option>
                                    @foreach ($services as $service)
                                        @if ($service->status == 1)
                                            <option value="{{ $service->id }}" data-price="{{ $service->price }}">
                                                {{ $service->service_name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Please select a service.
                                </div>
                            </div>

                            <!-- Price Input (Editable) -->
                            <div class="mb-3">
                                <label for="servicePrice" class="form-label">Price</label>
                                <input type="number" class="form-control" id="servicePrice" name="servicePrice"
                                    min="0" step="0.01" placeholder="Enter price" required>
                                <div class="invalid-feedback">
                                    Please enter a valid price.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Service Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="feeStructure" id="perCopy"
                                        value="per_copy" required>
                                    <label class="form-check-label" for="perCopy">
                                        Per Copy
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="feeStructure" id="perHour"
                                        value="per_hour">
                                    <label class="form-check-label" for="perHour">
                                        Per Hour
                                    </label>
                                </div>
                                <!-- Removed Fee per Amount Option -->
                                <div class="invalid-feedback">
                                    Please select a fee structure.
                                </div>
                            </div>

                            <!-- Conditional Input for Fee Amount -->
                            <div class="mb-3" id="feeAmountInput" style="display: none;">
                                <label for="feeAmountValue" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="feeAmountValue" name="feeAmountValue"
                                    min="0" step="0.01">
                                <div class="invalid-feedback">
                                    Please enter a valid amount.
                                </div>
                            </div>

                            <!-- Conditional Input for Number of Copies -->
                            <div class="mb-3" id="numberOfCopiesInput" style="display: none;">
                                <label for="numberOfCopies" class="form-label">Number of Copies</label>
                                <input type="number" class="form-control" id="numberOfCopies" name="numberOfCopies"
                                    min="1" step="1">
                                <div class="invalid-feedback">
                                    Please enter a valid number of copies.
                                </div>
                            </div>

                            <!-- Conditional Input for Number of Hours -->
                            <div class="mb-3" id="numberOfHoursInput" style="display: none;">
                                <label for="numberOfHours" class="form-label">Number of Hours</label>
                                <input type="number" class="form-control" id="numberOfHours" name="numberOfHours"
                                    min="1" step="0.1">
                                <div class="invalid-feedback">
                                    Please enter a valid number of hours.
                                </div>
                            </div>

                            <!-- Total Display -->
                            <div class="mb-3">
                                <label for="totalDisplay" class="form-label"><strong>Total</strong></label>
                                <span id="totalDisplay" class="form-control-plaintext">0.00</span>
                                <!-- Display total here -->
                            </div>

                            <!-- Hidden Fields to Capture Service Data -->
                            <input type="hidden" id="serviceType" name="serviceType">
                            <input type="hidden" id="numberOfCopiesHidden" name="numberOfCopies">
                            <input type="hidden" id="numberOfHoursHidden" name="numberOfHours">
                            <input type="hidden" id="totalHidden" name="total">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="addServiceButtonModal">Add</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Global variables
            let serviceItems = [];

            $(document).ready(function() {
                // Initialize Select2 for the service dropdown
                $('#fetchService').select2({
                    placeholder: "Select the Service",
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5'
                });

                let serviceIndex = 1; // Index for each row added

                // Load services from local storage
                loadServicesFromLocalStorage();

                // Initial UI states
                computeChange();
                toggleSaveButton();
                updateServicesTable(); // Update services table based on loaded serviceItems

                // Add service button click (Main Column)
                $('#addServiceButton').click(function() {
                    const serviceId = $('#fetchService').val();
                    if (!serviceId) {
                        showAlert('Please select a service first.', 'danger');
                        return;
                    }

                    const selectedOption = $('#fetchService option:selected').text();
                    const serviceName = selectedOption.split(' - ')[0];
                    const serviceType = selectedOption.split(' - ')[1];

                    const serviceData = @json($services).find(s => s.id == serviceId);

                    if (serviceData) {
                        // Check if the service is already added
                        const existingService = serviceItems.find(svc => svc.id === serviceId);
                        if (existingService) {
                            showAlert('Service already added.', 'warning');
                            return;
                        }

                        // Create a new service item
                        const serviceToAdd = {
                            id: serviceData.id,
                            name: serviceName,
                            service_type: serviceType,
                            price: parseFloat(serviceData.price),
                            total: parseFloat(serviceData.price),
                            // Additional fields can be added as needed
                        };

                        serviceItems.push(serviceToAdd);
                        const row = `
                        <tr data-id="${serviceToAdd.id}">
                            <td>${serviceIndex}</td>
                            <td>${serviceToAdd.name}</td>
                            <td>${serviceToAdd.service_type}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>₱${serviceToAdd.price.toFixed(2)}</td>
                            <td>₱${serviceToAdd.total.toFixed(2)}</td>
                            <td><button type="button" class="btn btn-outline-danger btn-sm remove-service" title="Void Service">VOID</button></td>
                        </tr>
                    `;
                        $('#servicesDatatables tbody').append(row);
                        serviceIndex++;

                        showAlert('Service added successfully.', 'success');
                    }

                    saveServicesToLocalStorage();
                    $('#no-services-message').hide();
                    updateSummary();
                    toggleSaveButton();
                });

                // Remove service without confirmation
                $('#servicesDatatables').on('click', '.remove-service', function() {
                    const row = $(this).closest('tr');
                    const serviceId = row.data('id');

                    // Remove from serviceItems array
                    serviceItems = serviceItems.filter(service => service.id != serviceId);

                    // Remove row from table
                    row.remove();

                    // Show no services message if no services left
                    if ($('#servicesDatatables tbody tr').length === 0) {
                        $('#no-services-message').show();
                    }

                    // Save changes to local storage
                    saveServicesToLocalStorage();
                    updateSummary();
                    toggleSaveButton();

                    // Optionally, you can show an alert
                    showAlert('Service removed successfully.', 'success');
                });

                // Compute change
                function computeChange() {
                    const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                    const total = parseFloat($('#total').text()) || 0;
                    let change = 0;

                    if (paymentMethod === 'Cash') {
                        const cashTendered = parseFloat($('#cashTendered').val()) || 0;
                        change = cashTendered - total;
                        if (change < 0) {
                            change = 0.00;
                        }
                        $('#change').text(change.toFixed(2));
                    } else {
                        $('#change').text('0.00');
                    }
                }

                function updateSummary() {
                    let subtotal = serviceItems.reduce((acc, svc) => acc + svc.total, 0);

                    let discount = parseFloat($('#discount').val()) || 0;
                    let discountAmount = (subtotal * discount) / 100;
                    let total = subtotal - discountAmount;
                    let totalServices = serviceItems.length;

                    $('#subtotal').text(subtotal.toFixed(2));
                    $('#discount').text(discountAmount.toFixed(2));
                    $('#total').text(total.toFixed(2));
                    $('#totalServices').text(totalServices);

                    computeChange();
                }


                // Toggle save button
                function toggleSaveButton() {
                    const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                    let enable = false;

                    if (paymentMethod === 'Cash') {
                        const cashTendered = parseFloat($('#cashTendered').val()) || 0;
                        const total = parseFloat($('#total').text()) || 0;
                        enable = cashTendered >= total && serviceItems.length > 0;
                    } else if (paymentMethod === 'GCash') {
                        const gcashReference = $('#gcashReference').val();
                        enable = gcashReference.trim() !== '' && serviceItems.length > 0;
                    } else if (paymentMethod === 'Credit') {
                        const chargeType = $('input[name="chargeType"]:checked').val() || '';
                        if (!chargeType) {
                            enable = false;
                        } else {
                            enable = serviceItems.length > 0 && validateChargeTypeInputs();
                        }
                    }

                    $('#saveTransaction').prop('disabled', !enable);
                }

                // Validate charge type inputs
                function validateChargeTypeInputs() {
                    const chargeType = $('input[name="chargeType"]:checked').val();
                    let isValid = true;

                    if (chargeType === 'Department') {
                        isValid = $('#fullName').val().trim() !== '' &&
                            $('#idNumber').val().trim() !== '' &&
                            $('#contactNumber').val().trim() !== '' &&
                            $('#department').val().trim() !== '';
                    } else if (chargeType === 'Faculty') {
                        isValid = $('#facultyName').val().trim() !== '' &&
                            $('#facultyIdNumber').val().trim() !== '' &&
                            $('#facultyContactNumber').val().trim() !== '';
                    }

                    return isValid;
                }

                // Payment method change
                $('input[name="paymentMethod"]').change(function() {
                    const selectedMethod = $(this).val();
                    if (selectedMethod === 'Cash') {
                        $('#cashFields').show();
                        $('#gcashFields').hide();
                        $('#creditFields').hide();
                    } else if (selectedMethod === 'GCash') {
                        $('#cashFields').hide();
                        $('#gcashFields').show();
                        $('#creditFields').hide();
                    } else {
                        $('#cashFields').hide();
                        $('#gcashFields').hide();
                        $('#creditFields').show();
                    }
                    computeChange();
                    toggleSaveButton();
                });

                // Discount input change
                $('#discount').on('input', function() {
                    updateSummary();
                    toggleSaveButton();
                });

                // Cash tendered change
                $('#cashTendered').on('input', function() {
                    computeChange();
                    toggleSaveButton();
                });

                // GCash reference change
                $('#gcashReference').on('input', function() {
                    toggleSaveButton();
                });

                // Charge type change
                $('input[name="chargeType"]').change(function() {
                    const chargeType = $(this).val();
                    $('#departmentFields input').val('');
                    $('#facultyFields input').val('');
                    if (chargeType === 'Department') {
                        $('#departmentFields').show();
                        $('#facultyFields').hide();
                    } else if (chargeType === 'Faculty') {
                        $('#facultyFields').show();
                        $('#departmentFields').hide();
                    } else {
                        $('#departmentFields').hide();
                        $('#facultyFields').hide();
                    }
                    toggleSaveButton();
                });

                // Save transaction
                $('#saveTransaction').click(function() {
                    const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                    let isValid = true;
                    let errorMessage = '';
                    const chargeType = $('input[name="chargeType"]:checked').val();

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
                        if (!chargeType) {
                            isValid = false;
                            errorMessage = 'Please select a charge type.';
                        } else {
                            if (chargeType === 'Department') {
                                const fullName = $('#fullName').val();
                                const idNumber = $('#idNumber').val();
                                const contactNumber = $('#contactNumber').val();
                                const department = $('#department').val();
                                if (!fullName || !idNumber || !contactNumber || !department) {
                                    isValid = false;
                                    errorMessage = 'Please fill out all fields for department charge.';
                                }
                            } else if (chargeType === 'Faculty') {
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

                    if (serviceItems.length === 0) {
                        isValid = false;
                        errorMessage = 'Please add at least one service to the transaction.';
                    }

                    if (!isValid) {
                        showAlert(errorMessage, 'danger');
                        return;
                    }

                    // Prepare data for AJAX submission
                    const postData = {
                        _token: '{{ csrf_token() }}',
                        services: serviceItems,
                        paymentMethod: paymentMethod,
                        cashTendered: $('#cashTendered').val(),
                        gcashReference: $('#gcashReference').val(),
                        subtotal: $('#subtotal').text(),
                        discount: $('#discount').text(),
                        total: $('#total').text(),
                        chargeType: chargeType,
                        fullName: $('#fullName').val(),
                        idNumber: $('#idNumber').val(),
                        contactNumber: $('#contactNumber').val(),
                        department: $('#department').val(),
                        facultyName: $('#facultyName').val(),
                        facultyIdNumber: $('#facultyIdNumber').val(),
                        facultyContactNumber: $('#facultyContactNumber').val(),
                    };

                    $.ajax({
                        url: '{{ route('cashier.save_services') }}',
                        method: 'POST',
                        data: postData,
                        success: function(response) {
                            if (response.success) {
                                showAlert('Transaction saved successfully.', 'success');

                                const transactionNo = response.transaction_no || 'N/A';
                                const cashierName =
                                    '{{ Auth::guard('cashier')->user()->full_name }}';

                                // Trigger invoice generation and printing
                                generateServicesInvoice({
                                    transactionNo: transactionNo,
                                    cashierName: cashierName,
                                    dateTime: new Date().toLocaleString(),
                                    services: serviceItems,
                                    discount: $('#discount').text(),
                                    total: $('#total').text(),
                                    paymentMethod: paymentMethod,
                                    cashTendered: $('#cashTendered').val(),
                                    change: $('#change').text(),
                                    gcashReference: $('#gcashReference').val(),
                                    chargeType: chargeType,
                                    fullName: $('#fullName').val(),
                                    idNumber: $('#idNumber').val(),
                                    contactNumber: $('#contactNumber').val(),
                                    department: $('#department').val(),
                                    facultyName: $('#facultyName').val(),
                                    facultyIdNumber: $('#facultyIdNumber').val(),
                                    facultyContactNumber: $('#facultyContactNumber').val()
                                });

                                // Reset the transaction to prepare for the next one
                                clearServicesFromLocalStorage();
                                updateServicesTable();
                                resetTransaction();
                            } else {
                                showAlert('Error saving transaction: ' + response.message,
                                    'danger');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) { // Validation error
                                showAlert(xhr.responseJSON.message, 'danger');
                            } else {
                                showAlert('An unexpected error occurred.', 'danger');
                            }
                        }
                    });
                });

                // Function to generate and print invoice
                function generateServicesInvoice(data) {
                    const invoiceNumber = generateServicesInvoiceNumber();
                    const {
                        transactionNo,
                        cashierName,
                        dateTime,
                        services,
                        discount,
                        total,
                        paymentMethod,
                        cashTendered,
                        change,
                        gcashReference,
                        chargeType,
                        fullName,
                        idNumber,
                        contactNumber,
                        department,
                        facultyName,
                        facultyIdNumber,
                        facultyContactNumber
                    } = data;

                    let printContent = `
            <div style="font-family: Arial, sans-serif; width: 250px; margin: 0 auto; padding: 10px; text-align: left; font-size: 12px;">
                <p style="text-align: center; margin-bottom: 10px; font-size: 14px;">
                    <strong>Divine Word College of Calapan</strong><br>
                    Gov. Infantado St. Calapan City Oriental Mindoro<br>
                    TIN 001-000-033-000 &nbsp;&nbsp;&nbsp;&nbsp; NON-VAT<br>
                    Accr No. 036-103286608-000508<br>
                    Permit No. 1013-063-171588-000<br>
                    MIN 130336072
                </p>
                ====================================
                <p><strong>Transaction No:</strong> ${transactionNo}</p>
                <p><strong>Cashier:</strong> ${cashierName}</p>
                <p><strong>Date/Time:</strong> ${dateTime}</p>
                ====================================
                <table style="width: 100%; text-align: left; margin-bottom: 10px; font-size: 12px;">
                    <tr>
                        <th style="text-align: left;">Description</th>
                        <th style="text-align: center;">Copies</th>
                        <th style="text-align: center;">Hours</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Sub-Total</th>
                    </tr>
            `;

                    services.forEach((service) => {
                        const copies = service.number_of_copies !== null && service.number_of_copies > 0 ?
                            service.number_of_copies : '';
                        const hours = service.number_of_hours !== null && service.number_of_hours > 0 ? service
                            .number_of_hours : '';
                        printContent += `
                <tr>
                    <td>${service.name}</td>
                    <td style="text-align: center;">${copies}</td>
                    <td style="text-align: center;">${hours}</td>
                    <td style="text-align: right;">₱${service.price.toFixed(2)}</td>
                    <td style="text-align: right;">₱${service.total.toFixed(2)}</td>
                </tr>
            `;
                    });

                    printContent += `
            </table>
            ====================================
            <div style="text-align: left;">
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <span><strong>Discount:</strong></span><span>₱${parseFloat(discount).toFixed(2)}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <span><strong>Total:</strong></span><span>₱${total}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <span><strong>Payment Method:</strong></span><span>${paymentMethod}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Status:</strong>
                    <span>${paymentMethod === 'Credit' ? 'Not Paid' : 'Completed'}</span>
                </p>
                ====================================
            `;

                    if (paymentMethod === 'Cash') {
                        printContent += `
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <span><strong>Cash Tendered:</strong></span><span>₱${parseFloat(cashTendered).toFixed(2)}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <span><strong>Change:</strong></span><span>₱-${parseFloat(change).toFixed(2)}</span>
                </p>
            `;
                    } else if (paymentMethod === 'GCash') {
                        printContent += `
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>GCash Reference:</strong><span>${gcashReference}</span>
                </p>
            `;
                    } else if (paymentMethod === 'Credit') {
                        printContent += `
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Charge to:</strong><span>${chargeType}</span>
                </p>
            `;

                        if (chargeType === 'Department') {
                            printContent += `
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Full Name:</strong><span>${fullName}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>ID Number:</strong><span>${idNumber}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Contact Number:</strong><span>${contactNumber}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Department:</strong><span>${department}</span>
                </p>
            `;
                        } else if (chargeType === 'Faculty') {
                            printContent += `
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Faculty Name:</strong><span>${facultyName}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>ID Number:</strong><span>${facultyIdNumber}</span>
                </p>
                <p style="display: flex; justify-content: space-between; margin: 0;">
                    <strong>Contact Number:</strong><span>${facultyContactNumber}</span>
                </p>
            `;
                        }
                    }

                    printContent += `
                ====================================
                <p style="text-align: center; font-size: 12px; margin-bottom: 10px;">This is your Sales Invoice</p>
                <p style="text-align: center; font-size: 12px;">${invoiceNumber}</p>
                <p style="text-align: center; font-size: 10px;">Thank you for choosing our services!</p>
            </div>
            `;

                    // Open print window
                    const screenWidth = window.innerWidth;
                    const screenHeight = window.innerHeight;
                    const windowWidth = 400;
                    const windowHeight = 600;
                    const left = (screenWidth - windowWidth) / 2;
                    const top = (screenHeight - windowHeight) / 2;

                    const printWindow = window.open('', '',
                        `height=${windowHeight},width=${windowWidth},top=${top},left=${left}`);
                    printWindow.document.write('<html><head><title>Sales Invoice</title><style>');
                    printWindow.document.write('@media print {');
                    printWindow.document.write(
                        'body { margin: 0; padding: 0; text-align: center; font-family: Arial, sans-serif; }');
                    printWindow.document.write(
                        'div { width: 300px; margin: 0 auto; padding: 10px; font-size: 12px; border: 1px solid #ccc; text-align: left; }'
                    );
                    printWindow.document.write(
                        'table { width: 100%; text-align: left; font-size: 12px; margin-bottom: 10px; }');
                    printWindow.document.write('th, td { padding: 5px; }');
                    printWindow.document.write('h3 { font-size: 14px; margin-bottom: 10px; }');
                    printWindow.document.write('p { font-size: 12px; }');
                    printWindow.document.write('}');
                    printWindow.document.write('</style></head><body>');
                    printWindow.document.write(printContent);
                    printWindow.document.write('</body></html>');

                    printWindow.document.close();
                    printWindow.print();
                }



                function generateServicesInvoiceNumber() {
                    const currentDate = new Date();
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const randomNum = String(Math.floor(Math.random() * 900000) + 100000);
                    return `SI-${year}${month}-${randomNum}`;
                }

                function resetTransaction() {
                    $('#servicesDatatables tbody').empty();
                    $('#no-services-message').show();
                    serviceIndex = 1;
                    serviceItems = [];
                    clearServicesFromLocalStorage();
                    $('#subtotal').text('0.00');
                    $('#discount').text('0.00');
                    $('#total').text('0.00');
                    $('input[name="paymentMethod"][value="Cash"]').prop('checked', true);
                    $('#cashTendered').val('');
                    $('#gcashReference').val('');
                    $('#change').text('0.00');
                    $('#gcashFields').hide();
                    $('#cashFields').show();
                    $('#creditFields').hide();
                    $('input[name="chargeType"]').prop('checked', false);
                    $('#saveTransaction').prop('disabled', true);
                    $('#cashTendered').focus();
                }

                // Local storage for services
                function saveServicesToLocalStorage() {
                    localStorage.setItem('serviceItems', JSON.stringify(serviceItems));
                }

                function loadServicesFromLocalStorage() {
                    const savedServices = localStorage.getItem('serviceItems');
                    if (savedServices) {
                        try {
                            serviceItems = JSON.parse(savedServices);

                            serviceItems.forEach((service, index) => {
                                const row = `
                                <tr data-id="${service.id}">
                                    <td>${index + 1}</td>
                                    <td>${service.name}</td>
                                    <td>${service.service_type}</td>
                                    <td>${service.number_of_copies || '-'}</td>
                                    <td>${service.number_of_hours || '-'}</td>
                                    <td>₱${service.price.toFixed(2)}</td>
                                    <td>₱${service.total.toFixed(2)}</td>
                                    <td><button type="button" class="btn btn-outline-danger btn-sm remove-service" data-index="${index}">VOID</button></td>
                                </tr>
                            `;
                                $('#servicesDatatables tbody').append(row);
                                serviceIndex++;
                            });

                            if (serviceItems.length > 0) {
                                $('#no-services-message').hide();
                            } else {
                                $('#no-services-message').show();
                            }

                            updateSummary();
                            toggleSaveButton();
                        } catch (e) {
                            console.error('Error parsing serviceItems from Local Storage:', e);
                            serviceItems = [];
                            localStorage.removeItem('serviceItems');
                        }
                    }
                }

                function clearServicesFromLocalStorage() {
                    localStorage.removeItem('serviceItems');
                }

                // Alert messages
                function showAlert(message, type) {
                    const alertId = 'alert-' + Date.now();
                    let icon = '';
                    if (type === 'success') {
                        icon = 'check-circle';
                    } else if (type === 'danger') {
                        icon = 'exclamation-triangle';
                    } else {
                        icon = 'info-circle';
                    }

                    const alertHtml = `
                    <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-${icon} me-2 fa-lg"></i>
                        <div>${message}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                    $('.scale-wrapper').before(alertHtml);

                    setTimeout(function() {
                        var alertElement = document.getElementById(alertId);
                        if (alertElement) {
                            var alert = new bootstrap.Alert(alertElement);
                            alert.close();
                        }
                    }, 1500);

                    $('#' + alertId).on('closed.bs.alert', function() {
                        if (type === 'success') {
                            location.reload();
                        }
                        $('#cashTendered').focus();
                    });
                }

                // Update date/time every second
                function updateDateTime() {
                    const now = new Date();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const year = now.getFullYear();
                    let hours = now.getHours();
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'pm' : 'am';
                    hours = hours % 12;
                    hours = hours ? String(hours).padStart(2, '0') : '12';

                    const formattedDateTime = `${month}-${day}-${year} ${hours}:${minutes}:${seconds} ${ampm}`;
                    document.getElementById('currentDateTime').textContent = formattedDateTime;
                }

                updateDateTime();
                setInterval(updateDateTime, 1000);

                $('input[name="feeStructure"]').change(function() {
                    const selectedType = $(this).val();
                    // Removed feeAmountInput logic
                    $('#numberOfCopiesInput').hide();
                    $('#numberOfHoursInput').hide();

                    if (selectedType === 'per_copy') {
                        $('#numberOfCopiesInput').show();
                    } else if (selectedType === 'per_hour') {
                        $('#numberOfHoursInput').show();
                    }
                    calculateServiceTotal();
                });

                $('#serviceSelect').change(function() {
                    const selectedOption = $(this).find('option:selected');
                    const serviceName = selectedOption.text().trim();


                    // **Corrected Logic:** Only disable the opposite radio button
                    if (serviceName === 'Computer Rental') {
                        $('#perHour').prop('checked', true);
                        $('#perCopy').prop('disabled', true).prop('checked',
                        false); // Disable and uncheck perCopy

                    } else {
                        $('#perCopy').prop('checked', true);
                        $('#perHour').prop('disabled', true).prop('checked',
                        false); // Disable and uncheck perHour
                    }



                    $('input[name="feeStructure"]:checked').trigger('change');
                });

                $('input[name="feeStructure"]').change(function() {


                    //Re-enable both before checking again
                    $('#perCopy').prop('disabled', false);
                    $('#perHour').prop('disabled', false);



                    if ($('#perHour').is(':checked')) {
                        $('#hours').prop('disabled', false);
                        $('#copies').prop('disabled', true).val('');
                        $('#perCopy').prop('disabled', true);




                    } else if ($('#perCopy').is(':checked')) {
                        $('#copies').prop('disabled', false);
                        $('#hours').prop('disabled', true).val('');
                        $('#perHour').prop('disabled', true);


                    }

                });

                // Trigger initial change 
                $('select[name="service_name"]').trigger('change');


                // Recalculate total when relevant inputs change in Modal
                $('#servicePrice, #feeAmountValue, #numberOfCopies, #numberOfHours').on('input', function() {
                    calculateServiceTotal();
                });

                function calculateServiceTotal() {
                    const price = parseFloat($('#servicePrice').val()) || 0;
                    const feeStructure = $('input[name="feeStructure"]:checked').val();
                    let total = price;
                    let serviceType = '';
                    let copies = 0;
                    let hours = 0;

                    if (feeStructure === 'per_copy') {
                        copies = parseInt($('#numberOfCopies').val()) || 0;
                        total = price * copies;
                        serviceType = 'Per Copy';
                    } else if (feeStructure === 'per_hour') {
                        hours = parseFloat($('#numberOfHours').val()) || 0;
                        total = price * hours;
                        serviceType = 'Per Hour';
                    }

                    $('#totalDisplay').text(total.toFixed(2));
                    $('#serviceType').val(serviceType);
                    $('#numberOfCopiesHidden').val(copies);
                    $('#numberOfHoursHidden').val(hours);
                    $('#totalHidden').val(total.toFixed(2));
                }

                const servicesModal = document.getElementById('servicesModal');
                servicesModal.addEventListener('shown.bs.modal', function() {
                    calculateServiceTotal();
                });

                servicesModal.addEventListener('hidden.bs.modal', function() {
                    const form = $('#servicesForm')[0];
                    form.reset();
                    $('#feeAmountInput').hide();
                    $('#numberOfCopiesInput').hide();
                    $('#numberOfHoursInput').hide();
                    $('#totalDisplay').text('0.00');
                });

                $('#addServiceButtonModal').click(function() {
                    const form = $('#servicesForm')[0];
                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        return;
                    }

                    const serviceId = $('#serviceSelect').val();
                    const serviceName = $('#serviceSelect option:selected').text();
                    const price = parseFloat($('#servicePrice').val()) || 0;
                    const feeStructure = $('input[name="feeStructure"]:checked').val();
                    const serviceType = $('#serviceType').val();
                    const copies = feeStructure === 'per_copy' ? parseInt($('#numberOfCopies').val()) : null;
                    const hours = feeStructure === 'per_hour' ? parseFloat($('#numberOfHours').val()) : null;
                    const total = parseFloat($('#totalDisplay').text()) || 0;

                    // Validation for price being greater than 0
                    if (price <= 0) {
                        showAlert('Price must be greater than 0.', 'danger');
                        return;
                    }

                    // Validation for Number of Copies if Fee Structure is Per Copy
                    if (feeStructure === 'per_copy' && (!copies || copies <= 0)) {
                        showAlert('Please enter a valid number of copies.', 'danger');
                        return;
                    }

                    // Validation for Number of Hours if Fee Structure is Per Hour
                    if (feeStructure === 'per_hour' && (!hours || hours <= 0)) {
                        showAlert('Please enter a valid number of hours.', 'danger');
                        return;
                    }


                    // Check if service already exists
                    const existingService = serviceItems.find(svc => svc.id === serviceId);
                    if (existingService) {
                        showAlert('Service already added.', 'warning');
                        return;
                    }

                    const serviceItem = {
                        id: serviceId,
                        name: serviceName,
                        service_type: serviceType,
                        price: price,
                        feeStructure: feeStructure,
                        number_of_copies: copies,
                        number_of_hours: hours,
                        total: total
                    };

                    serviceItems.push(serviceItem);
                    saveServicesToLocalStorage();
                    updateServicesTable();
                    $('#servicesModal').modal('hide');
                    form.reset();
                    form.classList.remove('was-validated');
                    $('#feeAmountInput').hide();
                    $('#numberOfCopiesInput').hide();
                    $('#numberOfHoursInput').hide();
                    $('#totalDisplay').text('0.00');
                    updateSummary(); // Update summary after adding a service
                });


                function updateServicesTable() {
                    let tableBody = $('#servicesDatatables tbody');
                    tableBody.empty();

                    if (serviceItems.length === 0) {
                        tableBody.html(
                            '<tr id="no-services-message"><td colspan="9" class="text-center">No services added.</td></tr>'
                        );
                    } else {
                        serviceItems.forEach((service, index) => {
                            let copies = service.number_of_copies !== null && service.number_of_copies > 0 ?
                                service.number_of_copies : '-';
                            let hours = service.number_of_hours !== null && service.number_of_hours > 0 ?
                                service.number_of_hours : '-';

                            tableBody.append(`
                            <tr data-id="${service.id}">
                                <td>${index + 1}</td>
                                <td>${service.name}</td>
                                <td>${service.service_type}</td>
                                <td>${copies}</td>
                                <td>${hours}</td>
                                <td>₱${service.price.toFixed(2)}</td>
                                <td>₱${service.total.toFixed(2)}</td>
                                <td><button type="button" class="btn btn-outline-danger btn-sm remove-service" data-index="${index}">VOID</button></td>
                            </tr>
                        `);
                        });
                    }
                    updateSummary(); // Update summary whenever services change
                }

                // Generate invoice number
                function generateServicesInvoiceNumber() {
                    const currentDate = new Date();
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const randomNum = String(Math.floor(Math.random() * 900000) + 100000);
                    return `SI-${year}${month}-${randomNum}`;
                }

                // Reset transaction
                function resetTransaction() {
                    $('#servicesDatatables tbody').empty();
                    $('#no-services-message').show();
                    serviceIndex = 1;
                    serviceItems = [];
                    clearServicesFromLocalStorage();
                    $('#subtotal').text('0.00');
                    $('#discount').text('0.00');
                    $('#total').text('0.00');
                    $('input[name="paymentMethod"][value="Cash"]').prop('checked', true);
                    $('#cashTendered').val('');
                    $('#gcashReference').val('');
                    $('#change').text('0.00');
                    $('#gcashFields').hide();
                    $('#cashFields').show();
                    $('#creditFields').hide();
                    $('input[name="chargeType"]').prop('checked', false);
                    $('#saveTransaction').prop('disabled', true);
                    $('#cashTendered').focus();
                }

                // Enable or disable Save Transaction button based on conditions
                function toggleSaveTransactionButton() {
                    const paymentMethod = $('input[name="paymentMethod"]:checked').val();
                    const isChargeTypeSelected = $('input[name="chargeType"]:checked').length > 0;

                    if (paymentMethod === 'Credit' && isChargeTypeSelected) {
                        $('#saveTransaction').prop('disabled', false);
                    } else {
                        $('#saveTransaction').prop('disabled', true);
                    }
                }

                // Listen for changes in payment method
                $('input[name="paymentMethod"]').change(function() {
                    const selectedMethod = $(this).val();

                    if (selectedMethod === 'Credit') {
                        $('#creditFields').show();
                        $('#cashFields').hide();
                        $('#gcashFields').hide();
                    } else if (selectedMethod === 'Cash') {
                        $('#cashFields').show();
                        $('#creditFields').hide();
                        $('#gcashFields').hide();
                    } else if (selectedMethod === 'GCash') {
                        $('#gcashFields').show();
                        $('#cashFields').hide();
                        $('#creditFields').hide();
                    }

                    toggleSaveTransactionButton();
                });

                // Listen for changes in charge type
                $('input[name="chargeType"]').change(function() {
                    toggleSaveTransactionButton();
                });

                // Initial check to ensure the button state is correct on page load
                toggleSaveTransactionButton();


            });
        </script>

    @endsection
