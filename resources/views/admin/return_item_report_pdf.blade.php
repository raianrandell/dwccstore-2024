<!DOCTYPE html>
<html>
<head>
    <title>Returned Item Report</title>
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
             font-size: 14px;
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
        h3 {
            margin: 10px 0;
        }
        p {
            margin: 0 0 5px 0;
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
    <h3 style="text-align: center">Returned Item Report</h3>

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
    </div>

    <!-- Table Section -->
    <table>
        <thead>
            <tr>
                <th>Transaction Number</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity Returned</th>
                <th>Reason</th>
                <th>Type</th>
                <th>Replacement Item</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($returnedItems as $item)
                <tr>
                    <td>{{ $item->transaction_no }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->item->category->category_name ?? 'N/A' }}</td>
                    <td>{{ $item->return_quantity }}</td>
                    <td>{{ $item->reason }}</td>
                    <td>Replacement</td>
                    <td>{{ $item->replacement_item ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('m-d-Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer Section -->
    <div class="footer" style="position: absolute; bottom: 0;">
        <p>Generated by: {{ $userFullName }}</p>
        <p>Generation Date: {{ \Carbon\Carbon::now()->format('m-d-Y h:i:s a') }}</p>
    </div>

</body>
</html>
