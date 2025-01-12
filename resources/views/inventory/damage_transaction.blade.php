@extends('layout.inventory')

@section('title', 'Damage Transaction')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Damage Transaction</li>
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

    <!-- Add button next to the search -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <!-- You can add search or other controls here -->
        </div>
        <div>
            <!-- Add New Damage Transaction Button -->
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                data-bs-target="#addDamageTransactionModal">
                <i class="fas fa-plus me-1"></i> Add Damage Item
            </button>
        </div>
    </div>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            List of Damage Items
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Damage Description</th>
                        <th>Date/Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($damageTransactions as $transaction)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $transaction->item->category->category_name }}</td>
                            <td>{{ $transaction->item->item_name }}</td>
                            <td>{{ $transaction->quantity }}</td>
                            <td>{{ $transaction->damage_description }}</td>
                            <td>{{ $transaction->created_at->format('m-d-Y h:i:s a') }}</td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-circle" title="Update"
                                    data-bs-toggle="modal" data-bs-target="#editQuantityModal{{ $transaction->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- Edit Quantity Modal -->
                        <div class="modal fade" id="editQuantityModal{{ $transaction->id }}" tabindex="-1"
                            aria-labelledby="editQuantityModalLabel{{ $transaction->id }}" aria-hidden="true" data-bs-backdrop="static">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('inventory.damage_transactions_update', $transaction->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editQuantityModalLabel{{ $transaction->id }}">Edit Quantity</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="d-flex align-items-center mb-1">
                                                <label class="form-label me-2"><strong>Item Name:</strong></label>
                                                <p class="mb-2">{{ $transaction->item->item_name }}</p>
                                            </div>
                                            <div class="d-flex align-items-center mb-1">
                                                <label class="form-label me-2"><strong>Available Stock:</strong></label>
                                                <p class="mb-2">{{ $transaction->item->qtyInStock }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Operation</strong></label>
                                                <div class="d-flex">
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="radio" name="operation"
                                                            id="operationAdd{{ $transaction->id }}" value="add" required checked>
                                                        <label class="form-check-label" for="operationAdd{{ $transaction->id }}">
                                                            Add Quantity
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="operation"
                                                            id="operationReduce{{ $transaction->id }}" value="deduct" required>
                                                        <label class="form-check-label" for="operationReduce{{ $transaction->id }}">
                                                            Reduce Quantity
                                                        </label>
                                                    </div>
                                                </div>
                                                @error('operation')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <label for="quantity{{ $transaction->id }}" class="form-label me-2"
                                                    id="quantityLabel{{ $transaction->id }}">Quantity:</label>
                                                <input type="number" class="form-control @error('quantity') is-invalid @enderror w-50"
                                                    id="quantity{{ $transaction->id }}" name="quantity" value="{{ old('quantity') }}" min="1"
                                                    required>
                                                @error('quantity')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>                        
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Damage Transaction Modal -->
    <div class="modal fade" id="addDamageTransactionModal" tabindex="-1" aria-labelledby="addDamageTransactionModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('inventory.storeDamageTransaction') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addDamageTransactionModalLabel">Add Damage Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Removed Category Selection -->

                        <div class="mb-3">
                            <label for="item_id" class="form-label">Item Name</label>
                            <select class="form-select @error('item_id') is-invalid @enderror" id="item_id"
                                name="item_id" required>
                                <option value="">Select Item</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}"
                                        {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->item_name }} - {{ $item->unit_of_measurement }} (Stock:
                                        {{ $item->qtyInStock }})
                                    </option>
                                @endforeach
                            </select>
                            @error('item_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity Damaged</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                id="quantity" name="quantity" value="{{ old('quantity') }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="damage_description" class="form-label">Damage Description</label>
                            <textarea class="form-control @error('damage_description') is-invalid @enderror" id="damage_description"
                                name="damage_description" rows="2" required>{{ old('damage_description') }}</textarea>
                            @error('damage_description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to update the label for the quantity input
        function updateQuantityLabel(operation, elementId) {
            const labelId = "quantityLabel" + elementId;
            const labelElement = document.getElementById(labelId);

            if (operation === 'add') {
                labelElement.textContent = "Quantity to Add";
            } else if (operation === 'deduct') {
                labelElement.textContent = "Quantity to Deduct";
            }
        }

        // Attach event listeners to the radio buttons
        const operationRadios = document.querySelectorAll('input[name="operation"]');
        operationRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const transactionId = this.id.match(/\d+/)[0];
                updateQuantityLabel(this.value, transactionId);
            });
        });

        // Initialize Select2 (only if necessary)
        $('#item_id').select2({
            allowClear: true,
            placeholder: "Select Item",
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: $('#addDamageTransactionModal')
        });

        // Set the default label on page load
        document.addEventListener('DOMContentLoaded', function() {
            operationRadios.forEach(radio => {
                if (radio.checked) {
                    const transactionId = radio.id.match(/\d+/)[0];
                    updateQuantityLabel(radio.value, transactionId);
                }
            });
        });
    </script>
@endsection