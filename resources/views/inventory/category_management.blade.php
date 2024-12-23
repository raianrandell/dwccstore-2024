@extends('layout.inventory')

@section('title', 'Category Management')

@section('content')
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active">Category Management</li>
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
    @if (Session::has('fail'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
            <div>
                {{ Session::get('fail') }}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Add button next to the search -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <!-- You can place a search bar here if needed -->
        </div>
        <div>
            <!-- Add New Category Button -->
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal"
                data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-1"></i> Add Category
            </button>
        </div>
    </div>

    <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            List of Categories
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Stock Number</th>
                        <th>Date Encoded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $index => $category)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $category->category_name }}</td>
                            <td>{{ $category->stock_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($category->created_at)->format('m-d-Y') }}</td>

                            <td>
                                <!-- View Button -->
                                <a href="#" class="btn btn-primary btn-sm rounded-circle view_btn"
                                    data-bs-toggle="modal" data-bs-target="#viewCategoryModal"
                                    data-category-id="{{ $category->id }}" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addCategoryForm" action="{{ route('inventory.addcategory') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Display Validation Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-exclamation-triangle me-2" style="font-size: 1.5rem;"></i>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select class="form-select" id="section" name="sec_id" required>
                                <!-- Ensure the name matches the validation -->
                                <option value="" selected>Select Section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}"
                                        {{ old('sec_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->sec_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="category_name"
                                value="{{ old('category_name') }}" required oninput="generateStockNumber()">
                        </div>
                        <div class="mb-3">
                            <label for="stock_num" class="form-label">Stock Number</label>
                            <input type="text" class="form-control" id="stock_num" name="stock_num"
                                value="{{ old('stock_num') }}" readonly required>
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


    <!-- View Category Modal -->
    <div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6><strong>Category Name:</strong> <span id="viewCategoryName"></span></h6>
                    <h6><strong>Date Encoded:</strong> <span id="viewCategoryDate"></span></h6>
                    <hr>
                    <!-- Wrap heading and search bar in a flex container -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Items under <span id="viewCategoryTitle"></span></h5>
                        <!-- Search Bar -->
                        <div style="max-width: 300px;">
                            <input type="text" id="itemSearch" class="form-control" placeholder="Search items...">
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Barcode No.</th>
                                <th>Item Name</th>
                                <th>Qty in Stock</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesList">
                            <!-- Items will be populated here via JavaScript -->
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Script to Generate Stock Number -->
    <script>
        function generateStockNumber() {
            const categoryName = document.getElementById('categoryName').value.trim();
            const stockNumberInput = document.getElementById('stock_num');

            if (categoryName) {
                // Extract the first two letters of the category name (uppercase)
                const prefix = categoryName.slice(0, 2).toUpperCase();
                // Generate a random three-digit number between 001 and 999
                const randomNum = String(Math.floor(Math.random() * 999) + 1).padStart(3, '0');
                // Set the stock number input value
                stockNumberInput.value = `${prefix}${randomNum}`;
            } else {
                // Clear the stock number input if the category name is empty
                stockNumberInput.value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 on the section dropdown with dropdownParent
            $('#section').select2({
                allowClear: true,
                placeholder: "Select Section",
                width: '100%',
                theme: 'bootstrap-5', // Use 'bootstrap-5' since you're using Bootstrap 5
                dropdownParent: $('#addCategoryModal') // Ensure the dropdown is within the modal
            });
        });

        // When the modal is hidden, reset the form and remove error messages
        var addCategoryModal = document.getElementById('addCategoryModal');
        addCategoryModal.addEventListener('hidden.bs.modal', function() {
            // Reset the form, which also clears the input fields
            var form = document.getElementById('addCategoryForm');
            form.reset();

            // Manually clear the input fields if needed (optional, as form.reset() should suffice)
            var inputs = form.querySelectorAll('input');
            inputs.forEach(function(input) {
                input.value = '';
            });

            $('#section').val(null).trigger('change');

            // Remove any displayed validation errors (alert messages)
            var errorAlerts = document.querySelectorAll('.alert.alert-danger');
            errorAlerts.forEach(function(alert) {
                alert.remove();
            });

            // Hide the invalid feedback messages (if any)
            var invalidFeedbacks = document.querySelectorAll('.invalid-feedback');
            invalidFeedbacks.forEach(function(feedback) {
                feedback.style.display = 'none';
            });

            // Optionally, remove any 'is-invalid' class added to form inputs
            var invalidInputs = form.querySelectorAll('.is-invalid');
            invalidInputs.forEach(function(input) {
                input.classList.remove('is-invalid');
            });
        });

        // Automatically open the modal if there are validation errors
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                var addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
                addCategoryModal.show();
            });
        @endif

       $(document).ready(function() {
    // Attach event listener to the search input
    $('#itemSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        var hasVisibleRows = false;
        $("#categoriesList tr.data-row").each(function() {
            if ($(this).text().toLowerCase().indexOf(value) > -1) {
                $(this).show();
                hasVisibleRows = true;
            } else {
                $(this).hide();
            }
        });
        if (!hasVisibleRows) {
            $('#noItemsFound').show();
        } else {
            $('#noItemsFound').hide();
        }
    });

    // Handle the 'View' button click
    $('.view_btn').on('click', function(e) {
        e.preventDefault();

        // Get the category ID from data attribute
        var categoryId = $(this).data('category-id');

        // Clear previous data
        $('#viewCategoryName').text('');
        $('#viewCategoryDate').text('');
        $('#viewCategoryTitle').text('');
        $('#categoriesList').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#itemSearch').val(''); // Clear the search input

        // Make an AJAX request to fetch category items
        $.ajax({
            url: '{{ url('/inventory/category') }}/' + categoryId + '/items',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Populate the modal with category details
                $('#viewCategoryName').text(response.category_name);
                $('#viewCategoryDate').text(response.created_at);
                $('#viewCategoryTitle').text(response.category_name);

                // Populate the items list
                var itemsHtml = '';
                if (response.items.length > 0) {
                    $.each(response.items, function(index, item) {
                        itemsHtml += '<tr class="data-row">';
                        itemsHtml += '<td>' + (index + 1) + '</td>';
                        itemsHtml += '<td>' + item.barcode + '</td>';
                        itemsHtml += '<td>' + item.item_name + '</td>';
                        itemsHtml += '<td>' + item.qtyInStock + '</td>';
                        itemsHtml += '</tr>';
                    });
                    // Add a hidden row for 'No items found' message
                    itemsHtml += '<tr id="noItemsFound" style="display: none;"><td colspan="4" class="text-center">No items found.</td></tr>';
                } else {
                    itemsHtml =
                        '<tr><td colspan="4" class="text-center">No items found under this category.</td></tr>';
                }

                $('#categoriesList').html(itemsHtml);
            },
            error: function(xhr, status, error) {
                // Handle errors
                $('#categoriesList').html(
                    '<tr><td colspan="4" class="text-center text-danger">Failed to load items.</td></tr>'
                );
                console.error('Error fetching category items:', error);
            }
        });
    });

    // Optional: Clear modal data when it's closed
    $('#viewCategoryModal').on('hidden.bs.modal', function() {
        $('#viewCategoryName').text('');
        $('#viewCategoryDate').text('');
        $('#viewCategoryTitle').text('');
        $('#categoriesList').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#itemSearch').val(''); // Clear the search input
    });
});

    </script>

@endsection
