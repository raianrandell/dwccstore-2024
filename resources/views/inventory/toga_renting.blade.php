@extends('layout.inventory')

@section('title', 'Toga Renting')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Toga Renting</li>
    </ol>

    <!-- Alerts Container -->
    <div id="alerts-container"></div>

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fa-lg"></i>
            <div>
                {{ Session::get('success') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (Session::has('fail'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
            <div>
                {{ Session::get('fail') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <div>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus me-1"></i> Add Item For Rent
            </button>
        </div>
    </div>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Toga Rent List
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Item For Rent</th>
                        <th>Total Quantity</th>
                        <th>Remaining Stock Available (Good)</th>
                        <th>Quantity Borrowed</th>
                        <th>Return Damaged Item</th>
                        <th>Return Lost Item</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            <td>{{ $item->total_quantity - $item->quantity_borrowed - $item->damaged_quantity - $item->lost_quantity }}
                            </td>
                            <td>{{ $item->quantity_borrowed }}</td>
                            <td>{{ $item->damaged_quantity }}</td>
                            <td>{{ $item->lost_quantity }}</td>
                            <td>
                                <button type="button" title="Add Stock"
                                    class="btn btn-success btn-sm rounded-circle open-modal" data-id="{{ $item->id }}"
                                    data-name="{{ $item->item_name }}" data-total="{{ $item->total_quantity }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Quantity Modal -->
    <div class="modal fade" id="addQuantityModal" tabindex="-1" aria-labelledby="addQuantityModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addQuantityModalLabel">Add Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addQuantityForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Item Name:</label>
                            <p id="modalItemName" class="fw-bold"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Quantity:</label>
                            <p id="modalTotalQuantity" class="fw-bold"></p>
                        </div>
                        <div class="mb-3">
                            <label for="addQuantityInput" class="form-label">Add Quantity</label>
                            <input type="number" class="form-control" id="addQuantityInput" name="add_quantity"
                                min="1" required>
                        </div>
                        <input type="hidden" id="modalItemId" name="item_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveQuantityButton">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addItemForm" action="{{ route('inventory.add_item_for_rent') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add Item For Rent</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name For Rent</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <select class="form-control" id="color" name="color" required>
                                <option value="" disabled selected>Select Color</option>
                                <!-- Primary Colors -->
                                <option value="Red">Red</option>
                                <option value="Blue">Blue</option>
                                <option value="Yellow">Yellow</option>
                                <!-- Secondary Colors -->
                                <option value="Green">Green</option>
                                <option value="Orange">Orange</option>
                                <option value="Purple">Purple</option>
                                <!-- Additional Common Colors -->
                                <option value="Black">Black</option>
                                <option value="White">White</option>
                                <option value="Gray">Gray</option>
                                <option value="Pink">Pink</option>
                                <option value="Brown">Brown</option>
                                <option value="Gold">Gold</option>
                                <option value="Silver">Silver</option>
                                <option value="Cyan">Cyan</option>
                                <option value="Magenta">Magenta</option>
                                <option value="Beige">Beige</option>
                                <option value="Maroon">Maroon</option>
                                <option value="Navy">Navy</option>
                                <option value="Teal">Teal</option>
                                <option value="Lavender">Lavender</option>
                                <option value="Turquoise">Turquoise</option>
                                <option value="Violet">Violet</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="size" class="form-label">Size</label>
                            <select class="form-control" id="size" name="size" required>
                                <option value="" disabled selected>Select Size</option>
                                <option value="Double Extra Small">XXS</option>
                                <option value="Extra Small">XS</option>
                                <option value="Small">S</option>
                                <option value="Medium">M</option>
                                <option value="Large">L</option>
                                <option value="Extra Large">XL</option>
                                <option value="Double Extra Large">XXL</option>
                                <!-- Add more sizes as needed -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="total_quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="total_quantity" name="total_quantity"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Alert Handler
        function displayAlert(type, message, duration = 3000) {
            const alertType = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            const alertHTML = `
            <div class="alert ${alertType} alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="${icon} me-2 fa-lg"></i>
                <div>${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
            $('#alerts-container').html(alertHTML);
            setTimeout(() => $('.alert').alert('close'), 3000);
        }

        // Open Add Quantity Modal
        $(document).on('click', '.open-modal', function() {
            const itemId = $(this).data('id');

            // Fetch the latest data for the item using AJAX
            $.ajax({
                url: `/inventory/items/${itemId}`,
                type: 'GET',
                success: function(response) {
                    $('#modalItemName').text(response.item_name);
                    $('#modalTotalQuantity').text(response.total_quantity);
                    $('#modalItemId').val(itemId);
                    $('#addQuantityModal').modal('show');
                },
                error: function() {
                    displayAlert('fail', 'Failed to load item data. Please try again.');
                }
            });
        });

        // Save Quantity
        $('#saveQuantityButton').on('click', function() {
            const itemId = $('#modalItemId').val();
            const addedQuantity = $('#addQuantityInput').val();

            if (addedQuantity && addedQuantity > 0) {
                $.ajax({
                    url: `/inventory/items/${itemId}/add-stock`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        amount: addedQuantity
                    },
                    success: function(response) {
                        $('#addQuantityInput').val('');
                        $('#addQuantityModal').modal('hide');
                        displayAlert('success', response.message);

                        // Update table dynamically
                        const row = $(`button[data-id="${itemId}"]`).closest('tr');
                        row.find('td').eq(1).text(response.total_quantity); // Total Quantity
                        row.find('td').eq(2).text(response.remaining_stock); // Remaining Stock
                    },
                    error: function(xhr) {
                        displayAlert('fail', xhr.responseJSON.message ||
                            'An error occurred. Please try again.');
                    }
                });
            } else {
                displayAlert('fail', 'Please enter a valid quantity.');
            }
        });

        // Add Item Form
        $('#addItemForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Avoid duplicate entries
                        const isDuplicate = Array.from($('#datatablesSimple tbody').find('tr')).some(row => {
                            return row.cells[0].textContent.trim() === data.item.item_name;
                        });
                        if (isDuplicate) {
                            displayAlert('fail', 'This item already exists in the table.');
                            return;
                        }

                        // Add new item to the table
                        const newRow = `
                    <tr>
                        <td>${data.item.item_name}</td>
                        <td>${data.item.total_quantity}</td>
                        <td>${data.item.total_quantity - data.item.quantity_borrowed}</td>
                        <td>${data.item.quantity_borrowed}</td>
                        <td>${data.item.damaged_quantity || 0}</td>
                        <td>${data.item.lost_quantity || 0}</td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm rounded-circle open-modal" 
                                data-id="${data.item.id}" 
                                data-name="${data.item.item_name}" 
                                data-total="${data.item.total_quantity}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        $('#datatablesSimple tbody').append(newRow);

                        // Close the modal and reset the form
                        $('#addItemModal').modal('hide');
                        $('#addItemForm')[0].reset();
                        displayAlert('success', data.message);
                        window.location.reload();
                    } else {
                        displayAlert('fail', data.message || 'An error occurred. Please try again.');
                    }
                })
                .catch(() => displayAlert('fail', 'An error occurred. Please try again.'));
        });

        // Initialize Select2 for dropdowns
        $('#color').select2({
            allowClear: true,
            placeholder: "Select Color",
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: $('#addItemModal'),
        });

        $('#size').select2({
            allowClear: true,
            placeholder: "Select Size",
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: $('#addItemModal'),
        });
    </script>
@endsection
