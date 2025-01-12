@extends('layout.admin')

@section('title', 'Returned Items Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Returned Items Report</li>
</ol>

<!-- Page Title and Export Options -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
       <!-- Export PDF with All Filters -->
        <a href="{{ route('admin.returned_item_report_pdf', request()->query()) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>

        <!-- Export Excel with All Filters -->
        <a href="{{ route('admin.returned_item_report_excel', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>

    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.return_item_report') }}">
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
                        @foreach($items as $item)
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
                            @foreach($categories as $category)
                                <option value="{{ $category->category_name }}" {{ request('category') == $category->category_name ? 'selected' : '' }}>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <!-- Filter Button -->
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <!-- Reset Button -->
                    <a href="{{ route('admin.return_item_report') }}" class="btn btn-secondary w-100">
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
        Returned Items Report
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
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