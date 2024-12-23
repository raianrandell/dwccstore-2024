@extends('layout.inventory')

@section('title', 'Low Stock Item Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Low Stock Items</li>
</ol>

<!-- Page Title and Export Options -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('inventory.low_stock_item_report.pdf', [
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'item_name' => request('item_name'),
            'category' => request('category')
        ]) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('inventory.low_stock_item_report.excel', [
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'item_name' => request('item_name'),
            'category' => request('category')
        ]) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('inventory.low_stock_item_report') }}">
            <div class="row">
                <div class="col-md-2">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" id="startDate" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" id="endDate" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="itemName" class="form-label">Filter by Item Name</label>
                    <select id="itemName" name="item_name" class="form-select">
                        <option value="">All Items</option>
                        @foreach($itemNames as $item)
                            <option value="{{ $item->item_name }}" {{ request('item_name') == $item->item_name ? 'selected' : '' }}>
                                {{ $item->item_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('inventory.low_stock_item_report') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Low Stock Items Report Table -->
<div class="card mb-4">
    <div class="card-header">Low Stock Item List Report</div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>Barcode Number</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Stock Available</th>
                    <th>Date Encoded</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockItems as $item)
                    <tr>
                        <td>{{ $item->barcode }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->category->category_name }}</td>
                        <td>{{ $item->qtyInStock }}</td>
                        <td>{{ $item->created_at->format('m-d-Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Include jQuery DataTables for Search and Sort Features -->
<script>
    $(document).ready(function() {
        $('#datatablesSimple').DataTable({
            // Customize the DataTable as needed
        });
    });
</script>
@endsection
