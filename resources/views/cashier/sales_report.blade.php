@extends('layout.cashier')

@section('title', 'Sales Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Sales Report</li>
</ol>

<!-- Page Title and Export Options -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('cashier.sales_report.pdf', [
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'payment' => request('payment'),
            'category' => request('category'),
            'item_name' => request('item_name')
        ]) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('cashier.sales_report.excel', [
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'payment' => request('payment'),
            'category' => request('category'),
            'item_name' => request('item_name')
        ]) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('cashier.sales_report') }}">
            <div class="row">
                <!-- Start Date -->
                <div class="col-md-2">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" id="startDate" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                
                <!-- End Date -->
                <div class="col-md-2">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" id="endDate" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <!-- Item Name (New Filter) -->
                <div class="col-md-2">
                    <label for="item_name" class="form-label">Filter by Item Name</label>
                    <select id="item_name" name="item_name" class="form-select w-40">
                        <option value="">All Items</option>
                        @foreach ($items as $id => $item_name)
                            <option value="{{ $item_name }}" {{ request('item_name') == $item_name ? 'selected' : '' }}>
                                {{ $item_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Category -->
                <div class="col-md-2">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $id => $category_name)
                            <option value="{{ $id }}" {{ request('category') == $id ? 'selected' : '' }}>
                                {{ $category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Payment Method -->
                <div class="col-md-2">
                    <label for="payment" class="form-label">Filter by Payment Method</label>
                    <select id="payment" name="payment" class="form-select">
                        <option value="">All Methods</option>
                        <option value="Cash" {{ request('payment') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ request('payment') == 'GCASH' ? 'selected' : '' }}>GCash</option>
                        <option value="Credit" {{ request('payment') == 'Credit' ? 'selected' : '' }}>Credit</option>              
                    </select>
                </div>
                
                <!-- Apply Filters Button -->
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                
                <!-- Reset Filters Button -->
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('cashier.sales_report') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Sales Table -->
<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Sales Report
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Transaction Number</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Payment Method</th>
                    <th>Cashier Name</th>
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
                            <td>₱{{ number_format($item->price, 2) }}</td>
                            <td>₱{{ number_format($item->total, 2) }}</td>
                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                            <td>{{ $transaction->user->full_name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Total Sales -->
<div class="text-end mt-3">
    <h5><strong>Total Sales:</strong> ₱{{ number_format($totalSales, 2) }}</h5>
</div>
@endsection
