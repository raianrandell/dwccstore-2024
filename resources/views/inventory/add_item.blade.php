@extends ('layout.inventory')

@section('title', 'Add New Item')

@section('content')
<!-- Breadcrumb -->
<div id="breadcrumb">
    <ol class="breadcrumb mb-3 mt-5">
        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventory.stockmanagement') }}">Stock Management</a></li>
        <li class="breadcrumb-item active">Add New Item</li>
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

<!-- Add New Item Section -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-plus-circle me-1"></i>
        Add New Item
    </div>
    <div class="card-body">
        <form action="{{ route('inventory.itemstore') }}" method="POST"> 
            @csrf
            <p class="text-danger">* Required Fields</p>
            
            <!-- First Row: Category, Barcode Number, Item Name, Description -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Category -->
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="category" name="category_id" required>
                        <option value="" selected>Select Category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Barcode Number with Generate Button -->
                <div class="col-md-3">
                    <label for="barcode" class="form-label">Barcode Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control @error('barcode_no') is-invalid @enderror" id="barcode" name="barcode_no" placeholder="Generate barcode number" maxlength="13" value="{{ old('barcode_no') }}">
                        <button type="button" class="btn btn-outline-secondary bg-success text-white" id="generateBarcodeBtn">
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
                    <input type="text" class="form-control @error('item_name') is-invalid @enderror" id="itemName" name="item_name" placeholder="Enter item name" required value="{{ old('item_name') }}">
                    @error('item_name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <!-- Description -->
                    <label for="itemDescription" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="itemDescription" name="description" rows="3" placeholder="Enter item description">{{ old('description') }}</textarea>
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
                    <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" placeholder="Enter brand name" value="{{ old('brand') }}">
                    @error('brand')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>   
                
                <div class="col-md-3">
                    <!-- Quantity -->
                    <label for="quantity" class="form-label">Quantity in Stock <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('quantity_in_stock') is-invalid @enderror" id="quantity" name="quantity_in_stock" placeholder="Enter quantity" required min="0" value="{{ old('quantity_in_stock') }}">
                    @error('quantity_in_stock')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>    

                <div class="col-md-3">
                    <!-- Low Stock Limit -->
                    <label for="lowStockLimit" class="form-label">Low Stock Limit <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('low_stock_limit') is-invalid @enderror" id="lowStockLimit" name="low_stock_limit" placeholder="Enter low stock limit" required min="0" value="{{ old('low_stock_limit') }}">
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
                        <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                        <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Grams (g)</option>
                        <option value="mg" {{ old('unit') == 'mg' ? 'selected' : '' }}>Milligrams (mg)</option>
                        <option value="lb" {{ old('unit') == 'lb' ? 'selected' : '' }}>Pounds (lb)</option>
                        <option value="oz" {{ old('unit') == 'oz' ? 'selected' : '' }}>Ounces (oz)</option>
                        <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>Liters (L)</option>
                        <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                        <option value="m" {{ old('unit') == 'm' ? 'selected' : '' }}>Meters (m)</option>
                        <option value="cm" {{ old('unit') == 'cm' ? 'selected' : '' }}>Centimeters (cm)</option>
                        <option value="mm" {{ old('unit') == 'mm' ? 'selected' : '' }}>Millimeters (mm)</option>
                        <option value="in" {{ old('unit') == 'in' ? 'selected' : '' }}>Inches (in)</option>
                        <option value="ft" {{ old('unit') == 'ft' ? 'selected' : '' }}>Feet (ft)</option>
                        <option value="yd" {{ old('unit') == 'yd' ? 'selected' : '' }}>Yards (yd)</option>
                        <option value="dozen" {{ old('unit') == 'dozen' ? 'selected' : '' }}>Dozen (dz)</option>
                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box (bx)</option>
                        <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack (pk)</option>
                        <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Set (st)</option>
                        <option value="tsp" {{ old('unit') == 'tsp' ? 'selected' : '' }}>Teaspoons (tsp)</option>
                        <option value="tbsp" {{ old('unit') == 'tbsp' ? 'selected' : '' }}>Tablespoons (tbsp)</option>
                        <option value="cup" {{ old('unit') == 'cup' ? 'selected' : '' }}>Cups</option>
                        <option value="pt" {{ old('unit') == 'pt' ? 'selected' : '' }}>Pints (pt)</option>
                        <option value="qt" {{ old('unit') == 'qt' ? 'selected' : '' }}>Quarts (qt)</option>
                        <option value="cc" {{ old('unit') == 'cc' ? 'selected' : '' }}>Cubic Centimeters (cc)</option>
                        <option value="m3" {{ old('unit') == 'm3' ? 'selected' : '' }}>Cubic Meters (m³)</option>
                        <option value="ha" {{ old('unit') == 'ha' ? 'selected' : '' }}>Hectares (ha)</option>
                        <option value="acre" {{ old('unit') == 'acre' ? 'selected' : '' }}>Acres</option>
                        <option value="batch" {{ old('unit') == 'batch' ? 'selected' : '' }}>Batch</option>
                        <option value="pair" {{ old('unit') == 'pair' ? 'selected' : '' }}>Pair</option>
                        <option value="ream" {{ old('unit') == 'ream' ? 'selected' : '' }}>Ream</option>
                        <option value="palette" {{ old('unit') == 'palette' ? 'selected' : '' }}>Palette</option>
                        <option value="pad" {{ old('unit') == 'pad' ? 'selected' : '' }}>Pad</option>
                        <!-- Add more units as needed -->
                    </select>
                    @error('unit')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>       
            </div>

            <!-- Third Row: Price, Selling Price, Color, Size -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Base Price -->
                    <label for="price" class="form-label">Base Price <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('base_price') is-invalid @enderror" id="price" name="base_price" placeholder="Enter base price" required min="0" step="0.01" value="{{ old('base_price') }}">
                    @error('base_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <!-- Selling Price -->
                    <label for="sellingPrice" class="form-label">Selling Price <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('selling_price') is-invalid @enderror" id="sellingPrice" name="selling_price" placeholder="Enter selling price" required min="0" step="0.01" value="{{ old('selling_price') }}">
                    @error('selling_price')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <!-- Color -->
                    <label for="color" class="form-label">Color</label>
                    <select class="form-select @error('color') is-invalid @enderror" id="color" name="color">
                        <option value="" selected>Select a color</option>
                        <option value="black" {{ old('color') == 'black' ? 'selected' : '' }}>Black</option>
                        <option value="white" {{ old('color') == 'white' ? 'selected' : '' }}>White</option>
                        <option value="red" {{ old('color') == 'red' ? 'selected' : '' }}>Red</option>
                        <option value="blue" {{ old('color') == 'blue' ? 'selected' : '' }}>Blue</option>
                        <option value="green" {{ old('color') == 'green' ? 'selected' : '' }}>Green</option>
                        <option value="yellow" {{ old('color') == 'yellow' ? 'selected' : '' }}>Yellow</option>
                        <option value="orange" {{ old('color') == 'orange' ? 'selected' : '' }}>Orange</option>
                        <option value="purple" {{ old('color') == 'purple' ? 'selected' : '' }}>Purple</option>
                        <option value="pink" {{ old('color') == 'pink' ? 'selected' : '' }}>Pink</option>
                        <option value="brown" {{ old('color') == 'brown' ? 'selected' : '' }}>Brown</option>
                        <option value="gray" {{ old('color') == 'gray' ? 'selected' : '' }}>Gray</option>
                        <option value="silver" {{ old('color') == 'silver' ? 'selected' : '' }}>Silver</option>
                        <option value="gold" {{ old('color') == 'gold' ? 'selected' : '' }}>Gold</option>
                        <option value="beige" {{ old('color') == 'beige' ? 'selected' : '' }}>Beige</option>
                        <option value="maroon" {{ old('color') == 'maroon' ? 'selected' : '' }}>Maroon</option>
                        <option value="navy" {{ old('color') == 'navy' ? 'selected' : '' }}>Navy</option>
                        <option value="olive" {{ old('color') == 'olive' ? 'selected' : '' }}>Olive</option>
                        <option value="teal" {{ old('color') == 'teal' ? 'selected' : '' }}>Teal</option>
                        <option value="lime" {{ old('color') == 'lime' ? 'selected' : '' }}>Lime</option>
                        <option value="indigo" {{ old('color') == 'indigo' ? 'selected' : '' }}>Indigo</option>
                        <!-- Add more colors as needed -->
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
                    <select class="form-select @error('size') is-invalid @enderror" id="size" name="size">
                        <option value="" selected>Select a size</option>
                        <option value="XS" {{ old('size') == 'XS' ? 'selected' : '' }}>Extra Small (XS)</option>
                        <option value="S" {{ old('size') == 'S' ? 'selected' : '' }}>Small (S)</option>
                        <option value="M" {{ old('size') == 'M' ? 'selected' : '' }}>Medium (M)</option>
                        <option value="L" {{ old('size') == 'L' ? 'selected' : '' }}>Large (L)</option>
                        <option value="XL" {{ old('size') == 'XL' ? 'selected' : '' }}>Extra Large (XL)</option>
                        <option value="XXL" {{ old('size') == 'XXL' ? 'selected' : '' }}>Double Extra Large (XXL)</option>
                        <option value="XXXL" {{ old('size') == 'XXXL' ? 'selected' : '' }}>Triple Extra Large (XXXL)</option>
                    </select>                    
                    @error('size')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Fourth Row: Weight, Is Perishable?, Expiration Date, (Optional Column) -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <!-- Weight -->
                    <label for="weight" class="form-label">Weight</label>
                    <input type="number" step="any" class="form-control @error('weight') is-invalid @enderror" id="weight" name="weight" placeholder="Enter weight" value="{{ old('weight') }}">
                    @error('weight')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <!-- Supplier -->
                    <label for="supplier" class="form-label">Supplier</label>
                    <input type="text" class="form-control @error('supplier_info') is-invalid @enderror" id="supplier" name="supplier_info" placeholder="Enter supplier name" value="{{ old('supplier') }}">
                    @error('supplier_info')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="col-md-2">
                    <!-- Is Perishable? -->
                    <label class="form-label">Is Perishable?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_perishable" id="perishableNo" value="0" required {{ old('is_perishable', '0') == '0' ? 'checked' : '' }} onclick="toggleExpirationDate(false)">
                        <label class="form-check-label" for="perishableNo">
                            No
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_perishable" id="perishableYes" value="1" required {{ old('is_perishable') == '1' ? 'checked' : '' }} onclick="toggleExpirationDate(true)">
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
                
                <div class="col-md-2" id="expirationDateContainer" style="display: none;">
                    <!-- Expiration Date -->
                    <label for="expirationDate" class="form-label">Expiration Date&nbsp;&nbsp;<span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('expiration_date') is-invalid @enderror" id="expirationDate" name="expiration_date" value="{{ old('expiration_date') }}">
                    @error('expiration_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <!-- Optional Column: You can add additional fields here or leave it empty -->
                </div>
            </div>

            <!-- Buttons Row -->
            <div class="row mb-2">
                <div class="col-md-12 text-end">
                    <!-- Buttons -->
                    <a href="{{ route('inventory.stockmanagement') }}" class="btn btn-secondary">Back</a>&nbsp;
                    <button type="submit" class="btn btn-primary">Save Item</button>
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
                // If you have a function to generate the barcode image, call it here
                // generateBarcode(barcode);
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
