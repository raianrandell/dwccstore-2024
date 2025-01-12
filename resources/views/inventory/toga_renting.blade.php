@extends('layout.inventory')

@section('title', 'Toga Renting')

@section('content')
<ol class="breadcrumb mb-3 mt-5">
    <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Toga Renting</a></li>
</ol>

<div id="alerts-container"></div>

@if(session('message'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <!-- You can place a search bar here if needed -->
    </div>
    <div>
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="fas fa-plus me-1"></i> Add Item For Rent
        </button>
    </div>
</div>

<div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Toga Rent List
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Item Name For Rent</th>
                    <th>Total Quantity</th>
                    <th>Remaining Stock Available</th>
                    <th>Quantity Borrowed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->total_quantity }}</td>
                    <td>{{ $item->total_quantity - $item->quantity_borrowed }}</td>
                    <td>{{ $item->quantity_borrowed }}</td>
                    <td>
                        <button 
                            type="button" 
                            class="btn btn-success btn-sm rounded-circle open-modal" 
                            data-id="{{ $item->id }}" 
                            data-name="{{ $item->item_name }}" 
                            data-total="{{ $item->total_quantity }}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addQuantityModal" tabindex="-1" aria-labelledby="addQuantityModalLabel" aria-hidden="true" data-bs-backdrop="static">
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
                        <input type="number" class="form-control" id="addQuantityInput" name="add_quantity" min="1" required>
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


<!-- Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true" data-bs-backdrop="static">
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
                    <input type="number" class="form-control" id="total_quantity" name="total_quantity" required>
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
    //this is for addinf quantity
    $(document).on('click', '.open-modal', function () {
        const itemId = $(this).data('id');
        
        // Fetch the latest data for the item using an AJAX request
        $.ajax({
            url: `/inventory/items/${itemId}`,
            type: 'GET',
            success: function (response) {
                // Populate modal fields with the updated data
                $('#modalItemName').text(response.item_name);
                $('#modalTotalQuantity').text(response.total_quantity);
                $('#modalItemId').val(itemId);

                // Show the modal
                $('#addQuantityModal').modal('show');
            },
            error: function () {
                alert('Failed to load item data. Please try again.');
            }
        });
    });

    $('#saveQuantityButton').on('click', function () {
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
                success: function (response) {
                    // Reset the input field
                    $('#addQuantityInput').val('');

                    // Close the modal
                    $('#addQuantityModal').modal('hide');

                    // Show success message dynamically
                    const successAlert = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                    $('#alerts-container').html(successAlert);

                    // Remove the alert after 2 seconds
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 2000);

                    // Update the table row with the new total quantity
                    const row = $(`button[data-id="${itemId}"]`).closest('tr');
                    row.find('td').eq(1).text(response.total_quantity); // Update "Total Quantity"
                    row.find('td').eq(2).text(response.total_quantity - parseInt(row.find('td').eq(3).text())); // Update "Remaining Stock"
                },
                error: function (xhr) {
                    // Show error message dynamically
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${xhr.responseJSON.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                    $('#alerts-container').html(errorAlert);

                    // Remove the alert after 2 seconds
                    setTimeout(() => {
                        $('.alert').alert('close');
                    }, 2000);
                }
            });
        } else {
            alert('Please enter a valid quantity.');
        }
    });

    //this is for adding item for rent 
    document.addEventListener('DOMContentLoaded', () => {
        const addItemForm = document.getElementById('addItemForm');
        const tableBody = document.querySelector('#datatablesSimple tbody');

        // Clear validation errors
        const clearValidationErrors = () => {
            document.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
                const errorFeedback = field.parentNode.querySelector('.invalid-feedback');
                if (errorFeedback) {
                    errorFeedback.remove();
                }
            });
        };

        addItemForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearValidationErrors(); // Clear previous validation errors

            // Serialize form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Check for duplicate in the table
                    const isDuplicate = Array.from(tableBody.querySelectorAll('tr')).some(row => {
                        return row.cells[0].textContent.trim() === data.item.item_name;
                    });

                    if (isDuplicate) {
                        alert('This item already exists in the table.');
                        return;
                    }

                    // Add new row to the table
                    const newRow = `
                        <tr>
                            <td>${data.item.item_name}</td>
                            <td>${data.item.total_quantity}</td>
                            <td>${data.item.total_quantity - data.item.quantity_borrowed}</td>
                            <td>${data.item.quantity_borrowed}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm rounded-circle">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML('beforeend', newRow);

                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
                    modal.hide();

                    // Reset the form
                    addItemForm.reset();

                    // Show success message
                    alert(data.message);
                } else {
                    // Display validation errors if provided
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const field = document.querySelector(`[name="${key}"]`);
                            if (field) {
                                field.classList.add('is-invalid');
                                const errorMessage = document.createElement('div');
                                errorMessage.className = 'invalid-feedback';
                                errorMessage.textContent = data.errors[key];
                                field.parentNode.appendChild(errorMessage);
                            }
                        });
                    } else {
                        alert(data.message); // Generic failure message
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
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
    });

</script>

@endsection
