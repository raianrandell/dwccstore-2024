@extends('layout.cashier')

@section('title', 'Void Records')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Void Records</li>
</ol>
<!-- Success and Error Messages -->
@if (Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        <div>{{ Session::get('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (Session::has('danger'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
        <div>{{ Session::get('danger') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        List of Void Records
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Voided By</th>
                    <th>Voided At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($voidRecords as $voidRecord)
                    <tr>
                        <td>{{ $loop->iteration }}</td> <!-- Display the current iteration -->  
                        <td>{{ $voidRecord->item_name }}</td>
                        <td>₱{{ number_format($voidRecord->price, 2) }}</td> <!-- Format price -->
                        <td>{{ $voidRecord->voided_by }}</td> <!-- Full name of the user who voided -->
                        <td>{{ $voidRecord->voided_at->format('m-d-Y h:i:s a') }}</td> <!-- Format date -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
