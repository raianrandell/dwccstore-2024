@extends('layout.cashier')

@section('title', 'Fines History')

@section('content')
<!-- Breadcrumb Navigation -->
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item">
        <a href="{{ route('cashier.cashier_dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active">Fines History</li>
</ol>

<!-- Card Container -->
<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Toga Fines History
    </div>
    <div class="card-body">
        <!-- Fines History Table -->
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Item Borrowed</th>
                    <th>Borrowed Date</th>
                    <th>Expected Return Date</th>
                    <th>Days Late</th>
                    <th>Condition</th>
                    <th>Late Fee</th>
                    <th>Additional Fee (Damage/Lost)</th>
                    <th>Mode of Payment</th>
                    <th>Total</th>
                    <th>Amount Tendered</th>
                    <th>Change</th>
                    <th>Gcash Ref. #</th>
                    <th>Actual Return Date</th>
                    <th>Cashier Name</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finesHistory as $fine)
                    <tr>
                        <td>{{ $fine->student_id }}</td>
                        <td>{{ $fine->student_name }}</td>
                        <td>{{ $fine->item_borrowed }}</td>
                        <td>{{ \Carbon\Carbon::parse($fine->borrowed_date)->format('m-d-Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($fine->expected_return_date)->format('m-d-Y') }}</td>
                        <td>{{ $fine->days_late }}</td>
                        <td>{{ ucfirst($fine->condition) }}</td>
                        <td>₱{{ number_format($fine->days_late * 10, 2) }}</td>
                        <td>₱{{ number_format(abs($fine->days_late * 10 - $fine->fines_amount), 2) }}</td>
                        <td>{{ $fine->payment_method }}</td>
                        <td>₱{{ number_format($fine->fines_amount, 2) }}</td>
                        <td>₱{{ number_format($fine->cash_tendered, 2) }}</td>
                        <td>₱{{ number_format($fine->change, 2) }}</td>
                        <td>{{ $fine->gcash_reference_number ?? 'N/A' }}</td>
                        <td>{{ $fine->actual_return_date ? \Carbon\Carbon::parse($fine->actual_return_date)->format('m-d-Y h:i:s') : 'N/A' }}</td>
                        <td>{{ $fine->cashier_name ?? 'Unknown'}}</td> <!-- Display the cashier's name -->
                    </tr>
                @endforeach
            </tbody>
            
        </table>
    </div>
</div>

@endsection
