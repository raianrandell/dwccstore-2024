{{-- resources/views/services_history.blade.php --}}
@extends('layout.cashier')

@section('title', 'Services History')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Services History</li>
    </ol>

    <!-- Success and Error Messages -->
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fa-lg"></i>
            <div>{{ Session::get('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (Session::has('danger'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
            <div>{{ Session::get('danger') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Services History
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Transaction Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('m-d-Y h:i:s a') }}</td>
                            <td>{{ $transaction->transaction_no }}</td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm rounded-circle view_service_btn"
                                    title="View" data-transaction-no="{{ $transaction->transaction_no }}"
                                    data-bs-toggle="modal" data-bs-target="#serviceTransactionModal">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Service Transaction Details -->
    <div class="modal fade" id="serviceTransactionModal" tabindex="-1" aria-labelledby="serviceTransactionModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Service Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between">
                        <p><strong>Transaction No:</strong> <span id="serviceTransactionNo"></span></p>
                        <p><strong>Cashier: </strong><span id="serviceCashierName"></span></p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p><strong>Date/Time:</strong> <span id="serviceTransactionDateTime"></span></p>
                        <p><strong>Payment Method:</strong> <span id="servicePaymentMethod"></span></p>
                    </div>
                    {{-- <div class="d-flex justify-content-between">
                        <p><strong>Status:</strong> <span id="serviceStatus"></span></p>
                    </div> --}}

                    <!-- Transaction Items Table -->
                    <table id="serviceTransactionItemsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Service Name</th>
                                <th>Service Type</th>
                                <th>Number of Copies</th>
                                <th>Number of Hours</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Transaction items will be injected here via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <!-- Print Button -->
                    <button type="button" class="btn btn-outline-primary" id="servicePrintBtn">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Function to generate invoice number
        function generateServicesInvoiceNumber() {
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const randomNum = String(Math.floor(Math.random() * 900000) + 100000);
            return `SI-${year}${month}-${randomNum}`;
        }

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
                facultyContactNumber,
                status // Ensure status is destructured, but will be replaced with logic
            } = data;

            // Adjust the invoice content based on charge type
            let chargeInfo = '';
            if (chargeType === 'Department') {
                chargeInfo = `
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>Full Name:</strong><span>${fullName}</span></p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>ID Number:</strong><span>${idNumber}</span></p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>Contact No:</strong><span>${contactNumber}</span></p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>Department:</strong><span>${department}</span></p>
                `;
            } else if (chargeType === 'Employee') {
                chargeInfo = `
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>Employee Name:</strong><span>${facultyName}</span></p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>ID Number:</strong><span>${facultyIdNumber}</span></p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;"><strong>Contact No:</strong><span>${facultyContactNumber}</span></p>
                `;
            }

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
                    <p><strong>Txn No:</strong> ${transactionNo}</p>
                    <p><strong>Cashier:</strong> ${cashierName}</p>
                    <p><strong>Date/Time:</strong> ${dateTime}</p>
                    --------------------------------------------------

                    <table style="width: 100%; text-align: left; margin-bottom: 5px; font-size: 10px; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left; width: 40%;">Description</th>
                                <th style="text-align: center; width: 10%;">Copies</th>
                                <th style="text-align: center; width: 10%;">Hours</th>
                                <th style="text-align: right; width: 20%;">Price</th>
                                <th style="text-align: right; width: 20%;">SubTotal</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            services.forEach((service, index) => {
                const copies = service.number_of_copies !== null && service.number_of_copies > 0 ?
                    service.number_of_copies : ''; // changed '-' to '' to match first function
                // **Remove decimals from hours by parsing as integer**
                const hours = service.number_of_hours !== null && service.number_of_hours > 0 ?
                    parseInt(service.number_of_hours) : ''; // changed '-' to '' to match first function
                printContent += `
                    <tr>
                        <td style="padding: 2px 0;">${service.name}</td>
                        <td style="text-align: center; padding: 2px 0;">${copies}</td>
                        <td style="text-align: center; padding: 2px 0;">${hours}</td>
                        <td style="text-align: right; padding: 2px 0;">₱${service.price.toFixed(2)}</td>
                        <td style="text-align: right; padding: 2px 0;">₱${service.total.toFixed(2)}</td>
                    </tr>
                `;
            });

            printContent += `
                        </tbody>
                    </table>
                    --------------------------------------------------
                    <div style="text-align: left;">
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
                            <span>${paymentMethod === 'Credit' ? 'Not Paid' : 'Completed'}</span>

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
            } else if (paymentMethod === 'GCash') {
                printContent += `
                        <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                            <strong>GCash Ref:</strong><span>${gcashReference}</span>
                        </p>
                `;
            } else if (paymentMethod === 'Credit') {
                printContent += `
                        <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                            <strong>Charge to:</strong><span> ${chargeType}</span>
                        </p>
                        ${chargeInfo}
                `;
            }

            printContent += `
                    --------------------------------------------------
                    <p style="text-align: center; font-size: 10px; margin-bottom: 5px;">This is your Sales Invoice</p>
                    <p style="text-align: center; font-size: 10px;">${invoiceNumber}</p>
                    <p style="text-align: center; font-size: 9px;">Thank you for choosing our services!</p>
                </div>
                <br><br>
            `;

            // Open print window and center it
            const screenWidth = window.innerWidth;
            const screenHeight = window.innerHeight;
            const windowWidth = 300; // Width of the print window, adjusted to match first function
            const windowHeight = 600; // Height of the print window
            const left = (screenWidth - windowWidth) / 2; // Center horizontally
            const top = (screenHeight - windowHeight) / 2; // Center vertically

            const printWindow = window.open('', '',
                `height=${windowHeight},width=${windowWidth},top=${top},left=${left}`);

            // Set the styles for the print window
            printWindow.document.write('<html><head><title>Sales Invoice</title><style>');
            printWindow.document.write('@media print {');
            printWindow.document.write(
                'body { margin: 0; padding: 0; text-align: center; font-family: monospace; }'); // changed to monospace
            printWindow.document.write(
                'div { width: 280px; margin: 0 auto; padding: 5px; font-size: 10px; text-align: left; line-height: 1.2; }' // adjusted to match first function
            );
            printWindow.document.write(
                'table { width: 100%; text-align: left; font-size: 10px; margin-bottom: 5px; border-collapse: collapse;}'); // adjusted to match first function
            printWindow.document.write('th, td { padding: 2px 0; }'); // adjusted to match first function
            printWindow.document.write('h3 { font-size: 12px; margin-bottom: 5px; }');
            printWindow.document.write('p { font-size: 10px; margin: 2px 0;}'); // adjusted to match first function
            printWindow.document.write('strong { font-weight: bold; }'); // added strong style to match first function
            printWindow.document.write('}');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.print();
        }
        // Handle View Button Click for Services History
        $(document).on('click', '.view_service_btn', function() {
            var transactionNo = $(this).data('transaction-no');

            // Clear existing modal data
            $('#serviceTransactionItemsTable tbody').empty();
            $('#serviceTransactionNo').text('');
            $('#serviceCashierName').text('');
            $('#serviceTransactionDateTime').text('');
            $('#servicePaymentMethod').text('');
            $('#serviceStatus').text('');

            // Show a loading row
            var loadingRow = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';
            $('#serviceTransactionItemsTable tbody').append(loadingRow);
            // Destroy the existing Simple-DataTable instance if any
            const datatablesSimple = document.getElementById('serviceTransactionItemsTable');
                if (datatablesSimple && datatablesSimple._dataTable) {
                    datatablesSimple._dataTable.destroy(); // Clean up the table instance
                }
            // Fetch transaction items via AJAX
            $.ajax({
                url: '{{ route('cashier.getServiceTransactionItems') }}',
                method: 'GET',
                data: {
                    transaction_no: transactionNo
                },
                success: function(response) {
                    if (response.success) {
                        // Clear the loading row
                        $('#serviceTransactionItemsTable tbody').empty();

                        // Populate transaction details
                        $('#serviceTransactionNo').text(response.transaction_no);
                        $('#serviceCashierName').text(response.cashier_name);
                        $('#serviceTransactionDateTime').text(response.transaction_date_time);
                        $('#servicePaymentMethod').text(response.payment_method);
                        $('#serviceStatus').text(response.status);

                        // Determine charge type
                        var chargeType = response.charge_type;
                        var chargeDetails = response.charge_details;

                        // Initialize invoiceData object
                        var invoiceData = {
                            transactionNo: response.transaction_no,
                            cashierName: response.cashier_name,
                            dateTime: response.transaction_date_time,
                            services: response.transaction_items.map(function(item) {
                                return {
                                    name: item.item_name,
                                    service_type: item.service_type,
                                    number_of_copies: item.number_of_copies,
                                    // **Parse number_of_hours as integer to remove decimals**
                                    number_of_hours: item.number_of_hours ? parseInt(item.number_of_hours) : null,
                                    price: parseFloat(item.price.replace('₱', '')),
                                    total: parseFloat(item.total.replace('₱', ''))
                                };
                            }),
                            discount: response.discount,
                            total: response.total,
                            paymentMethod: response.payment_method,
                            cashTendered: response.cash_tendered,
                            change: response.change,
                            gcashReference: response.gcash_reference,
                            chargeType: chargeType,
                            fullName: chargeDetails.full_name || '',
                            status: response.status,
                            // Conditionally add fields based on chargeType
                            idNumber: chargeType === 'Department' ? chargeDetails.id_number : '',
                            contactNumber: chargeType === 'Department' ? chargeDetails.contact_number : '',
                            department: chargeType === 'Department' ? chargeDetails.department : '',
                            facultyName: chargeType === 'Employee' ? chargeDetails.faculty_name : '',
                            facultyIdNumber: chargeType === 'Employee' ? chargeDetails.id_number : '',
                            facultyContactNumber: chargeType === 'Employee' ? chargeDetails.contact_number : ''
                        };

                        // Append transaction items to the table
                        response.transaction_items.forEach(function(item, index) {
                            // **Parse number_of_hours as integer to remove decimals**
                            var hours = item.number_of_hours ? parseInt(item.number_of_hours) : '-';
                            var row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + item.item_name + '</td>' +
                                '<td>' + item.service_type + '</td>' +
                                '<td>' + (item.number_of_copies || '-') + '</td>' +
                                '<td>' + hours + '</td>' + // Updated here
                                '<td>₱' + item.price + '</td>' +
                                '<td>₱' + item.total + '</td>' +
                                '</tr>';
                            $('#serviceTransactionItemsTable tbody').append(row);
                        });

                          // Reinitialize DataTable
                        const datatablesSimple = document.getElementById('serviceTransactionItemsTable');
                        const dataTableInstance = new simpleDatatables.DataTable(datatablesSimple, {
                            searchable: true, 
                            fixedHeight: false, 
                            perPage: 5, 
                            perPageSelect: [5, 10, 15, 20], 
                            labels: {
                                placeholder: "Search...",
                                perPage: "rows per page",
                                noRows: "No transaction items found",
                                info: "Showing {start} to {end} of {rows} entries",
                            }
                        });

                        // Store the invoice data in the print button using jQuery's data method
                        $('#servicePrintBtn').data('invoice-data', invoiceData);
                    } else {
                        $('#serviceTransactionItemsTable tbody').html(
                            '<tr><td colspan="7" class="text-center">No transaction items found.</td></tr>'
                        );
                    }
                },

                error: function() {
                    // Handle AJAX error
                    $('#serviceTransactionItemsTable tbody').html(
                        '<tr><td colspan="7" class="text-center text-danger">An error occurred while fetching transaction items.</td></tr>'
                    );
                }
            });
        });

        // Listen for modal close action to clear stored data
        $(document).on('click', '.btn-close, .modal-close', function() {
            // Reset modal content
            $('#serviceTransactionItemsTable tbody').empty();
            $('#serviceTransactionNo').text('');
            $('#serviceCashierName').text('');
            $('#serviceTransactionDateTime').text('');
            $('#servicePaymentMethod').text('');
            $('#serviceStatus').text('');

            // Reset print button data
            $('#servicePrintBtn').removeData('invoice-data');
        });

        // Print Receipt Button Click for Services History
        $('#servicePrintBtn').on('click', function() {
            var invoiceData = $(this).data('invoice-data');

            if (!invoiceData) {
                alert('No invoice data available to print.');
                return;
            }

            // Call the generateServicesInvoice function with the collected data
            generateServicesInvoice(invoiceData);
        });

    </script>
@endsection
