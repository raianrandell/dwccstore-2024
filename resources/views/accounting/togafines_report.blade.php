@extends('layout.accounting')

@section('title', 'Toga Fines Report')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('accounting.chargeTransaction') }}">Home</a></li>
    <li class="breadcrumb-item active">Toga Fines Report</li>
</ol>

<!-- Page Title and Export Options -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
       <!-- Export PDF with All Filters -->
        <a href="{{ route('accounting.toga_fines_report_pdf', request()->query()) }}" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>

        <!-- Export Excel with All Filters -->
        <a href="{{ route('accounting.toga_fines_report_excel', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>

    </div>
</div>
<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('accounting.toga_fines_report') }}">
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
                    <label for="itemName" class="form-label">Filter by Item Borrowed</label>
                    <select id="itemName" name="item_name" class="form-select">
                        <option value="">All</option>
                        @foreach ($items as $key => $value)
                            <option value="{{ $key }}" {{ request('item_name') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="condition" class="form-label">Filter by Condition</label>
                    <select id="condition" name="condition" class="form-select">
                        <option value="">All</option>
                        <option value="Good" {{ request('condition') == 'Good' ? 'selected' : '' }}>Good</option>
                        <option value="Damaged" {{ request('condition') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="Lost" {{ request('condition') == 'Lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="payment" class="form-label">Filter by Mode of Payment</label>
                    <select id="payment" name="payment" class="form-select">
                        <option value="">All Methods</option>
                        <option value="Cash" {{ request('payment') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Gcash" {{ request('payment') == 'Gcash' ? 'selected' : '' }}>GCash</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filters
                    </button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('accounting.toga_fines_report') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
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
        Toga Fines Report
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
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
                    <th>Actual Return Date</th>
                    <th>Cashier</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($finesReport as $fines)
                    <tr>
                        <td>{{ $fines->student_id }}</td>
                        <td>{{ $fines->student_name }}</td>
                        <td>{{ $fines->item_borrowed }}</td>
                        <td>{{ \Carbon\Carbon::parse($fines->borrowed_date)->format('m-d-Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($fines->expected_return_date)->format('m-d-Y') }}</td>
                        <td>{{ $fines->days_late }}</td>
                        <td>{{ ucfirst($fines->condition) }}</td>
                        <td>₱{{ number_format($fines->days_late * 10, 2) }}</td>
                        <td>₱{{ number_format(abs($fines->days_late * 10 - $fines->fines_amount), 2) }}</td>
                        <td>{{ $fines->payment_method }}</td>
                        <td>₱{{ number_format($fines->fines_amount, 2) }}</td>
                        <td>{{ $fines->actual_return_date ? \Carbon\Carbon::parse($fines->actual_return_date)->format('m-d-Y') : 'N/A' }}</td>
                        <td>{{ $fines->cashier_name ?? 'Unknown' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="text-end mt-3">
    <h5><strong>Total Fines:</strong> ₱{{ number_format($totalFines, 2) }}</h5>
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