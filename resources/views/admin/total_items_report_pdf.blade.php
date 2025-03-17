<!DOCTYPE html>

<html>
<head>
    <title>DWCC - College Bookstore Inventory</title>
    <link rel="icon" href="{{ asset('images/dwcclogo.ico') }}" type="image/x-icon">
    <!-- Include Bootstrap CSS for grid system -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }
        .header img {
            position: absolute;
            left: 10px;
            top: 20px;
            height: 100px; /* Adjust the height of the logo as needed */
        }
        .content {
            padding-bottom: 50px; /* Prevents content from overlapping footer */
        }
        .footer {
            position: fixed;
            bottom: 0;
            right: 0;
            margin: 10px;
            text-align: right;
            font-size: 0.8em;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd; 
            padding: 10px;
            text-align: left;
            font-size: 0.9em;
        }
        th {
            background-color: #20c997;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .filter-info p {
            margin: 5px 0; /* Adjust spacing for readability */
            font-size: 0.9em; /* Adjust font size as needed */
        }
        /* h3 {
            margin: 10px 0;
        } */
        p {
            margin: 0 0 5px 0;
        }
        .category-header {
            margin-top: 30px;
            font-size: 1.2em;
            color: #333;
        }
        .signatures {
            margin-top: 40px;
        }
        .signature-column {
            margin-bottom: 20px;
        }
        .signature-column p {
            margin: 0; /* Adjust spacing for labels */
            text-align: left; /* Align labels to the left */
        }
        .signature-line {
            border-bottom: 1px solid #000;
            padding-top: 37px;
            margin-bottom: 5px;
            width: 40%;
        }
        .signature-name {
            font-weight: bold;
            margin-bottom: 0;
            text-transform: uppercase;
        }
        .signature-title {
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<!-- Header Section -->
<div class="header">
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/dwcclogo.png'))) }}" alt="College Logo" class="logo">
    <h2>Divine Word College of Calapan</h2>
    <p>Gov. Infantado St. Calapan City, Oriental Mindoro, Philippines</p>
    <h3>DWCC STORE: Sales and Inventory</h3>
</div>
<br>
<h3 style="text-align: center">COLLEGE BOOKSTORE INVENTORY</h3>
<h4 style="text-align: center">As of {{ \Carbon\Carbon::now()->format('F d, Y') }}</h4>

<!-- Display the selected filters -->
<div class="filter-info">
    <p><strong>Date Range:</strong>
        @if(isset($startDate) && $startDate)
            {{ is_string($startDate) ? \Carbon\Carbon::parse($startDate)->format('m-d-Y') : $startDate->format('m-d-Y') }}
        @else
            All Dates
        @endif
        -
        @if(isset($endDate) && $endDate)
            {{ is_string($endDate) ? \Carbon\Carbon::parse($endDate)->format('m-d-Y') : $endDate->format('m-d-Y') }}
        @else
            All Dates
        @endif
    </p>
    <p><strong>Item Name:</strong> {{ $itemName ?? 'All Items' }}</p>
    <p><strong>Category:</strong> {{ $categoryName ?? 'All Categories' }}</p>
    <p><strong>Unit of Measurement:</strong> {{ $unit ?? 'All Units' }}</p> <!-- Added this line -->
</div>


<div class="table-container">
    @foreach ($items as $category => $categoryItems)
        <!-- Conditionally display the category header -->
        @if (($categoryName ?? 'All Categories') == 'All Categories')
            <div class="category-header"><strong>Category:</strong> {{ $category }}</div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Base Price</th>
                    <th>Selling Price</th>
                    <th>Total Base Price</th>
                    <th>Total Selling Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categoryItems as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->qtyInStock }}</td>
                        <td>{{ $item->unit_of_measurement }}</td>
                        <td>{{ number_format($item->base_price, 2) }}</td>
                        <td>{{ number_format($item->selling_price, 2) }}</td>
                        <td>{{ number_format($item->qtyInStock * $item->base_price, 2) }}</td>
                        <td>{{ number_format($item->qtyInStock * $item->selling_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>

<div class="signatures">
    <br><br><br><br>
    <div class="row">
        <div class="col-md-6 signature-column">
            <p style="margin-top: 50px">Prepared by:</p>
            <div class="signature-line"></div>
            <p class="signature-name">&nbsp;</p>
            <p class="signature-title">Cashier - College Bookstore</p>
        </div>
        <div class="col-md-6 signature-column">
            <p>Checked by:</p>
            <div class="signature-line"></div>
            <p class="signature-name">MARAFE O. OCAMPO</p>
            <p class="signature-title">Purchasing Officer</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 signature-column">
            <p>Verified by:</p>
            <div class="signature-line"></div>
            <p class="signature-name">MS. GRACE LUZON</p>
            <p class="signature-title">Head, Acctg Office</p>
        </div>
        <div class="col-md-6 signature-column">
            <p>Approved by:</p>
            <div class="signature-line"></div>
            <p class="signature-name">FR. JEROME A. ORMITA, <span style="font-weight: normal; font-style: italic;">SVD</span></p>
            <p class="signature-title">VP for Finance</p>
        </div>
    </div>
</div>

<div class="footer" style="position: absolute; bottom: 0;">
        <div>Generated by:  {{ $userFullName }}</div>
        <div>Generation Date: {{ \Carbon\Carbon::now()->format('m-d-Y h:i:s a') }}</div>
</div>

<!-- Include Bootstrap JS (optional, but might be needed for other Bootstrap features) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>