<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
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
            position: absolute; /* Allows the logo to stay left of the centered text */
            left: 10px;
            top: 20px;
            height: 100px; /* Adjust the height of the logo as needed */
        }
        .header-content {
            margin-left: 100px; /* Adds space to prevent overlap with the logo */
        }
        .content {
            margin: 20px;
            padding-bottom: 50px; /* Prevents content from overlapping footer */
        }
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 0.8em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Ensures consistent column widths */
            word-wrap: break-word; /* Prevents overflow by wrapping text */
            font-size: 12px; /* Small font size for compact layout */
        }
        th, td {
            border: 1px solid #ddd; 
            padding: 5px; /* Smaller padding for compact rows */
            text-align: left;
        }
        th {
            background-color: #20c997;
            color: #fff;
            padding: 5px;
        }
        .total {
            text-align: right;
            margin-top: 10px;
            font-size: 0.9em;
            font-weight: bold;
        }
        @page {
            margin: 20px;
        }
        thead {
            display: table-header-group; /* Ensures headers repeat on each new page */
        }
        tfoot {
            display: table-footer-group; /* Ensures footers repeat on each new page */
        }
        tr {
            page-break-inside: avoid; /* Prevents row splitting across pages */
        }

        .filter-info p {
            margin: 5px 0 0 20px; /* Adjust spacing for readability */
            font-size: 0.9em; /* Adjust font size as needed */
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <img src="{{ public_path('images/dwcclogo.png') }}" alt="College Logo" class="logo">
        <h2>Divine Word College of Calapan</h2>
        <p> Gov. Infantado St. Calapan City, Oriental Mindoro, Philippines</p>
        <h3>DWCC STORE: Sales and Inventory</h3>
    </div>

    <!-- Report Title -->
    <h3 style="text-align: center">Sales Report</h3>
    <div class="filter-info">
    <p>
        <strong>Date Range:</strong>
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
    <p><strong>Item Name:</strong> {{ $itemNameLabel ?? 'All Items' }}</p>
    <p><strong>Category:</strong> {{ $categoryName ?? 'All Categories' }}</p>
    
    <p><strong>Payment Method:</strong> {{ $paymentMethod ?? 'All Methods' }}</p>
</div>
   


    <!-- Table Section -->
    <div class="content">
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Date/Time</th>
                    <th style="width: 10%;">Trans. #</th>
                    <th style="width: 20%;">Item Name</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 10%;">Quantity</th>
                    <th style="width: 10%;">Price</th>
                    <th style="width: 10%;">Total</th>
                    <th style="width: 10%;">Payment Method</th>
                    <th style="width: 15%;">Cashier Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    @foreach ($transaction->items as $item)
                        <tr>
                            <td>{{ $transaction->created_at->format('m-d-Y h:i:s a') }}</td>
                            <td>{{ $transaction->transaction_no }}</td>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->item->category->category_name ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td style="text-align: right;">{{ number_format($item->price, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($item->total, 2) }}</td>
                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                            <td>{{ $transaction->user->full_name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Total Sales -->
        <div class="total">
            <p>Total Sales:P{{ number_format($totalSales, 2) }}</p>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer" style="position: absolute; bottom: 0;">
        <p>Generated by: {{ $userFullName }}</p>
        <p>Generation Date: {{ \Carbon\Carbon::now()->format('m-d-Y h:i:s a') }}</p>
    </div>

</body>
</html>
