@extends('layout.cashier')

@section('title', 'Fines History')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Fines History</li>
</ol>

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Toga Fines History
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Student Name</th>
                    <th>Item Borrowed</th>
                    <th>Days Late</th>
                    <th>Fines Amount</th>
                    <th>Payment Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finesHistory as $fine)
                    <tr>
                        <td>{{ $fine->student_id }}</td>
                        <td>{{ $fine->student_name }}</td>
                        <td>{{ $fine->item_borrowed }}</td>
                        <td>{{ $fine->days_late }}</td>
                        <td>{{ number_format($fine->fines_amount, 2) }}</td>
                        <td>Paid via: <strong>{{ $fine->payment_method }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection