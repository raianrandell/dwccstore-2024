@extends('layout.inventory')

@section('title', 'View Price Update')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.stockmanagement') }}">Stock Management</a></li>
        <li class="breadcrumb-item active">Item Price Update</li>
    </ol>

    <!-- Success and Error Messages -->
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fa-lg"></i>
            <div>
                {{ Session::get('success') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (Session::has('danger'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
            <div>
                {{ Session::get('danger') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            List of Price Updates
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <!-- No Results Message -->
            <div id="noResultsMessage" class="alert alert-warning text-center mt-3" style="display: none;">
                No items found for the selected filters.
            </div>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th>Old Base Price</th>
                        <th>New Base Price</th>
                        <th>Old Selling Price</th>
                        <th>New Selling Price</th>
                        <th>Old Expiration Date</th>
                        <th>New Expiration Date</th>
                        <th>Old Quantity in Stock</th> <!-- Added column -->
                        <th>New Quantity in Stock</th> <!-- Added column -->
                        <th>Old Barcode</th> <!-- Added column -->
                        <th>New Barcode</th> <!-- Added column -->
                        <th>Updated By</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($priceUpdates as $index => $update)
                        <tr>
                            <td>{{ $priceUpdates->firstItem() + $index }}</td>
                            <td>{{ $update->item_name ?? 'N/A' }}</td>
                            <td>₱{{ number_format($update->old_base_price, 2) }}</td>
                            <td>₱{{ number_format($update->new_base_price, 2) }}</td>
                            <td>₱{{ number_format($update->old_selling_price, 2) }}</td>
                            <td>₱{{ number_format($update->new_selling_price, 2) }}</td>
                            <td>{{ $update->old_expiration_date ? $update->old_expiration_date->format('m-d-Y') : 'N/A' }}</td>
                            <td>{{ $update->new_expiration_date ? $update->new_expiration_date->format('m-d-Y') : 'N/A' }}</td>
                            <td>{{ $update->old_qty_in_stock ?? 'N/A' }}</td> <!-- Display old quantity in stock -->
                            <td>{{ $update->new_qty_in_stock ?? 'N/A' }}</td> <!-- Display new quantity in stock -->
                            <td>{{ $update->old_barcode ?? 'N/A' }}</td> <!-- Display old barcode -->
                            <td>{{ $update->new_barcode ?? 'N/A' }}</td> <!-- Display new barcode -->
                            <td>{{ $update->user->full_name }}</td>
                            <td>{{ $update->created_at->format('m-d-Y h:i:s a') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $priceUpdates->links() }}
            </div>
        </div>
    </div>
@endsection
