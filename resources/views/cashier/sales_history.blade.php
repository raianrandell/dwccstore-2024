@extends('layout.cashier')

@section('title', 'Sales History')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Sales History</li>
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
        List of Sales History
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
                            <!-- Example action buttons -->
                            <a href="#" class="btn btn-primary btn-sm rounded-circle view_btn" title="View" data-transaction-no="{{ $transaction->transaction_no }}" data-bs-toggle="modal" data-bs-target="#transactionModal">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Item Purchased Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between">
                    <p><strong>Transaction No:</strong> <span id="transactionNo"></span></p>
                    <p><strong>Cashier: </strong><span id="cashierName"></span></p>
                </div>
                <div class="d-flex justify-content-between">
                    <p><strong>Date/Time:</strong> <span id="transactionDateTime"></span></p>
                    <p><strong>Payment Method:</strong> <span id="paymentMethod"></span></p>
                </div>
              

                <!-- Transaction Items Table -->
                <table id="transactionItemsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Sub-Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Transaction items will be injected here via AJAX -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <!-- Print Button -->
                <button type="button" class="btn btn-outline-primary" id="printBtn">
                    <i class="fas fa-print"></i> Print Receipt
                </button>                
            </div>
        </div>
    </div>
</div>


<script>
 // Handle View Button Click
$(document).on('click', '.view_btn', function () {
    var transactionNo = $(this).data('transaction-no');
    
    // Clear existing modal data
    $('#transactionItemsTable tbody').empty();
    $('#transactionNo').text('');
    $('#cashierName').text('');
    $('#transactionDateTime').text('');

    // Show a loading row
    var loadingRow = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    $('#transactionItemsTable tbody').append(loadingRow);

    // Destroy the existing Simple-DataTable instance if any
    const datatablesSimple = document.getElementById('transactionItemsTable');
    if (datatablesSimple && datatablesSimple._dataTable) {
        datatablesSimple._dataTable.destroy(); // Clean up the table instance
    }

    // Fetch transaction items via AJAX
    $.ajax({
        url: '{{ route('cashier.getTransactionItems') }}',
        method: 'GET',
        data: { transaction_no: transactionNo },
        success: function (response) {
    if (response.success) {
        // Clear the loading row
        $('#transactionItemsTable tbody').empty();

        // Populate transaction details
        $('#transactionNo').text(transactionNo);
        $('#cashierName').text(response.cashier_name);
        $('#transactionDateTime').text(response.transaction_date_time);
        $('#paymentMethod').text(response.payment_method);
        $('#transactionStatus').text(response.status);

        // Append transaction items
        response.transaction_items.forEach(function (item, index) {
            var price = parseFloat(item.price.replace(/,/g, '')); // Remove commas
            var total = parseFloat(item.total.replace(/,/g, '')); // Remove commas

            var row = '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + item.item_name + '</td>' +
                '<td>' + item.quantity + '</td>' +
                '<td>₱' + price.toFixed(2) + '</td>' +
                '<td>₱' + total.toFixed(2) + '</td>' +
                '</tr>';
            $('#transactionItemsTable tbody').append(row);
        });

        // Reinitialize DataTable
        const datatablesSimple = document.getElementById('transactionItemsTable');
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

        // Store summary data for the print view
        $('#printBtn').data('discount', response.discount);
        $('#printBtn').data('total', response.total);
        $('#printBtn').data('total_quantity', response.total_quantity);
        $('#printBtn').data('payment_method', response.payment_method);
        $('#printBtn').data('cash_tendered', response.cash_tendered); // Store cash tendered
        $('#printBtn').data('change', response.change); // Store change
        $('#printBtn').data('gcash_reference', response.gcash_reference);
        $('#printBtn').data('charge_type', response.charge_type); 
        $('#printBtn').data('charge_details', response.charge_details); // Store charge details for printing
        $('#printBtn').data('status', response.status);

    } else {
        $('#transactionItemsTable tbody').html('<tr><td colspan="5" class="text-center">No transaction items found.</td></tr>');
    }
},
        error: function () {
            // Handle AJAX error
            $('#transactionItemsTable tbody').html('<tr><td colspan="5" class="text-center text-danger">An error occurred while fetching transaction items.</td></tr>');
        }
    });
});

// Listen for modal close action
$(document).on('click', '.close-modal, .modal-close', function () {
    // Reset modal content
    $('#transactionItemsTable tbody').empty();
    $('#transactionNo').text('');
    $('#cashierName').text('');
    $('#transactionDateTime').text('');

    // Reset print button data
    $('#printBtn').removeData('discount')
                  .removeData('total')
                  .removeData('total_quantity')
                  .removeData('payment_method')
                  .removeData('cash_tendered')
                  .removeData('change')
                  .removeData('gcash_reference')
                  .removeData('charge_type')
                  .removeData('charge_details');
});



