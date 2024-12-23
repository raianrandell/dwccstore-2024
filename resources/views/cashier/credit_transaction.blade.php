@extends('layout.cashier')

@section('title', 'Credit Transaction')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('cashier.cashier_dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Credit Transaction</a></li>
    </ol>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Credit List
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Transaction Number</th>
                        <th>Charge To</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($creditTransactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('m-d-Y h:i:s a') }}</td>
                            <td>{{ $transaction->transaction_no }}</td>
                            <td>
                                @if ($transaction->charge_type == 'Department')
                                    Department
                                @elseif($transaction->charge_type == 'Faculty')
                                    Faculty
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if ($transaction->status == 'Not Paid')
                                    <span class="badge bg-danger">{{ $transaction->status }}</span>
                                    @else 
                                        <span class="badge bg-success">{{ $transaction->status }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="btn btn-primary btn-sm rounded-circle view_btn"
                                    data-bs-toggle="modal" data-bs-target="#transactionModal"
                                    onclick="showTransactionDetails({{ $transaction }})" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="department-info" style="display: none;">
                        <h6 class="mb-3"><strong>Charge To: Department</strong></h6>
                        <p>Full Name: <span id="department-full-name"></span></p>
                        <p>ID Number: <span id="department-id-number"></span></p>
                        <p>Contact Number: <span id="department-contact-number"></span></p>
                        <p>Department: <span id="department-name"></span></p>
                    </div>

                    <div id="faculty-info" style="display: none;">
                        <h6 class="mb-3"><strong>Charge To: Faculty</strong></h6>
                        <p>Faculty Name: <span id="faculty-name"></span></p>
                        <p>ID Number: <span id="faculty-id-number"></span></p>
                        <p>Contact Number: <span id="faculty-contact-number"></span></p>
                    </div>

                    <!-- Display Status -->
                    <p class="mb-3">Status: <span id="transaction-status"></span></p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTransactionDetails(transaction) {
            // Hide both sections initially
            document.getElementById('department-info').style.display = 'none';
            document.getElementById('faculty-info').style.display = 'none';

            // Display the appropriate section based on charge_type
            if (transaction.charge_type === 'Department') {
                document.getElementById('department-info').style.display = 'block';
                document.getElementById('department-full-name').textContent = transaction.full_name;
                document.getElementById('department-id-number').textContent = transaction.id_number;
                document.getElementById('department-contact-number').textContent = transaction.contact_number;
                document.getElementById('department-name').textContent = transaction.department;
            } else if (transaction.charge_type === 'Faculty') {
                document.getElementById('faculty-info').style.display = 'block';
                document.getElementById('faculty-name').textContent = transaction.faculty_name;
                document.getElementById('faculty-id-number').textContent = transaction.id_number;
                document.getElementById('faculty-contact-number').textContent = transaction.contact_number;
            }

           // Display status and change text color based on the status
    const statusElement = document.getElementById('transaction-status');
    statusElement.textContent = transaction.status;

    // Reset text color classes
    statusElement.classList.remove('text-danger', 'text-success', 'text-secondary');

    // Apply color based on status
    if (transaction.status === 'Not Paid') {
        statusElement.classList.add('text-danger');
    } else 
        statusElement.classList.add('text-success');
    } 
    </script>

@endsection
