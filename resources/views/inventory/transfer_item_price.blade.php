@extends('layout.inventory')

@section('title', 'View Transfer Item Price')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.stockmanagement') }}">Stock Management</a></li>
        <li class="breadcrumb-item active">Transfer Item Price</li>
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
            List of Transfer Item Price
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Transferred Quantity</th>
                        <th>Target Item to Transfer</th>
                        <th>Transferred Base Price</th>
                        <th>Transferred Selling Price</th>
                        <th>Transferred By</th>
                        <th>Transferred At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transferItemLogs as $log)
                        <tr>
                            <td>{{ $log->item_name }}</td>
                            <td>{{ $log->transferred_quantity }}</td>
                            <td>{{ $log->transfer_to }}</td>
                            <td>₱{{ number_format($log->base_price, 2) }}</td>
                            <td>₱{{ number_format($log->selling_price, 2) }}</td>
                            <td>{{ $log->transferred_by }}</td>
                            <td>{{ $log->created_at->format('m-d-Y h:i:s a') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
