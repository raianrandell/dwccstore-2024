@extends('layout.inventory')

@section('title', 'Edit Item')

@section('content')
<!-- Breadcrumb -->
<div id="breadcrumb">
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.stockmanagement') }}">Stock Management</a></li>
        <li class="breadcrumb-item active">Edit Item</li>
    </ol>
</div>

<!-- Success and Error Messages -->
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-check-circle me-2 fa-lg"></i>
        <div>
            {{ Session::get('success') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(Session::has('fail'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
        <div>
            {{ Session::get('fail') }}
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Edit Item Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-edit me-1"></i>
        Edit Item
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.itemupdate', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <p class="text-danger">* Required Fields</p>

            <!-- First Row: Category, Barcode Number, Item Name, Description -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Category -->
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="category" name="category_id" required disabled>
                        <option value="" selected>Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (old('category_id', $item->cat_id) == $category->id) ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Barcode Number with Generate Button -->
                <div class="col-md-3">
                    <label for="barcode" class="form-label">Barcode Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('barcode_no') is-invalid @enderror" id="barcode" name="barcode_no" placeholder="Generate barcode number" maxlength="13" value="{{ old('barcode_no', $item->barcode) }}" readonly>
                        <button type="button" class="btn btn-outline-secondary bg-success text-white" id="generateBarcodeBtn" disabled>
                            <i class="fas fa-sync" id="barcodeIcon"></i>
                        </button>
                        @error('barcode_no')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <!-- Item Name -->
                    <label for="itemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" id="itemName" name="item_name" placeholder="Enter item name" required value="{{ old('item_name', $item->item_name) }}">
                    @error('item_name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <!-- Description -->
                    <label for="itemDescription" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="itemDescription" name="description" rows="3" placeholder="Enter item description">{{ old('description', $item->item_description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>  
            </div>

            <!-- Second Row: Brand, Quantity in Stock, Low Stock Limit, Unit -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Brand -->
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" placeholder="Enter brand name" value="{{ old('brand', $item->item_brand) }}">
                    @error('brand')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>   

                <div class="col-md-3">
                    <!-- Quantity -->
                    <label for="quantity" class="form-label">Quantity in Stock <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('quantity_in_stock') is-invalid @enderror" id="quantity" name="quantity_in_stock" placeholder="Enter quantity" required min="0" value="{{ old('quantity_in_stock', $item->qtyInStock) }}" @readonly(true)>
                    @error('quantity_in_stock')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>    

                <div class="col-md-3">
                    <!-- Low Stock Limit -->
                    <label for="lowStockLimit" class="form-label">Low Stock Limit <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('low_stock_limit') is-invalid @enderror" id="lowStockLimit" name="low_stock_limit" placeholder="Enter low stock limit" required min="0" value="{{ old('low_stock_limit', $item->low_stock_limit) }}">
                    @error('low_stock_limit')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>   

                <div class="col-md-3">
                    <!-- Unit -->
                    <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                        <option value="" selected>Select a unit</option>
                        @php
                           $units = [
                                'pcs' => 'Pieces (pcs)',
                                'kg' => 'Kilograms (kg)',
                                'g' => 'Grams (g)',
                                'mg' => 'Milligrams (mg)',
                                'lb' => 'Pounds (lb)',
                                'oz' => 'Ounces (oz)',
                                'l' => 'Liters (L)',
                                'ml' => 'Milliliters (ml)',
                                'm' => 'Meters (m)',
                                'cm' => 'Centimeters (cm)',
                                'mm' => 'Millimeters (mm)',
                                'in' => 'Inches (in)',
                                'ft' => 'Feet (ft)',
                                'yd' => 'Yards (yd)',
                                'dozen' => 'Dozen (dz)',
                                'box' => 'Box (bx)',
                                'pack' => 'Pack (pk)',
                                'set' => 'Set (st)',
                                'tsp' => 'Teaspoons (tsp)',
                                'tbsp' => 'Tablespoons (tbsp)',
                                'cup' => 'Cups',
                                'pt' => 'Pints (pt)',
                                'qt' => 'Quarts (qt)',
                                'cc' => 'Cubic Centimeters (cc)',
                                'm3' => 'Cubic Meters (m³)',
                                'ha' => 'Hectares (ha)',
                                'acre' => 'Acres',
                                'batch' => 'Batch',
                                'pair' => 'Pair',
                                'ream' => 'Ream',
                                'palette' => 'Palette',
                                'pad' => 'Pad'
                            ];
                        @endphp
                        @foreach($units as $key => $value)
                            <option value="{{ $key }}" {{ (old('unit', $item->unit_of_measurement) == $key) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>       
            </div>

            <!-- Third Row: Base Price, Selling Price, Color, Size -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Base Price -->
                    <label for="price" class="form-label">Base Price <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('base_price') is-invalid @enderror" id="price" name="base_price" placeholder="Enter base price" required min="0" step="0.01" value="{{ old('base_price', number_format($item->base_price, 2)) }}"
                    @readonly(true)>
                    @error('base_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <!-- Selling Price -->
                    <label for="sellingPrice" class="form-label">Selling Price <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('selling_price') is-invalid @enderror" id="sellingPrice" name="selling_price" placeholder="Enter selling price" required min="0" step="0.01"  value="{{ old('selling_price', number_format($item->selling_price, 2)) }}"  @readonly(true)>
                    @error('selling_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <!-- Color -->
                    <label for="color" class="form-label">Color</label>
                    @php
                       $colors = [
                        'black' => 'Black',
                        'white' => 'White',
                        'red' => 'Red',
                        'blue' => 'Blue',
                        'green' => 'Green',
                        'yellow' => 'Yellow',
                        'orange' => 'Orange',
                        'purple' => 'Purple',
                        'pink' => 'Pink',
                        'brown' => 'Brown',
                        'gray' => 'Gray',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'beige' => 'Beige',
                        'maroon' => 'Maroon',
                        'navy' => 'Navy',
                        'olive' => 'Olive',
                        'teal' => 'Teal',
                        'lime' => 'Lime',
                        'indigo' => 'Indigo',
                        // Add more colors as needed
                    ];

                    @endphp
                    <select class="form-select @error('color') is-invalid @enderror" id="color" name="color">
                        <option value="" selected>Select a color</option>
                        @foreach($colors as $key => $value)
                            <option value="{{ $key }}" {{ (old('color', $item->color) == $key) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                    @error('color')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <!-- Size -->
                    <label for="size" class="form-label">Size</label>
                    @php
                        $sizes = [
                            'XS' => 'Extra Small (XS)',
                            'S' => 'Small (S)',
                            'M' => 'Medium (M)',
                            'L' => 'Large (L)',
                            'XL' => 'Extra Large (XL)',
                            'XXL' => 'Double Extra Large (XXL)',
                            'XXXL' => 'Triple Extra Large (XXXL)',
                        ];

                    @endphp
                    <select class="form-select @error('size') is-invalid @enderror" id="size" name="size">
                        <option value="" selected>Select a size</option>
                        @foreach($sizes as $key => $value)
                            <option value="{{ $key }}" {{ (old('size', $item->size) == $key) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>                    
                    @error('size')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Fourth Row: Weight, Is Perishable?, Expiration Date -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Weight -->
                    <label for="weight" class="form-label">Weight</label>
                    <input type="number" step="any" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" placeholder="Enter weight" value="{{ old('weight', $item->weight) }}">
                    @error('weight')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-2">
                    <!-- Is Perishable? -->
                    <label class="form-label">Is Perishable?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_perishable" id="perishableNo" value="0" required {{ old('is_perishable', $item->expiration_date ? '1' : '0') == '0' ? 'checked' : '' }} onclick="toggleExpirationDate(false)">
                        <label class="form-check-label" for="perishableNo">
                            No
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_perishable" id="perishableYes" value="1" required {{ old('is_perishable', $item->expiration_date ? '1' : '0') == '1' ? 'checked' : '' }} onclick="toggleExpirationDate(true)">
                        <label class="form-check-label" for="perishableYes">
                            Yes
                        </label>
                    </div>
                    @error('is_perishable')
                        <div class="text-danger">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-3" id="expirationDateContainer" style="display: none;">
                    <!-- Expiration Date -->
                    <label for="expirationDate" class="form-label">Expiration Date&nbsp;&nbsp;<span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('expiration_date') is-invalid @enderror" id="expirationDate" name="expiration_date" value="{{ old('expiration_date', $item->expiration_date ? \Carbon\Carbon::parse($item->expiration_date)->format('Y-m-d') : '') }}" readonly>
                    @error('expiration_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <!-- Optional Column -->
                </div>
            </div>
            <!-- Buttons Row -->
            <div class="row mb-2">
                <div class="col-md-12 text-end">
                    <!-- Buttons -->
                    <a href="{{ route('inventory.stockmanagement') }}" class="btn btn-secondary">Cancel</a>&nbsp;
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include necessary scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.0/JsBarcode.all.min.js"></script>
<!-- Include jQuery before Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<style>
    .spin {
        animation: spin 0.6s linear;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize Select2 on select elements
        $('#category, #size, #color, #unit').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Toggle expiration date visibility based on Is Perishable selection
        function toggleExpirationDate() {
            if ($('input[name="is_perishable"]:checked').val() == '1') {
                $('#expirationDateContainer').show();
            } else {
                $('#expirationDateContainer').hide();
                $('#expirationDate').val(''); // Clear the expiration date if not perishable
            }
        }

        // Initial check on page load
        toggleExpirationDate();

        // Event listener for radio buttons
        $('input[name="is_perishable"]').change(function() {
            toggleExpirationDate();
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
                $('#barcode').val(barcode);
                icon.removeClass('spin'); // Stop spinning after generating
            }, 600); // Duration should match CSS spin duration
        });
    });

    // Function to generate random 11-digit barcode
    function generateRandomBarcode() {
        var barcode = '';
        for (var i = 0; i < 13; i++) {
            barcode += Math.floor(Math.random() * 10); // Random digit from 0 to 9
        }
        return barcode;
    }

    function toggleExpirationDate(isPerishable) {
        const expirationDateContainer = document.getElementById('expirationDateContainer');
        const expirationDateInput = document.getElementById('expirationDate');
        
        if (isPerishable) {
            expirationDateContainer.style.display = 'block';
            expirationDateInput.setAttribute('required', true);
        } else {
            expirationDateContainer.style.display = 'none';
            expirationDateInput.removeAttribute('required');
            expirationDateInput.value = ''; // Clear the date if No is selected
        }
    }

    // Check on page load in case the old value is persisted
    window.onload = function() {
        const isPerishable = document.querySelector('input[name="is_perishable"]:checked').value;
        toggleExpirationDate(isPerishable === '1');
    };
    
</script>
@endsection