$('#printBtn').on('click', function () {
    var discount = $(this).data('discount');
    var total = $(this).data('total');
    var paymentMethod = $(this).data('payment_method');
    var cashTendered = $(this).data('cash_tendered'); // Added cash_tendered
    var change = $(this).data('change'); // Added change
    var totalQuantity = 0; // Variable to store total quantity
    var gcashReference = $(this).data('gcash_reference'); // Get the GCash reference number
    var chargeType = $(this).data('charge_type'); // Get the charge type for Credit payment
    var chargeDetails = $(this).data('charge_details');
    var status = $(this).data('status');
    var transactionNo = $('#transactionNo').text();
    var cashierName = $('#cashierName').text();
    var dateTime = $('#transactionDateTime').text();


    // Create a receipt-style print content
      var printContent = `
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

    $('#transactionItemsTable tbody tr').each(function () {
        var itemName = $(this).find('td:nth-child(2)').text();
        var quantity = parseInt($(this).find('td:nth-child(3)').text()); // Get the quantity as integer
        var price = parseFloat($(this).find('td:nth-child(4)').text().replace('₱', '')).toFixed(2); // Parse price
        var subTotal = parseFloat($(this).find('td:nth-child(5)').text().replace('₱', '')).toFixed(2); //Parse Subtotal

        // Add quantity to the total quantity
        totalQuantity += quantity;

          printContent += `
            <tr>
                <td style="padding: 2px 0;">${itemName}</td>
                <td style="text-align: right; padding: 2px 0;">${quantity}</td>
                <td style="text-align: right; padding: 2px 0;">${price}</td>
                <td style="text-align: right; padding: 2px 0;">${subTotal}</td>
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
    } else if (paymentMethod === 'GCash') {
        printContent += `
            <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <strong>GCash Ref:</strong><span>${gcashReference}</span>
            </p>
        `;
    } else if (paymentMethod === 'Credit') {
         printContent += `
            <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                <strong>Charge to:</strong><span>${chargeType}</span>
            </p>
        `;

        if (chargeDetails) {
            if (chargeDetails.full_name) {
                printContent += `
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>Full Name:</strong><span>${chargeDetails.full_name}</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>ID Number:</strong><span>${chargeDetails.id_number}</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>Contact No:</strong><span>${chargeDetails.contact_number}</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>Department:</strong><span>${chargeDetails.department}</span>
                    </p>
                `;
            } else if (chargeDetails.faculty_name) {
                 printContent += `
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>Employee Name:</strong><span>${chargeDetails.faculty_name}</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>ID Number:</strong><span>${chargeDetails.facultyIdNumber}</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 2px 0;">
                        <strong>Contact No:</strong><span>${chargeDetails.facultyContactNumber}</span>
                    </p>`;
            }
        }
    }

    printContent += `
             --------------------------------------------------
            <p style="text-align: center; font-size: 10px; margin-bottom: 5px;">This is your Sales Invoice</p>
            <p style="text-align: center; font-size: 10px;">${generateSalesInvoiceNumber()}</p>
            <p style="text-align: center; font-size: 9px;">Thank you for shopping with us!</p>
        </div>
    `;



    function generateSalesInvoiceNumber() {
        const currentDate = new Date();
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const randomNum = String(Math.floor(Math.random() * 900000) + 100000);
        return `SI-${year}${month}-${randomNum}`;
    }


    // Get the screen width and height to center the window
    var screenWidth = window.innerWidth;
    var screenHeight = window.innerHeight;
    var windowWidth = 300;  // Width of the print window
    var windowHeight = 600; // Height of the print window
    var left = (screenWidth - windowWidth) / 2;  // Center horizontally
    var top = (screenHeight - windowHeight) / 2;  // Center vertically

    // Open print window and center it
    var printWindow = window.open('', '', `height=${windowHeight},width=${windowWidth},top=${top},left=${left}`);

    // Set the styles for the print window
   printWindow.document.write('<html><head><title>Sales Invoice</title><style>');
    printWindow.document.write('@media print {');
    printWindow.document.write('body { margin: 0; padding: 0; text-align: center; font-family: monospace; }');
    printWindow.document.write('div { width: 280px; margin: 0 auto; padding: 5px; font-size: 10px; text-align: left; line-height: 1.2; }');
    printWindow.document.write('table { width: 100%; text-align: left; font-size: 10px; margin-bottom: 5px; border-collapse: collapse; }');
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
});

    </script>
    


@endsection
