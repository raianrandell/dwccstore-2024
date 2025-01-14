@extends('layout.inventory')

@section('title', 'Total Items Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Total Items</li>
</ol>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('inventory.total_item_report.pdf', request()->query()) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('inventory.total_item_report.excel', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('inventory.total_item_report') }}">
            <div class="row align-items-end">
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
                        @foreach ($itemsForDropdown->sortBy('item_name')->sortBy('category_name') as $item)
                        <option value="{{ $item->item_name }}" {{ request('item_name') == $item->item_name ? 'selected' : '' }}>
                            {{ $item->item_name }}
                        </option>
                    @endforeach
                    </select>
                </div>

                <!-- Filter by Category -->
                <div class="col-md-2">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="unit" class="form-label">Filter by Unit</label>
                    <select id="unit" name="unit" class="form-select">
                        <option value="">All Units</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>

                <!-- Reset Button -->
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('inventory.total_item_report') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Total Items Report
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Base Price</th>
                    <th>Selling Price</th>
                    <th>Total Base Price</th>
                    <th>Total Selling Price</th>
                    <th>Date Encoded</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['item_name'] }}</td>
                    <td>{{ $item['category_name'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>₱{{ number_format($item['base_price'], 2) }}</td>
                    <td>₱{{ number_format($item['selling_price'], 2) }}</td>
                    <td>₱{{ number_format($item['quantity'] * $item['base_price'], 2) }}</td> <!-- Calculate total_base_price -->
                    <td>₱{{ number_format($item['quantity'] * $item['selling_price'], 2) }}</td> <!-- Calculate total_selling_price -->
                    <td>{{ $item['created_at']->format('m-d-Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection