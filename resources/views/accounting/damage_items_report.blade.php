@extends('layout.accounting')

@section('title', 'Damage Items Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Damage Items</li>
</ol>

<!-- Page Title and Export Options -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('inventory.damage_item_report.pdf', [
            'start_date' => request('start_date'),
            'end_date' => request('end_date'),
            'item_name' => request('item_name'),
            'category' => request('category')
        ]) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('inventory.damage_item_report.excel', [
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
        <form method="GET" action="{{ route('accounting.damage_items') }}">
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
                        @foreach ($uniqueItemNames as $itemName)
                            <option value="{{ $itemName }}" {{ request('item_name') == $itemName ? 'selected' : '' }}>
                                {{ $itemName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($uniqueCategories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
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
                    <a href="{{ route('accounting.damage_items') }}" class="btn btn-secondary w-100">
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
        Damage Item Report
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Date Encoded</th>
                    <th>Damage Description</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($damageItems as $damageItem)
                <tr>
                    <td>{{ $damageItem->item_name }}</td>
                    <td>{{ $damageItem->category->category_name ?? 'No Category' }}</td>
                    <td>{{ $damageItem->quantity }}</td>
                    <td>{{ $damageItem->created_at->format('m-d-Y') }}</td>
                    <td>{{ $damageItem->damage_description }}</td>
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
