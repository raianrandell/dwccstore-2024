@extends('layout.inventory')

@section('title', 'Expired Items Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Expired Items</li>
</ol>

<!-- Page Title and Export Options -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('inventory.expired_item_report.pdf', [
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'item_name' => request('item_name'),
            'category' => request('category')
        ]) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('inventory.expired_item_report.excel', [
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
        <form method="GET" action="{{ route('inventory.expired_item_report') }}">
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

                <!-- Filter by Item Name -->
                <div class="col-md-2">
                    <label for="itemName" class="form-label">Filter by Item Name</label>
                    <select id="itemName" name="item_name" class="form-select">
                        <option value="">All Items</option>
                        @foreach ($itemNames as $itemName)
                            <option value="{{ $itemName }}" {{ request('item_name') == $itemName ? 'selected' : '' }}>{{ $itemName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter by Category -->
                <div class="col-md-2">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>

                <!-- Reset Button -->
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('inventory.expired_item_report') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Damage Items Report Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Expired Item Report
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>Barcode Number</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiration Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expiredItemsFromDB as $expiredItem)
                    <tr>
                        <td>{{ $expiredItem->barcode }}</td>
                        <td>{{ $expiredItem->item_name }}</td>
                        <td>{{ $expiredItem->category }}</td>
                        <td>{{ $expiredItem->quantity }}</td>
                        <td>{{ \Carbon\Carbon::parse($expiredItem->expiration_date)->format('m-d-Y') }}</td>
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