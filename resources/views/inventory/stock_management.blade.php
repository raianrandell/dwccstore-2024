@extends('layout.inventory')

@section('title', 'Stock Management')

@section('content')
    <!-- Breadcrumb -->
    <div id="breadcrumb">
        <ol class="breadcrumb mb-3 mt-5">
            <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Stock Management</li>
        </ol>
    </div>
    <style>
        /* General styling for the entire table */
        table {
            width: 100%;
            /* Set table to take full width of its container */
            border-collapse: collapse;
            /* Remove space between borders of adjacent cells */
        }

        /* Styling for table headers and data cells */
        th,
        td {
            text-align: left;
            /* Aligns text to the left for a cleaner look */
            padding: 10px;
            /* Adds space around text for better readability */
            border: 1px solid #ddd;
            /* Adds a light gray border to each cell */
        }

        /* Specific column widths */
        th:nth-child(1),
        td:nth-child(1) {
            width: 5%;
            /* Set the width of the first column */
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 8%;
            /* Set the width of the second column */
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 9%;
            /* Set the width of the third column */
        }

        th:nth-child(4),
        td:nth-child(4) {
            width: 9%;
            /* Set the width of the fourth column */
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 5%;
            /* Set the width of the fifth column */
        }

        th:nth-child(6),
        td:nth-child(6) {
            width: 7%;
            /* Set the width of the sixth column */
        }

        th:nth-child(7),
        td:nth-child(7) {
            width: 5%;
            /* Set the width of the seventh column */
        }

        th:nth-child(8),
        td:nth-child(8) {
            width: 7%;
            /* Set the width of the eighth column */
        }

        th:nth-child(9),
        td:nth-child(9) {
            width: 3%;
            /* Set the width of the ninth column */
        }

        th:nth-child(10),
        td:nth-child(10) {
            width: 6%;
            /* Set the width of the tenth column */
        }

        th:nth-child(11),
        td:nth-child(11) {
            width: 6%;
            /* Set the width of the eleventh column */
        }

        th:nth-child(12),
        td:nth-child(12) {
            width: 7%;
            /* Set the width of the twelfth column */
        }

        th:nth-child(13),
        td:nth-child(13) {
            width: 6%;
            /* Set the width of the thirteenth column */
        }

        th:nth-child(14),
        td:nth-child(14) {
            width: 5%;
            /* Set the width of the fourteenth column */
        }

        th:nth-child(15),
        td:nth-child(15) {
            width: 10%;
            /* Set the width of the fifteenth column */
        }
    </style>
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

    <!-- Main Stock Management Section -->
    <div id="stock-management-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
             <div>

            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="{{ route('inventory.priceUpdate') }}" class="btn btn-outline-primary">
                        <i class="fas fa-peso-sign me-1"></i> View Price Update
                    </a>
                    &nbsp;
                    <a href="{{ route('inventory.transferItemsPrice') }}" class="btn btn-outline-warning me-2">
                        <i class="fas fa-exchange-alt me-1"></i> View Transfer Items Price
                    </a>
                    &nbsp;
                </div>
                <div>
                    <a href="{{ route('inventory.additem') }}" class="btn btn-outline-success">
                        <i class="fas fa-plus me-1"></i> Add New Item
                    </a>
                </div>
            </div>
            
        </div>
        <div class="card mb-4" style="box-shadow: 12px 12px 7px rgba(0, 0, 0, 0.3);">
            <div class="card-header">
                <i class="fas fa-cubes"></i>
                Stock Inventory
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Barcode No.</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Qty in Stock</th>
                            <th>Low Stock Limit</th>
                            <th>Unit</th>
                            <th>Base Price</th>
                            <th>Selling Price</th>
                            <th>Expiration Date</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->barcode }}</td>
                                <td>
                                    {{ $item->item_name }}
                                    @if ($item->color || $item->size || $item->weight)
                                        <span class="text-muted">
                                            @if ($item->color)
                                                {{ $item->color }}
                                            @endif
                                            @if ($item->size)
                                                {{ $item->size }}
                                            @endif
                                            @if ($item->weight)
                                                {{ $item->weight }} kg
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $item->item_description }}
                                </td>
                                <td>{{ $item->item_brand ?? 'N/A' }}</td>
                                <td>{{ $item->category->category_name ?? 'N/A' }}</td>
                                <td>{{ $item->qtyInStock }}</td>
                                <td>{{ $item->low_stock_limit }}</td>
                                <td>{{ $item->unit_of_measurement }}</td>
                                <td>₱{{ number_format($item->base_price, 2) }}</td>
                                <td>₱{{ number_format($item->selling_price, 2) }}</td>
                                <td>
                                    {{ $item->expiration_date ? \Carbon\Carbon::parse($item->expiration_date)->format('m-d-Y') : 'N/A' }}
                                    @if ($item->expiration_date)
                                        @php
                                            $expirationDate = \Carbon\Carbon::parse($item->expiration_date);
                                            $currentDate = \Carbon\Carbon::now('Asia/Manila')->startOfDay(); // Ensure comparison is done for full-day
                                            $warningDates = [
                                                'day' => $currentDate->copy()->addDay(1),
                                                'twoDays' => $currentDate->copy()->addDays(2),
                                                'threeDays' => $currentDate->copy()->addDays(3),
                                                'fourDays' => $currentDate->copy()->addDays(4),
                                                'fiveDays' => $currentDate->copy()->addDays(5),
                                                'sixDays' => $currentDate->copy()->addDays(6),
                                                'week' => $currentDate->copy()->addDays(7),
                                                'month' => $currentDate->copy()->addMonth(1),
                                            ];
                                        @endphp
                                        @if ($expirationDate < $currentDate)
                                            <span class="badge bg-secondary text-white">Expired</span>
                                        @elseif ($expirationDate->isSameDay($currentDate))
                                            <span class="badge bg-danger text-white">Expiring Today</span>
                                        @elseif ($expirationDate <= $warningDates['day'])
                                            <span class="badge bg-danger text-white">Expiring in 1 day</span>
                                        @elseif ($expirationDate <= $warningDates['twoDays'])
                                            <span class="badge bg-danger text-white">Expiring in 2 days</span>
                                        @elseif ($expirationDate <= $warningDates['threeDays'])
                                            <span class="badge bg-danger text-white">Expiring in 3 days</span>
                                        @elseif ($expirationDate <= $warningDates['fourDays'])
                                            <span class="badge bg-danger text-white">Expiring in 4 days</span>
                                        @elseif ($expirationDate <= $warningDates['fiveDays'])
                                            <span class="badge bg-danger text-white">Expiring in 5 days</span>
                                        @elseif ($expirationDate <= $warningDates['sixDays'])
                                            <span class="badge bg-danger text-white">Expiring in 6 days</span>
                                        @elseif ($expirationDate <= $warningDates['week'])
                                            <span class="badge bg-warning text-dark">Expiring in 7 days</span>
                                        @elseif ($expirationDate <= $warningDates['month'])
                                            <span class="badge bg-info text-dark">Expiring soon</span>
                                        @endif
                                    @endif
                                </td>


                                <td>{{ $item->supplier_info ?? 'N/A' }}</td>
                                <td>
                                    @if ($item->qtyInStock == 0)
                                        <span class="badge bg-secondary">Out of Stock</span>
                                    @elseif ($item->qtyInStock <= $item->low_stock_limit)
                                        <span class="badge bg-danger">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>

                                <td>
                                    <button type="button"
                                        class="btn btn-info btn-sm rounded-circle view-item-btn text-white"
                                        title="View Item" data-bs-toggle="modal" data-bs-target="#viewItemModal"
                                        data-itemid="{{ $item->id }}" data-itemname="{{ $item->item_name }}"
                                        data-description="{{ $item->item_description ?? 'N/A' }}"
                                        data-brand="{{ $item->item_brand ?? 'N/A' }}"
                                        data-category="{{ $item->category->category_name ?? 'N/A' }}"
                                        data-qtyinstock="{{ $item->qtyInStock }}"
                                        data-lowstocklimit="{{ $item->low_stock_limit }}"
                                        data-unit="{{ $item->unit_of_measurement }}"
                                        data-baseprice="{{ number_format($item->base_price, 2) }}"
                                        data-sellingprice="{{ number_format($item->selling_price, 2) }}"
                                        data-expirationdate="{{ $item->expiration_date ? \Carbon\Carbon::parse($item->expiration_date)->format('m-d-Y') : 'N/A' }}"
                                        data-expirationstatus="{{ $item->expiration_date ? (\Carbon\Carbon::parse($item->expiration_date) < \Carbon\Carbon::now('Asia/Manila') ? 'Expired' : (\Carbon\Carbon::parse($item->expiration_date) <= \Carbon\Carbon::now('Asia/Manila')->addDays(7) ? 'Expiring in 1 week' : 'Valid')) : 'N/A' }}"
                                        data-supplier="{{ $item->supplier_info ?? 'N/A' }}"
                                        data-stocklogs="{{ $item->stockLogs->toJson() }}">
                                        <i class="fas fa-eye"></i>
                                    </button>


                                    <a href="{{ route('inventory.edititem', $item->id) }}"
                                        class="btn btn-warning btn-sm rounded-circle text-white" title="Update Item">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-primary btn-sm rounded-circle print-barcode-btn"
                                        title="Print Item Barcode" data-bs-toggle="modal"
                                        data-bs-target="#printBarcodeModal" data-barcode="{{ $item->barcode }}"
                                        data-itemname="{{ $item->item_name }}"
                                        data-sellingprice="{{ number_format($item->selling_price, 2) }}">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm rounded-circle"
                                        title="Update Stock" data-bs-toggle="modal" data-bs-target="#updateStockModal"
                                        data-itemid="{{ $item->id }}" data-itemname="{{ $item->item_name }}"
                                        data-baseprice="{{ number_format($item->base_price, 2) }}"
                                        data-sellingprice="{{ number_format($item->selling_price, 2) }}"
                                        data-qtyinstock="{{ $item->qtyInStock }}"
                                        data-expirationdate="{{ $item->expiration_date ? \Carbon\Carbon::parse($item->expiration_date)->format('m-d-Y') : 'N/A' }}">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button type="button"
                                        class="btn btn-dark btn-sm rounded-circle transfer-item-btn text-white mt-1"
                                        title="Transfer Old Price & Stock" data-bs-toggle="modal"
                                        data-bs-target="#transferItemModal" data-itemid="{{ $item->id }}"
                                        data-itemname="{{ $item->item_name }}" data-baseprice="{{ $item->base_price }}"
                                        data-sellingprice="{{ $item->selling_price }}"
                                        data-qtyinstock="{{ $item->qtyInStock }}">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>&nbsp;
                                    <button type="button" class="btn btn-secondary btn-sm rounded-circle mt-1"
                                        title="Modify Expiration Date and Add Stock" data-bs-toggle="modal"
                                        data-bs-target="#modifyExpirationDateModal" data-itemid="{{ $item->id }}"
                                        data-itemname="{{ $item->item_name }}"
                                        data-expirationdate="{{ $item->expiration_date ? \Carbon\Carbon::parse($item->expiration_date)->format('m-d-Y') : 'N/A' }}"
                                        data-qtyinstock="{{ $item->qtyInStock }}">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>


                                </td>
                            </tr>
                        @endforeach
                        @if ($items->isEmpty())
                            <tr>
                                <td colspan="15" class="text-center">No items found.</td>
                            </tr>
                        @endif
                    </tbody>

                    <!-- New <tbody> for Variants Grouping -->
                    <tbody>
                        @foreach ($items->groupBy('item_name') as $group)
                            @foreach ($group as $index => $item)
                                @if ($index > 0)
                                    <!-- Only show variant items -->
                                    <tr>
                                        <td>{{ $loop->parent->iteration }}-{{ $index }}</td>
                                        <!-- Link iteration to original group -->
                                        <td>{{ $item->barcode }}</td>
                                        <td>
                                            {{ $item->item_name }}
                                            <span class="badge bg-info text-white">Variant</span>
                                        </td>
                                        <td>{{ $item->item_description }}</td>
                                        <td>{{ $item->item_brand ?? 'N/A' }}</td>
                                        <td>{{ $item->category->category_name ?? 'N/A' }}</td>
                                        <td>{{ $item->qtyInStock }}</td>
                                        <td>{{ $item->low_stock_limit }}</td>
                                        <td>{{ $item->unit_of_measurement }}</td>
                                        <td>₱{{ number_format($item->base_price, 2) }}</td>
                                        <td>₱{{ number_format($item->selling_price, 2) }}</td>
                                        <!-- Add other columns as needed -->
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- Print Barcode Modal -->
    <div class="modal fade" id="printBarcodeModal" tabindex="-1" aria-labelledby="printBarcodeModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="printBarcodeModalLabel">Print Barcode</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Left side -->
                        <div class="col-md-6">
                            <p><strong>Barcode Number:</strong> <span id="modalBarcodeNumber"></span></p>
                            <p><strong>Item Name:</strong> <span id="modalItemName"></span></p>
                            <p><strong>Selling Price:</strong> ₱<span id="modalSellingPrice"></span></p>
                            <div class="mb-3">
                                <label for="numberOfBarcodes" class="form-label"><strong>Number of Barcodes to
                                        Print:</strong></label>
                                <input type="number" class="form-control" id="numberOfBarcodes" value="1"
                                    min="1">
                            </div>
                        </div>
                        <!-- Right side -->
                        <div class="col-md-6 text-center">
                            <!-- Barcode Image -->
                            <div id="barcodeImage">
                                <svg id="barcode"></svg>
                            </div>
                            <button type="button" class="btn btn-success mt-3 btn-md" id="printButton">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Stock Modal -->
    <div class="modal fade" id="updateStockModal" tabindex="-1" aria-labelledby="updateStockModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="updateStockModalLabel">Update Stock</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addStockForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="modalItemId">
                        <p><strong>Item Name:</strong> <span id="modalAddItemName"></span></p>

                        <!-- Operation and Quantity Input Fields Wrapper -->
                        <div id="operationQuantityFields">
                            <!-- Operation Selection -->
                            <div class="mb-3">
                                <label class="form-label"><strong>Operation:</strong></label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="operation"
                                            id="operationDeduct" value="deduct">
                                        <label class="form-check-label" for="operationDeduct">Deduct</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="operation"
                                            id="operationAdd" value="add" checked>
                                        <label class="form-check-label" for="operationAdd">Add</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Input -->
                            <div class="mb-3">
                                <label for="quantity" class="form-label"><strong>Quantity to Add:</strong></label>
                                <input type="number" name="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror" id="quantity"
                                    min="1" value="{{ old('quantity') }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3" id="expirationDateWrapper" style="display: none;">

                            </div>
                        </div>

                        <!-- Card for Base Price and Selling Price Update -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <strong>Increase Price Update</strong>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="price_update"
                                        id="priceUpdateNo" value="no"
                                        {{ old('price_update') === 'no' ? 'checked' : 'checked' }}>
                                    <label class="form-check-label" for="priceUpdateNo">No</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="price_update"
                                        id="priceUpdateYes" value="yes"
                                        {{ old('price_update') === 'yes' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="priceUpdateYes">Yes</label>
                                </div>
                                <br>
                                <!-- Current Prices and Stock -->
                                <div id="currentPriceFields" style="display: none;">
                                    <div class="mb-3 mt-3">
                                        <label for="currentBasePrice" class="form-label"><strong>Current Base
                                                Price:</strong></label>
                                        <span id="currentBasePrice"></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="currentSellingPrice" class="form-label"><strong>Current Selling
                                                Price:</strong></label>
                                        <span id="currentSellingPrice"></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="currentQtyInStock" class="form-label"><strong>Quantity in
                                                Stock:</strong></label>
                                        <span id="currentQtyInStock"></span>
                                    </div>
                                    <div class="mb-3" id="oldExpirationDateContainer" style="display: none;">
                                        <label class="form-label"><strong>Old Expiration Date:</strong></label>
                                        <span id="oldExpirationDate"></span>
                                    </div>
                                </div>

                                <!-- Price Fields (Show only if "Yes" is selected) -->
                                <div id="priceFields" style="display: none;">
                                    <div class="mb-3 mt-4">
                                        <label for="newBasePrice" class="form-label">New Base Price</label>
                                        <input type="number"
                                            class="form-control @error('new_base_price') is-invalid @enderror"
                                            id="newBasePrice" name="new_base_price" placeholder="Enter new base price"
                                            min="0" step="0.01" value="{{ old('new_base_price') }}">
                                        @error('new_base_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="newSellingPrice" class="form-label">New Selling Price</label>
                                        <input type="number"
                                            class="form-control @error('new_selling_price') is-invalid @enderror"
                                            id="newSellingPrice" name="new_selling_price"
                                            placeholder="Enter new selling price" min="0" step="0.01"
                                            value="{{ old('new_selling_price') }}">
                                        @error('new_selling_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- New Quantity in Stock Input Field -->
                                    <div class="mb-3">
                                        <label for="newQtyInStock" class="form-label">New Quantity in Stock</label>
                                        <input type="number" class="form-control" id="newQtyInStock"
                                            name="new_qty_in_stock" placeholder="Enter new quantity in stock"
                                            min="0" value="{{ old('new_qty_in_stock') }}">
                                    </div>

                                    <!-- Barcode Input Field -->
                                    <div class="mb-3">
                                        <label for="barcode" class="form-label">Barcode Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="barcodeNew" name="barcode_no"
                                                placeholder="Generate new barcode number" maxlength="13"
                                                value="{{ old('barcode_no') }}">
                                            <button type="button" class="btn btn-outline-secondary bg-success text-white"
                                                id="generateBarcodeBtn">
                                                <i class="fas fa-sync" id="barcodeIcon"></i>
                                            </button>
                                        </div>
                                        @error('barcode_no')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3" id="newExpirationDateContainer" style="display: none;">
                                        <label for="newExpirationDate" class="form-label">New Expiration Date</label>
                                        <input type="date" class="form-control" id="newExpirationDate"
                                            name="new_expiration_date" value="{{ old('new_expiration_date') }}">
                                    </div>
                                </div>
                            </div>
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



    <!-- View Item Modal -->
    <div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="viewItemModalLabel">View Item Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Item Details -->
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Item Name:</strong> <span id="modalItemNameView"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Description:</strong> <span id="modalItemDescriptionView"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Brand:</strong> <span id="modalItemBrand"></span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Category:</strong> <span id="modalItemCategory"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Quantity in Stock:</strong> <span id="modalQtyInStock"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Low Stock Limit:</strong> <span id="modalLowStockLimit"></span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Unit:</strong> <span id="modalUnit"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Base Price:</strong> ₱<span id="modalBasePrice"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Selling Price:</strong> ₱<span id="modalSellingPriceView"></span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <strong>Expiration Date:</strong> <span id="modalExpirationDate"></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Supplier:</strong> <span id="modalSupplier"></span>
                        </div>
                    </div>

                    <!-- Stock Logs Section -->
                    <h5 class="mt-4 d-flex justify-content-between align-items-center">Stock Logs</h5>
                    <table class="table table-bordered table-striped" id="datatablesSimple1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Quantity Change</th>
                                <th>Date/Time Updated</th>
                            </tr>
                        </thead>
                        <tbody id="stockLogsTbody">
                            <!-- Stock log entries will be populated here using JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="transferItemModal" tabindex="-1" aria-labelledby="transferItemModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferItemModalLabel">Transfer Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="transferItemForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to transfer this item?</p>
                        <div>
                            <strong>Item Name:</strong> <span id="modalTransferItemName"></span><br>
                            <strong>Current Stock:</strong> <span id="modalTransferQty"></span><br>
                            <strong>Base Price:</strong> ₱<span id="modalTransferBasePrice"></span><br>
                            <strong>Selling Price:</strong> ₱<span id="modalTransferSellingPrice"></span>
                        </div>
                        <div class="mt-3">
                            <label for="targetItemDropdown" class="form-label"><strong>Select Higher-Priced
                                    Variant:</strong></label>
                            <select class="form-select" id="targetItemDropdown" name="target_item_id" required>
                                <option value="">Select an item with a higher price...</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="item_id" id="modalTransferItemId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modify Expiration Date Modal -->
    <div class="modal fade" id="modifyExpirationDateModal" tabindex="-1"
        aria-labelledby="modifyExpirationDateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modifyExpirationDateModalLabel">Modify Expiration Date and Add Stock
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="modifyExpirationDateForm" method="POST"
                    action="{{ route('inventory.modifyExpirationDate') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="item_id" id="modalModifyItemId">
                        <p><strong>Item Name:</strong> <span id="modalModifyItemName"></span></p>
                        <div class="mb-3">
                            <label for="new_expiration_date" class="form-label">New Expiration Date</label>
                            <input type="date" class="form-control @error('new_expiration_date') is-invalid @enderror"
                                id="new_expiration_date" name="new_expiration_date" required>
                            @error('new_expiration_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="quantity_added" class="form-label">Quantity to Add</label>
                            <input type="number" class="form-control @error('quantity_added') is-invalid @enderror"
                                id="quantity_added" name="quantity_added" min="1"
                                value="{{ old('quantity_added') }}" required>
                            @error('quantity_added')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Modify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>





    <!-- jQuery (required by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



    <style>
        .spin {
            animation: spin 0.6s linear;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Include JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // When the print barcode modal is shown
            var printBarcodeModal = document.getElementById('printBarcodeModal');
            printBarcodeModal.addEventListener('show.bs.modal', function(event) {
                // Button that triggered the modal
                var button = event.relatedTarget;
                // Extract info from data-* attributes
                var barcodeNumber = button.getAttribute('data-barcode');
                var itemName = button.getAttribute('data-itemname');
                var sellingPrice = button.getAttribute('data-sellingprice');
                // Update the modal's content
                var modalBarcodeNumber = document.getElementById('modalBarcodeNumber');
                var modalItemName = document.getElementById('modalItemName');
                var modalSellingPrice = document.getElementById('modalSellingPrice');
                modalBarcodeNumber.textContent = barcodeNumber;
                modalItemName.textContent = itemName;
                modalSellingPrice.textContent = sellingPrice;
                // Generate barcode
                JsBarcode("#barcode", barcodeNumber, {
                    format: "CODE128",
                    displayValue: true,
                    fontSize: 12, // Adjust font size for readability
                    textMargin: 2, // Reduce margin for compactness
                    height: 50, // Standard height (about 0.52 inches)
                    width: 1 // Width of the barcode bars
                });
            });

            // Handle print button click
            var printButton = document.getElementById('printButton');
            printButton.addEventListener('click', function() {
                var numberOfBarcodes = document.getElementById('numberOfBarcodes').value;
                var barcodeNumber = document.getElementById('modalBarcodeNumber').textContent;
                var itemName = document.getElementById('modalItemName').textContent;
                var sellingPrice = document.getElementById('modalSellingPrice').textContent;

                // Open a new window for printing
                var printWindow = window.open();
                printWindow.document.write('<html><head><title>Print Barcode</title>');
                printWindow.document.write('<style>');
                printWindow.document.write('body { font-family: Arial, sans-serif; }');
                printWindow.document.write(
                    '.barcode-container { text-align: center; margin-bottom: 20px; }');
                printWindow.document.write('.barcode-container p { margin: 5px 0; }');
                printWindow.document.write('</style>');
                printWindow.document.write('</head><body>');

                // Generate the specified number of barcodes
                for (var i = 0; i < numberOfBarcodes; i++) {
                    printWindow.document.write('<div class="barcode-container">');
                    printWindow.document.write('<p>' + itemName + '</p>');
                    printWindow.document.write('<svg id="barcode' + i + '"></svg>');
                    printWindow.document.write('</div>');
                }

                // Include JsBarcode script
                printWindow.document.write(
                    '<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>'
                );
                printWindow.document.write('<script>');
                for (var i = 0; i < numberOfBarcodes; i++) {
                    printWindow.document.write('JsBarcode("#barcode' + i + '", "' + barcodeNumber +
                        '", { format: "CODE128", displayValue: true, fontSize: 12, textMargin: 2, height: 50, width: 1 });'
                    );
                }
                printWindow.document.write('<\/script>');

                printWindow.document.write('</body></html>');
                printWindow.document.close();

                // Wait for the content to load before printing
                printWindow.onload = function() {
                    printWindow.print();
                };
            });

            // Handle the Add Stock Modal show event
            var updateStockModal = document.getElementById('updateStockModal');
            updateStockModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var itemId = button.getAttribute('data-itemid');
                var itemName = button.getAttribute('data-itemname');

                // Update the modal's content
                document.getElementById('modalItemId').value = itemId;
                document.getElementById('modalAddItemName').textContent = itemName;

                // Reset the form to default state (Add operation)
                document.getElementById('operationAdd').checked = true;
                document.getElementById('quantity').value = '';
                var quantityLabel = document.querySelector('label[for="quantity"]');
                quantityLabel.innerHTML = '<strong>Quantity to Add:</strong>';
            });

            // Handle form submission for Add/Deduct Stock
            document.getElementById('addStockForm').onsubmit = function(event) {
                event.preventDefault(); // Prevent default form submission
                var form = this;
                var itemId = document.getElementById('modalItemId').value;
                var operation = form.querySelector('input[name="operation"]:checked').value;
                var actionUrl = '{{ route('inventory.updateStock', ':id') }}';
                actionUrl = actionUrl.replace(':id', itemId);
                form.action = actionUrl;
                form.submit(); // Submit the form
            };

            // Handle dynamic label updates based on operation
            const operationAdd = document.getElementById('operationAdd');
            const operationDeduct = document.getElementById('operationDeduct');
            const quantityLabel = document.querySelector('label[for="quantity"]');

            function updateQuantityLabel() {
                if (operationAdd.checked) {
                    quantityLabel.innerHTML = '<strong>Quantity to Add:</strong>';
                } else if (operationDeduct.checked) {
                    quantityLabel.innerHTML = '<strong>Quantity to Deduct:</strong>';
                }
            }

            // Initial label update
            updateQuantityLabel();

            // Event listeners for radio buttons
            operationAdd.addEventListener('change', updateQuantityLabel);
            operationDeduct.addEventListener('change', updateQuantityLabel);
        });

        // Barcode generation with spinning icon
        $('#generateBarcodeBtn').click(function() {
            var icon = $('#barcodeIcon');

            // Remove the class to reset the animation
            icon.removeClass('spin');

            // Trigger reflow to reset the animation
            void icon[0].offsetWidth;

            // Add the class to start the animation
            icon.addClass('spin');

            setTimeout(function() {
                var barcode = generateRandomBarcode();
                $('#barcodeNew').val(barcode);
                icon.removeClass('spin'); // Stop spinning after generating
            }, 600); // Duration should match CSS spin duration
        });

        // Function to generate random 11-digit barcode
        function generateRandomBarcode() {
            var barcode = '';
            for (var i = 0; i < 13; i++) {
                barcode += Math.floor(Math.random() * 10); // Random digit from 0 to 9
            }
            return barcode;
        }

        document.addEventListener('DOMContentLoaded', function() {
    const datatablesSimpleId = 'datatablesSimple1'; // ID for the DataTable
    let stockLogsTableInstance = null; // Variable to hold the DataTable instance

    var viewItemModal = document.getElementById('viewItemModal');

    viewItemModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget; // Button that triggered the modal

        // Extract data from data-* attributes
        var itemId = button.getAttribute('data-itemid');
        var itemName = button.getAttribute('data-itemname');
        var description = button.getAttribute('data-description');
        var brand = button.getAttribute('data-brand');
        var category = button.getAttribute('data-category');
        var qtyInStock = button.getAttribute('data-qtyinstock');
        var lowStockLimit = button.getAttribute('data-lowstocklimit');
        var unit = button.getAttribute('data-unit');
        var basePrice = button.getAttribute('data-baseprice');
        var sellingPrice = button.getAttribute('data-sellingprice');
        var expirationDate = button.getAttribute('data-expirationdate');
        var supplier = button.getAttribute('data-supplier');

        // Update the modal's content
        document.getElementById('modalItemNameView').textContent = itemName;
        document.getElementById('modalItemDescriptionView').textContent = description;
        document.getElementById('modalItemBrand').textContent = brand;
        document.getElementById('modalItemCategory').textContent = category;
        document.getElementById('modalQtyInStock').textContent = qtyInStock;
        document.getElementById('modalLowStockLimit').textContent = lowStockLimit;
        document.getElementById('modalUnit').textContent = unit;
        document.getElementById('modalBasePrice').textContent = basePrice;
        document.getElementById('modalSellingPriceView').textContent = sellingPrice;
        document.getElementById('modalExpirationDate').textContent = expirationDate;
        document.getElementById('modalSupplier').textContent = supplier;

        // Handle stock logs
        var stockLogs = JSON.parse(button.getAttribute('data-stocklogs'));
        var stockLogsTbody = document.getElementById('stockLogsTbody');

        // Clear previous stock logs
        stockLogsTbody.innerHTML = '';

        if (stockLogs && stockLogs.length > 0) {
            stockLogs.forEach(function(log, index) {
                var row = document.createElement('tr');
                var quantityChangeClass = log.quantity_change > 0 ? 'text-success' : 'text-danger';
                var quantityChangeFormatted = log.quantity_change > 0
                    ? `+${log.quantity_change}`
                    : log.quantity_change;

                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td class="${quantityChangeClass}">${quantityChangeFormatted}</td>
                    <td>${new Date(log.updated_at).toLocaleString()}</td>
                `;
                stockLogsTbody.appendChild(row);
            });
        } else {
            var row = document.createElement('tr');
            row.innerHTML = '<td colspan="3" class="text-center">No stock logs found.</td>';
            stockLogsTbody.appendChild(row);
        }

        // Destroy the previous DataTable instance if it exists
        if (stockLogsTableInstance) {
            stockLogsTableInstance.destroy();
            stockLogsTableInstance = null;
        }

        // Initialize the DataTable
        const stockLogsTable = document.getElementById(datatablesSimpleId);
        stockLogsTableInstance = new simpleDatatables.DataTable(stockLogsTable, {
            searchable: true,
            fixedHeight: false,
            perPage: 5,
            labels: {
                placeholder: "Search...",
                perPage: "entries per page",
                noRows: "No stock logs available",
                info: "Showing {start} to {end} of {rows} entries",
            },
        });
    });

    viewItemModal.addEventListener('hidden.bs.modal', function() {
        // Destroy the DataTable when the modal is closed
        if (stockLogsTableInstance) {
            stockLogsTableInstance.destroy();
            stockLogsTableInstance = null;
        }
    });
});



        // Handle when the modal is fully shown
        updateStockModal.addEventListener('shown.bs.modal', function() {
            document.getElementById('quantity').focus();
        });

        document.addEventListener('DOMContentLoaded', function() {
            // When the update stock modal is shown
            var updateStockModal = document.getElementById('updateStockModal');
            updateStockModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var itemId = button.getAttribute('data-itemid');
                var itemName = button.getAttribute('data-itemname');
                var basePrice = button.getAttribute('data-baseprice');
                var sellingPrice = button.getAttribute('data-sellingprice');
                var qtyInStock = button.getAttribute('data-qtyinstock');
                var modalTitle = document.getElementById('updateStockModalLabel'); // Modal title element
                operationAdd.checked = true;
                modalTitle.textContent = 'Update Stock';

                var expirationDate = button.getAttribute('data-expirationdate'); // Get the expiration date

                // Display expiration date field if the item has an expiration date
                if (expirationDate !== 'N/A') {
                    document.getElementById('expirationDateWrapper').style.display = 'block';
                    document.getElementById('newExpirationDateUpdate').value = expirationDate;
                } else {
                    document.getElementById('expirationDateWrapper').style.display = 'none';
                }


                // Update the modal's content
                document.getElementById('modalItemId').value = itemId;
                document.getElementById('modalAddItemName').textContent = itemName;
                document.getElementById('currentBasePrice').textContent = "₱" + basePrice;
                document.getElementById('currentSellingPrice').textContent = "₱" + sellingPrice;
                document.getElementById('currentQtyInStock').textContent = qtyInStock;

                // Reset the form to default state (Add operation)
                document.getElementById('operationAdd').checked = true;
                document.getElementById('quantity').value = '';
                var quantityLabel = document.querySelector('label[for="quantity"]');
                quantityLabel.innerHTML = '<strong>Quantity to Add:</strong>';

                // Ensure that fields for price increase are hidden initially
                document.getElementById('currentPriceFields').style.display = 'none';
                document.getElementById('priceFields').style.display = 'none';

                // Remove the 'required' attribute initially
                document.getElementById('newBasePrice').removeAttribute('required');
                document.getElementById('newSellingPrice').removeAttribute('required');
            });

            // Handle the "Yes" radio button for price updates
            const priceUpdateYes = document.getElementById('priceUpdateYes');
            const priceUpdateNo = document.getElementById('priceUpdateNo');
            const currentPriceFields = document.getElementById('currentPriceFields');
            const priceFields = document.getElementById('priceFields');
            const newBasePrice = document.getElementById('newBasePrice');
            const newSellingPrice = document.getElementById('newSellingPrice');
            const barcodeNew = document.getElementById('barcodeNew');
            const operationDeduct = document.getElementById('operationDeduct'); // Deduct radio button
            const operationAdd = document.getElementById('operationAdd');
            const modalTitle = document.getElementById('updateStockModalLabel'); // Modal title element

            // When the modal is hidden, ensure that the "No" option is selected in the "Increase Price Update" section
            var updateStockModal = document.getElementById('updateStockModal');
            updateStockModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('barcodeNew').value = null;
                document.getElementById('priceUpdateNo').checked = true; // Select "No" for price update
                // Hide the price fields if they are visible
                document.getElementById('currentPriceFields').style.display = 'none';
                document.getElementById('priceFields').style.display = 'none';
                document.getElementById('operationQuantityFields').style.display =
                    'block'; // Show operation and quantity fields
                // Remove the 'required' attribute from price fields
                document.getElementById('newBasePrice').removeAttribute('required');
                document.getElementById('newSellingPrice').removeAttribute('required');
                document.getElementById('barcodeNew').removeAttribute('required');
            });


            // Toggle the price update fields based on the "Yes/No" price update option
            priceUpdateYes.addEventListener('change', function() {
                if (priceUpdateYes.checked) {
                    currentPriceFields.style.display = 'block';
                    quantity.removeAttribute('required'); // Make "Quantity to Add" not required
                    priceFields.style.display = 'block';
                    newBasePrice.setAttribute('required', 'required');
                    newSellingPrice.setAttribute('required', 'required');
                    barcodeNew.setAttribute('required', 'required');
                    modalTitle.textContent = 'Update Price';
                    // Hide operation and quantity fields
                    document.getElementById('operationQuantityFields').style.display = 'none';
                    document.getElementById('newQtyInStock').setAttribute('required',
                        'required'); // Make new quantity required
                    newBasePrice.focus();
                }
            });

            priceUpdateNo.addEventListener('change', function() {
                if (priceUpdateNo.checked) {
                    currentPriceFields.style.display = 'none';
                    quantity.setAttribute('required', 'required'); // Make "Quantity to Add" required again
                    priceFields.style.display = 'none';
                    newBasePrice.removeAttribute('required');
                    newSellingPrice.removeAttribute('required');
                    barcodeNew.removeAttribute('required');
                    document.getElementById('barcodeNew').value = null;
                    modalTitle.textContent = 'Update Stock';
                    // Show operation and quantity fields
                    document.getElementById('operationQuantityFields').style.display = 'block';
                    document.getElementById('newQtyInStock').removeAttribute(
                        'required'); // Remove required from new quantity


                }
            });

            // Initialize visibility and 'required' attribute based on radio button state
            if (priceUpdateYes.checked) {
                currentPriceFields.style.display = 'block';
                priceFields.style.display = 'block';
                newBasePrice.setAttribute('required', 'required');
                newSellingPrice.setAttribute('required', 'required');
                barcodeNew.setAttribute('required', 'required');
                operationDeduct.disabled = true;
                modalTitle.textContent = 'Update Price';
            } else {
                currentPriceFields.style.display = 'none';
                priceFields.style.display = 'none';
                newBasePrice.removeAttribute('required');
                newSellingPrice.removeAttribute('required');
                barcodeNew.removeAttribute('required');
                modalTitle.textContent = 'Update Stock';
            }
        });

        // Handle the update stock form submit and dynamically update the table
        $('#updateStockForm').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();
            var itemId = $('#modalItemId').val();

            $.ajax({
                url: '/inventory/update-stock/' + itemId, // Update the URL as needed
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Update the table row with new data
                    var row = $('#item-' + itemId);
                    row.find('.base-price').text('₱' + response.item.base_price);
                    row.find('.selling-price').text('₱' + response.item.selling_price);
                    row.find('.quantity-in-stock').text(response.item.qtyInStock);
                    row.find('.barcode').text(response.item.barcode);

                    // Optionally, show a success message
                    alert('Item updated successfully!');
                    $('#updateStockModal').modal('hide'); // Close the modal
                },
                error: function(response) {
                    // Show error message if there was a problem
                    alert('There was an error updating the item.');
                }
            });
        });

        // Inside the 'show.bs.modal' event listener for Update Stock Modal
        updateStockModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var itemId = button.getAttribute('data-itemid');
            var itemName = button.getAttribute('data-itemname');
            var expirationDate = button.getAttribute('data-expirationdate'); // Current expiration date

            // Update the modal content
            document.getElementById('modalItemId').value = itemId;
            document.getElementById('modalAddItemName').textContent = itemName;

            // Display current expiration date and allow updating if the item is perishable
            var oldExpirationDateContainer = document.getElementById('oldExpirationDateContainer');
            var newExpirationDateContainer = document.getElementById('newExpirationDateContainer');

            if (expirationDate !== 'N/A') {
                oldExpirationDateContainer.style.display = 'block';
                document.getElementById('oldExpirationDate').textContent = expirationDate;
                newExpirationDateContainer.style.display = 'block';
            } else {
                oldExpirationDateContainer.style.display = 'none';
                newExpirationDateContainer.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const operationAdd = document.getElementById('operationAdd');
            const operationDeduct = document.getElementById('operationDeduct');
            const expirationDateWrapper = document.getElementById('expirationDateWrapper');

            // Function to toggle the visibility of the expiration date field
            function toggleExpirationDateField() {
                if (operationDeduct.checked) {
                    expirationDateWrapper.style.display = 'none'; // Hide if "Deduct" is selected
                } else {
                    expirationDateWrapper.style.display = 'block'; // Show if "Add" is selected
                }
            }

            // Initial call to set the correct state on page load
            toggleExpirationDateField();

            // Event listeners for operation radio buttons
            operationAdd.addEventListener('change', toggleExpirationDateField);
            operationDeduct.addEventListener('change', toggleExpirationDateField);
        });

        document.addEventListener('DOMContentLoaded', function() {
            var transferModal = document.getElementById('transferItemModal');
            transferModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var itemId = button.getAttribute('data-itemid');
                var itemName = button.getAttribute('data-itemname');
                var basePrice = button.getAttribute('data-baseprice');
                var sellingPrice = button.getAttribute('data-sellingprice');
                var qtyInStock = button.getAttribute('data-qtyinstock');

                // Update the modal's content
                document.getElementById('modalTransferItemId').value = itemId;
                document.getElementById('modalTransferItemName').textContent = itemName;
                document.getElementById('modalTransferQty').textContent = qtyInStock;
                document.getElementById('modalTransferBasePrice').textContent = parseFloat(basePrice)
                    .toFixed(2);
                document.getElementById('modalTransferSellingPrice').textContent = parseFloat(sellingPrice)
                    .toFixed(2);

                // Fetch higher-priced items for the dropdown
                fetch(`/inventory/get-higher-priced-items/${itemId}`)
                    .then(response => response.json())
                    .then(data => {
                        var targetItemDropdown = document.getElementById('targetItemDropdown');
                        targetItemDropdown.innerHTML =
                            '<option value="">Select an item with a higher price...</option>'; // Reset options

                        // Check if there are any higher-priced items
                        if (data.higherPricedItems.length > 0) {
                            // Populate the dropdown with higher-priced items
                            data.higherPricedItems.forEach(function(item) {
                                var option = document.createElement('option');
                                option.value = item.id;
                                option.textContent =
                                    `${item.item_name} - Base Price: ₱${parseFloat(item.base_price).toFixed(2)}, Selling Price: ₱${parseFloat(item.selling_price).toFixed(2)}, ${item.unit_of_measurement}`;
                                option.classList.add('text-success');
                                targetItemDropdown.appendChild(option);
                            });
                        } else {
                            // Add a message indicating no items are available
                            var option = document.createElement('option');
                            option.value = '';
                            option.classList.add('text-danger');
                            option.disabled = true; // Make the option unselectable
                            option.textContent = 'No items with a higher price found.';
                            targetItemDropdown.appendChild(option);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching higher-priced items:', error);
                    });

            });

            // Handle form submission
            document.getElementById('transferItemForm').onsubmit = function(event) {
                event.preventDefault(); // Prevent form submission
                var itemId = document.getElementById('modalTransferItemId').value; // Get the item ID
                var actionUrl = '{{ route('inventory.transferItem', ':id') }}'; // The route with placeholder
                actionUrl = actionUrl.replace(':id', itemId); // Replace the placeholder with actual item ID
                this.action = actionUrl; // Update the form's action URL
                this.submit(); // Submit the form
            };
        });

        @if ($errors->has('new_base_price') || $errors->has('new_selling_price'))
            document.addEventListener('DOMContentLoaded', function() {
                // Retrieve the modal item data passed from the controller
                var modalItem = @json(session('modalItem'));
                if (modalItem) {
                    // Populate the modal fields with the previous data
                    document.getElementById('modalItemId').value = modalItem.id;
                    document.getElementById('modalAddItemName').textContent = modalItem.item_name;
                    document.getElementById('currentBasePrice').textContent = '₱' + parseFloat(modalItem.base_price)
                        .toFixed(2);
                    document.getElementById('currentSellingPrice').textContent = '₱' + parseFloat(modalItem
                        .selling_price).toFixed(2);
                    document.getElementById('currentQtyInStock').textContent = modalItem.qtyInStock;

                    // If the item has an expiration date, display it
                    if (modalItem.expiration_date) {
                        document.getElementById('oldExpirationDateContainer').style.display = 'block';
                        document.getElementById('oldExpirationDate').textContent = new Date(modalItem
                            .expiration_date).toLocaleDateString();
                    } else {
                        document.getElementById('oldExpirationDateContainer').style.display = 'none';
                    }

                    // Set the price fields with old input values
                    if ('{{ old('price_update') }}' === 'yes') {
                        document.getElementById('priceUpdateYes').checked = true;
                        document.getElementById('newBasePrice').value = '{{ old('new_base_price') }}';
                        document.getElementById('newSellingPrice').value = '{{ old('new_selling_price') }}';
                        document.getElementById('barcodeNew').value = '{{ old('barcode_no') }}';
                        document.getElementById('newQtyInStock').value = '{{ old('new_qty_in_stock') }}';

                        // Show the price fields
                        document.getElementById('currentPriceFields').style.display = 'block';
                        document.getElementById('priceFields').style.display = 'block';
                        document.getElementById('operationQuantityFields').style.display = 'none';
                        document.getElementById('newBasePrice').setAttribute('required', 'required');
                        document.getElementById('newSellingPrice').setAttribute('required', 'required');
                        document.getElementById('barcodeNew').setAttribute('required', 'required');
                    }
                }

                // Initialize and show the modal
                var updateStockModal = new bootstrap.Modal(document.getElementById('updateStockModal'));
                updateStockModal.show();
            });
        @endif


        document.addEventListener('DOMContentLoaded', function() {
            var modifyExpirationDateModal = document.getElementById('modifyExpirationDateModal');
            modifyExpirationDateModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var itemId = button.getAttribute('data-itemid');
                var itemName = button.getAttribute('data-itemname');
                var currentExpirationDate = button.getAttribute('data-expirationdate');
                var currentQtyInStock = button.getAttribute('data-qtyinstock');

                // Update the modal's content
                document.getElementById('modalModifyItemId').value = itemId;
                document.getElementById('modalModifyItemName').textContent = itemName;

                // Optionally, set the current expiration date as a placeholder or prefill if desired
                document.getElementById('newExpirationDate').value = currentExpirationDate !== 'N/A' ?
                    currentExpirationDate : '';

                // Optionally, prefill the quantity_added field
                document.getElementById('quantity_added').value = '';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
    var updateStockModal = document.getElementById('updateStockModal');
    updateStockModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var itemId = button.getAttribute('data-itemid');
        var itemName = button.getAttribute('data-itemname');
        var basePrice = button.getAttribute('data-baseprice');
        var sellingPrice = button.getAttribute('data-sellingprice');
        var qtyInStock = button.getAttribute('data-qtyinstock');
        var expirationDate = button.getAttribute('data-expirationdate'); // Current expiration date

        // Update the modal's content
        document.getElementById('modalItemId').value = itemId;
        document.getElementById('modalAddItemName').textContent = itemName;

        // Display current prices and stock
        document.getElementById('currentBasePrice').textContent = "₱" + parseFloat(basePrice).toFixed(2);
        document.getElementById('currentSellingPrice').textContent = "₱" + parseFloat(sellingPrice).toFixed(2);
        document.getElementById('currentQtyInStock').textContent = qtyInStock;

        // Display expiration date field if the item has an expiration date
        var oldExpirationDateContainer = document.getElementById('oldExpirationDateContainer');
        if (expirationDate !== 'N/A') {
            oldExpirationDateContainer.style.display = 'block';
            document.getElementById('oldExpirationDate').textContent = expirationDate;
        } else {
            oldExpirationDateContainer.style.display = 'none';
        }

        // Reset the form to default state (Add operation)
        document.getElementById('operationAdd').checked = true;
        document.getElementById('quantity').value = '';
        var quantityLabel = document.querySelector('label[for="quantity"]');
        quantityLabel.innerHTML = '<strong>Quantity to Add:</strong>';
        
        // Ensure that fields for price increase are hidden initially
        document.getElementById('currentPriceFields').style.display = 'none';
        document.getElementById('priceFields').style.display = 'none';
    });

    // Handle the "Yes" radio button for price updates
    const priceUpdateYes = document.getElementById('priceUpdateYes');
    const priceUpdateNo = document.getElementById('priceUpdateNo');
    const currentPriceFields = document.getElementById('currentPriceFields');
    const priceFields = document.getElementById('priceFields');
    const newBasePrice = document.getElementById('newBasePrice');
    const newSellingPrice = document.getElementById('newSellingPrice');
    const barcodeNew = document.getElementById('barcodeNew');

    // Toggle the price update fields based on the "Yes/No" price update option
    priceUpdateYes.addEventListener('change', function() {
        if (priceUpdateYes.checked) {
            currentPriceFields.style.display = 'block';
            priceFields.style.display = 'block';
            newBasePrice.setAttribute('required', 'required');
            newSellingPrice.setAttribute('required', 'required');
            barcodeNew.setAttribute('required', 'required');
        }
    });

    priceUpdateNo.addEventListener('change', function() {
        if (priceUpdateNo.checked) {
            currentPriceFields.style.display = 'none';
            priceFields.style.display = 'none';
            newBasePrice.removeAttribute('required');
            newSellingPrice.removeAttribute('required');
            barcodeNew.removeAttribute('required');
        }
    });
});


    </script>

@endsection
