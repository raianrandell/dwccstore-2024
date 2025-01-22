<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Section;
use App\Models\Category;
use Carbon\Carbon;
use App\Models\Item;
use App\Models\TotalItemReport;
use App\Models\DamageTransaction;
use App\Models\ItemLog;
use Illuminate\Support\Facades\DB;
use Session;
use PDF;
use App\Exports\TotalItemReportExport;
use App\Exports\DamageItemReportExport;
use App\Exports\ExpiredItemReportExport;
use App\Exports\LowStockItemReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\InventoryLog;
use App\Models\ExpirationDateChange;
use App\Models\StockLog;
use App\Models\TransferItemLogs;
use App\Models\ExpiredItem;
use App\Models\ModifiedExpirationDateLog;
use App\Models\ItemForRent;
use App\Models\Services;

class InventoryController extends Controller
{
    // Show the login form
    public function inventorylogin()
    {
        return view("inventory.inventory_login");
    }

    public function stockManagement()
    {
        // Fetch all items with their related category
        $items = Item::with(['category', 'stockLogs'])->get();
    
        // Fetch all categories to populate the dropdown
        $categories = Category::orderBy('category_name', 'ASC')->get();
    
        // Get the authenticated user ID and full name
        $userId = Auth::guard('inventory')->user()->id;
        $userName = Auth::guard('inventory')->user()->full_name;
    
        // Log the success message if it exists
        if (Session::has('success')) {
            InventoryLog::create([
                'message' => Session::get('success'),
                'type' => 'success',
                'user_id' => $userId, // Store the user's ID
                'manage_by' => $userName, // Store the user's full name
            ]);
        }
    
        // Log the failure message if it exists
        if (Session::has('fail')) {
            InventoryLog::create([
                'message' => Session::get('fail'),
                'type' => 'fail',
                'user_id' => $userId, // Store the user's ID
                'manage_by' => $userName, // Store the user's full name
            ]);
        }
    
        // Pass items and categories to the view
        return view('inventory.stock_management', compact('items', 'categories'));
    }

    // Authenticate the inventory user
    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // Validate the request inputs
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Check if user exists with the username
        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            return back()->with('fail', 'No account found with the provided username.');
        }

        // Check if the user has the 'Admin' role
        if ($user->user_role !== 'Inventory') {
            return back()->with('fail', 'You are not authorized to access this section.');
        } else if ($user->user_status !== 'Active') {
            return back()->with('fail', 'This account is inactive, please contact the administrator.');
        }

        if (Auth::guard('inventory')->attempt($credentials)) {
            return redirect()->route('inventory.dashboard')->with([
                'success' => 'Login Successful',
                'full_name' => $user->full_name
            ]);
        }

        return back()->with('fail', 'The password is incorrect.');
    }

    public function inventoryDashboard()
    {
        $fullName = session('full_name'); // Get from session
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $lowStockItems = Item::where('status', 'Low Stock')->where('qtyInStock', '>', 0)->count();
        $damageItemsByCategory = DamageTransaction::selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();
    
        return view('inventory.inventory_dashboard', compact('totalItems', 'totalCategories', 'lowStockItems', 'damageItemsByCategory'));
    }
    

    public function updateItem(Request $request, $id)
    {
        // Find the item by ID
        $item = Item::findOrFail($id);
    
        // Validate the request inputs
        $validatedData = $request->validate([
            'barcode_no' => 'nullable|string|max:13|unique:items,barcode,' . $item->id,
            'item_name' => 'required|string|max:100',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'low_stock_limit' => 'required|integer|min:0',
            'unit' => 'required|string|max:45',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'is_perishable' => 'required|boolean',
            'supplier_info' => 'nullable|string|max:255',
            'expiration_date' => 'nullable|date|after:today|required_if:is_perishable,1',
        ], [
            'expiration_date.required_if' => 'Expiration date is required for perishable items.',
        ]);
    
        // Handle perishable items
        if ($validatedData['is_perishable'] && empty($validatedData['expiration_date'])) {
            return back()->withErrors(['expiration_date' => 'Expiration date is required for perishable items.'])->withInput();
        }
    
        // Update the item's attributes
        $item->barcode = $validatedData['barcode_no'] ?? $item->barcode;
        $item->item_name = $validatedData['item_name'];
        $item->item_description = $validatedData['description'];
        $item->item_brand = $validatedData['brand'];
        $item->low_stock_limit = $validatedData['low_stock_limit'];
        $item->unit_of_measurement = $validatedData['unit'];
        $item->color = $validatedData['color'];
        $item->size = $validatedData['size'];
        $item->weight = $validatedData['weight'];
        $item->is_perishable = $validatedData['is_perishable'];
        $item->supplier_info = $validatedData['supplier_info'];
    
        // Handle expiration date
        if ($validatedData['is_perishable']) {
            $item->expiration_date = $validatedData['expiration_date'];
        } else {
            $item->expiration_date = null;
        }
    
        // Optionally, update the status based on low stock limit
        if ($item->qtyInStock == 0) {
            $item->status = 'Out of Stock';
        } elseif ($item->qtyInStock <= $item->low_stock_limit) {
            $item->status = 'Low Stock';
        } else {
            $item->status = 'In Stock';
        }
    
        // Save the updated item
        $item->save();
    
        InventoryLog::create([
            'message' => 'Item updated: ' . $item->item_name,
            'type' => 'update',
            'user_id' => Auth::guard('inventory')->user()->id,
            'manage_by' => Auth::guard('inventory')->user()->full_name,
        ]);
    
        // Redirect back with a success message
        return redirect()->route('inventory.stockmanagement')->with('success', 'Item updated successfully.');
    }

    // Inventory-specific logout function
    public function inventorylogout(Request $request)
    {
         // Get the logged-in inventory's ID
         $userId = Auth::guard('inventory')->user()->id;
        
         if ($userId) {
             // Log the "Not Active" status in the user logs table
             \DB::table('user_logs')->insert([
                 'user_id' => $userId, // The ID of the logged-in user
                 'activity' => 'Offline', // Activity description
                 'ip_address' => $request->ip(), // Capture the user's IP address
                 'created_at' => now(), // Record the current timestamp
                 'updated_at' => now(),
             ]);
         }

        Auth::logout(); // Optional if you want a full logout

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inventory.login')->with('success', 'You have been logged out successfully.');
    } 

    public function categoryManagement()
    {
        // Fetch all categories with their associated sections
        $categories = Category::with('section')->get();

        // Fetch all sections to populate the dropdown
        $sections = Section::all();

        // Return the view with categories and sections data
        return view('inventory.category_management', compact('categories', 'sections'));
    }   

    public function sectionManagement(){

        // Fetch all sections, possibly with pagination
        $sections = Section::all();

        // Return the view with sections data
        return view('inventory.section_management', compact('sections'));
    }

    public function addSections(Request $request)
    {
        $validated = $request->validate([
            'sec_name' => [
                'required',
                'string',
                'max:255',
                'unique:sections,sec_name',
                'regex:/^[a-zA-Z0-9\s]+$/', // Allow only letters, numbers, and spaces
            ],
        ], [
            'sec_name.required' => 'The section name is required.',
            'sec_name.unique' => 'This section name already exists.',
            'sec_name.regex' => 'The section name cannot contain special characters.',
        ]);
        

        // Create a new section
        Section::create([
            'sec_name' => $validated['sec_name'],
            'created_at' => now()->toDateString(),
        ]);

        // Redirect back with a success message
        return redirect()->route('inventory.sectionmanagement')->with('success', 'Section added successfully.');
    }

    public function addCategory(Request $request)
    {
        // Validate the request
        $request->validate([
            'sec_id' => 'required|exists:sections,id', // Validate that the section exists
            'category_name' => 'required|string|max:100|unique:categories,category_name', // Ensure the category name is unique
            'stock_num' => 'required|string|max:50', // Ensure stock number is not null
        ],[
            'category_name.unique' => 'This category name already exists.',
        ]
    );
    
        // Create a new category
        Category::create([
            'sec_id' => $request->sec_id, // Use the correct foreign key
            'category_name' => $request->category_name,
            'stock_no' => $request->stock_num,
            'created_at' => now(), // Use Carbon's now() for current timestamp
        ]);
    
        // Redirect back with success message
        return redirect()->back()->with('success', 'Category added successfully.');
    }

    public function showCategoryItems($categoryId)
    {
        $category = Category::with('items')->findOrFail($categoryId);
        return view('inventory.category_items', compact('category'));
    }

    public function addItem()
    {
        $categories = Category::orderBy('category_name', 'ASC')->get();
        return view('inventory.add_item', compact('categories'));
    }
    public function itemsStore(Request $request)
    {
        // Validate the request inputs
        $validatedData = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'barcode_no' => 'nullable|string|max:255|unique:items,barcode',
            'item_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'quantity_in_stock' => 'required|integer|min:0',
            'low_stock_limit' => 'required|integer|min:0', // Added validation for Low Stock Limit
            'unit' => 'required|string|max:45',
            'base_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'is_perishable' => 'required|boolean',
            'expiration_date' => 'nullable|date|after:today',
            'supplier_info' => 'nullable|string|max:255',
        ]);
    
        // Handle perishable items
        if ($validatedData['is_perishable'] && empty($validatedData['expiration_date'])) {
            return back()->withErrors(['expiration_date' => 'Expiration date is required for perishable items.'])->withInput();
        }
    
        // Determine the status based on stock levels
        $status = 'In Stock'; // Default status
        if ($validatedData['quantity_in_stock'] <= $validatedData['low_stock_limit']) {
            $status = 'Low Stock';
        }else if($validatedData['quantity_in_stock'] == 0){
            $status = 'Out of Stock';
        }
    
        // Create the new item
        Item::create([
            'cat_id' => $validatedData['category_id'],
            'barcode' => $validatedData['barcode_no'],
            'item_name' => $validatedData['item_name'],
            'item_description' => $validatedData['description'],
            'item_brand' => $validatedData['brand'],
            'qtyInStock' => $validatedData['quantity_in_stock'],
            'low_stock_limit' => $validatedData['low_stock_limit'], // Store Low Stock Limit
            'unit_of_measurement' => $validatedData['unit'],
            'base_price' => $validatedData['base_price'],
            'selling_price' => $validatedData['selling_price'],
            'expiration_date' => $validatedData['is_perishable'] ? $validatedData['expiration_date'] : null,
            'supplier_info' => $validatedData['supplier_info'], // Store supplier info
            'status' => $status, // Set status based on stock levels
            'size' => $validatedData['size'],
            'color' => $validatedData['color'],
            'weight' => $validatedData['weight'],
        ]);
    
        // Redirect back with a success message
        return redirect()->route('inventory.stockmanagement')->with('success', 'Item added successfully.');
    }

    public function editItem($id)
    {
        $item = Item::findOrFail($id);
        $categories = Category::all();

        return view('inventory.edit_item', compact('item', 'categories'));
    }
    public function updateStock(Request $request, $id)
    {
        // Validate the request inputs
        $validatedData = $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'operation' => 'nullable|in:add,deduct',
            'price_update' => 'required|in:yes,no',
            'new_base_price' => 'nullable|numeric|min:0',
            'new_selling_price' => 'nullable|numeric|min:0',
            'barcode_no' => 'nullable|string|max:255|unique:items,barcode,' . $id,
            'new_qty_in_stock' => 'nullable|integer|min:0',
            'new_expiration_date' => 'nullable|date|after:today',
            'new_expiration_date_update' => 'nullable|date|after:today',
        ]);
    
        // Find the item
        $item = Item::findOrFail($id);
    
        // Validation for price mismatch
        if ($validatedData['price_update'] === 'yes') {
            if (
                isset($validatedData['new_base_price']) && $validatedData['new_base_price'] == $item->base_price ||
                isset($validatedData['new_selling_price']) && $validatedData['new_selling_price'] == $item->selling_price
            ) {
                echo "<script>alert('The new base price or selling price must be different from the old prices. Please try again.'); window.history.back();</script>";
                exit;
            }
        }
    
        // Store the old expiration date before making any changes
        $oldExpirationDate = $item->expiration_date;
    
        // Check if both quantity is added and expiration date is modified
        $addedQuantityAndModifiedExpiration = $validatedData['operation'] === 'add' && isset($validatedData['new_expiration_date_update']);
    
        // Update the item expiration date if provided
        if (!empty($validatedData['new_expiration_date_update'])) {
            $modifiedBy = Auth::guard('inventory')->user()->full_name;
            $itemName = $item->item_name;
    
            // Save the old and new expiration date in a new table
            ExpirationDateChange::create([
                'item_id' => $item->id,
                'item_name' => $itemName,
                'old_expiration_date' => $oldExpirationDate,
                'new_expiration_date' => $validatedData['new_expiration_date_update'],
                'modified_by' => $modifiedBy,
            ]);
    
            // Update the item's expiration date
            $item->expiration_date = $validatedData['new_expiration_date_update'];
        }
    
        // Get the authenticated user
        $user = Auth::guard('inventory')->user()->full_name;
    
        if ($validatedData['price_update'] === 'yes') {
            // Create a new item entry with updated base price, selling price, and barcode
            $newItem = $item->replicate(); // Clone the existing item
            $newItem->base_price = $validatedData['new_base_price'];
            $newItem->selling_price = $validatedData['new_selling_price'];
            $newItem->barcode = $validatedData['barcode_no'];
            $newItem->qtyInStock = $validatedData['new_qty_in_stock'];
            $newItem->expiration_date = $validatedData['new_expiration_date'] ?? $item->expiration_date;
            $newItem->created_at = now();
    
            // Set the new item's status based on stock levels
            if ($newItem->qtyInStock == 0) {
                $newItem->status = 'Out of Stock';
            } elseif ($newItem->qtyInStock <= $item->low_stock_limit) {
                $newItem->status = 'Low Stock';
            } else {
                $newItem->status = 'In Stock';
            }
    
            $newItem->save();
    
            // Log the changes in an item log table
            ItemLog::create([
                'item_id' => $item->id,
                'new_item_id' => $newItem->id,
                'item_name' => $item->item_name,
                'old_base_price' => $item->base_price,
                'new_base_price' => $validatedData['new_base_price'],
                'old_selling_price' => $item->selling_price,
                'new_selling_price' => $validatedData['new_selling_price'],
                'old_qty_in_stock' => $item->qtyInStock,
                'new_qty_in_stock' => $validatedData['new_qty_in_stock'],
                'old_barcode' => $item->barcode,
                'new_barcode' => $validatedData['barcode_no'],
                'old_expiration_date' => $item->expiration_date,
                'new_expiration_date' => $validatedData['new_expiration_date'] ?? $item->expiration_date,
                'user_id' => Auth::guard('inventory')->user()->id,
                'update_by' => Auth::guard('inventory')->user()->full_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            return redirect()->route('inventory.stockmanagement')->with('success', 'Item updated successfully and new variant created.');
        } else {
            // Initialize the success message
            $operationMessage = '';
    
            // Define $quantityChange based on the operation
            if ($validatedData['operation'] === 'add') {
                $quantityChange = $validatedData['quantity'];
                $item->qtyInStock += $validatedData['quantity']; // Add quantity
                $operationMessage = "Successfully added {$validatedData['quantity']} quantity stock to {$item->item_name}.";
            } elseif ($validatedData['operation'] === 'deduct') {
                if ($validatedData['quantity'] > $item->qtyInStock) {
                    return back()->with('fail', 'Cannot deduct more than the current stock.');
                }
                $quantityChange = -$validatedData['quantity']; // Use negative value for deduction
                $item->qtyInStock -= $validatedData['quantity']; // Deduct quantity
                $operationMessage = "Successfully deducted {$validatedData['quantity']} quantity stock from {$item->item_name}.";
            }
    
            // Update status based on new stock levels
            if ($item->qtyInStock == 0) {
                $item->status = 'Out of Stock';
            } elseif ($item->qtyInStock <= $item->low_stock_limit) {
                $item->status = 'Low Stock';
            } else {
                $item->status = 'In Stock';
            }
    
            $item->save();
    
            // Log the stock change with timestamps
            StockLog::create([
                'item_id' => $item->id,
                'quantity_change' => $quantityChange,
                'update_by' => Auth::guard('inventory')->user()->full_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Return the appropriate success message
            if ($addedQuantityAndModifiedExpiration) {
                return redirect()->route('inventory.stockmanagement')->with('success', "Successfully added {$validatedData['quantity']} quantity to item {$item->item_name} and updated expiration date.");
            } else {
                return redirect()->route('inventory.stockmanagement')->with('success', $operationMessage);
            }
        }
    }
    
    

    public function getSectionCategories($id)
    {
        // Fetch the section by ID
        $section = Section::find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        // Eager load categories
        $categories = $section->categories()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'section' => [
                'sec_name' => $section->sec_name,
                'created_at' => $section->created_at,
            ],
            'categories' => $categories->map(function($category) {
                return [
                    'category_name' => $category->category_name,
                    'stock_no' => $category->stock_no,
                    'created_at' => $category->created_at,
                ];
            }),
        ]);
    }

public function getCategoryItems($categoryId)
{
    // Fetch the category with its section and items
    $category = Category::with(['section', 'items'])->find($categoryId);

    if (!$category) {
        return response()->json(['message' => 'Category not found.'], 404);
    }

    // Prepare the items data
    $items = $category->items->map(function($item, $index) {
        return [
            'index' => $index + 1,
            'item_name' => $item->item_name,
            'barcode' => $item->barcode,
            'qtyInStock' => $item->qtyInStock,
        ];
    });

    // Return the data as JSON
    return response()->json([
        'category_name' => $category->category_name,
        'section_name' => $category->section->sec_name,
        'created_at' => \Carbon\Carbon::parse($category->created_at)->format('m-d-Y'),
        'items' => $items,
    ]);
}

    /**
     * Display the Damage Transaction view with existing transactions.
     */
    public function damageTransaction()
    {
        // Fetch all damage transactions with related item and category
        $damageTransactions = DamageTransaction::with(['item.category'])->get();
    
        // Fetch all items to populate the dropdown
        $items = Item::where(function($query) {
            $query->whereNull('expiration_date')
                  ->orWhere('expiration_date', '>', Carbon::now());
        })
        ->where('qtyInStock', '>', 0)
        ->orderBy('item_name', 'ASC')
        ->get();
    
        return view('inventory.damage_transaction', compact('damageTransactions', 'items'));
    }
        public function updateDamageItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:add,deduct',
        ]);

        $transaction = DamageTransaction::findOrFail($id);
        $item = Item::findOrFail($transaction->item_id);

        if ($request->operation == 'add') {
            // Validate if adding quantity exceeds available stock
            if ($transaction->quantity + $request->quantity > $item->qtyInStock) {
                return redirect()->back()->with('danger', 'Damage quantity cannot exceed available stock.');
            }
            $transaction->quantity += $request->quantity;
            $item->qtyInStock -= $request->quantity;
        } else {
            $transaction->quantity -= $request->quantity;
            $item->qtyInStock += $request->quantity;
        }

        $transaction->save();
        $item->save();

        return redirect()->back()->with('success', 'Quantity updated successfully.');
    }
    

    /**
     * Store a new Damage Transaction.
     */
    public function storeDamageTransaction(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'damage_description' => 'required|string|max:1000',
        ]);
    
        // Fetch the item to check stock
        $item = Item::findOrFail($request->item_id);
    
        // Ensure that the quantity to be damaged does not exceed current stock
        if ($request->quantity > $item->qtyInStock) {
            return back()->with('danger', 'Damage quantity exceeds available stock.');
        }
    
        // Create the damage transaction
        DamageTransaction::create([
            'item_id' => $item->id,
            'category_id' => $item->cat_id, // Automatically associate with the item's category
            'item_name' => $item->item_name,
            'quantity' => $request->quantity,
            'damage_description' => $request->damage_description,
            // 'user_id' => Auth::id(), // Uncomment if tracking user
        ]);
    
        // Update the item's stock
        $item->qtyInStock -= $request->quantity;
        // Update status based on new stock
        if ($item->qtyInStock == 0) {
            $item->status = 'Out of Stock';
        } elseif ($item->qtyInStock <= $item->low_stock_limit) {
            $item->status = 'Low Stock';
        }
        $item->save();
    
        return back()->with('success', 'Damage transaction recorded successfully.');
    }
    
    /**
     * Get items based on the selected category via AJAX.
     */
    public function getCategoryItemsAjax($categoryId)
    {
        $category = Category::find($categoryId);
    
        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }
    
        $items = $category->items()->orderBy('item_name', 'ASC')->get(['id', 'item_name']);

        $items = Item::where('cat_id', $categoryId)
                ->where(function($query) {
                    $query->whereNull('expiration_date') // Items without an expiration date
                          ->orWhere('expiration_date', '>', Carbon::now()); // Items with future expiration dates
                })
                ->where('qtyInStock', '>', 0) // Optional: Only include items with stock
                ->get();
    
        return response()->json(['items' => $items]);
    }

    public function totalItemReport(Request $request)
    {
        // Clear existing data in the total_item_report table
        TotalItemReport::truncate();
    
        // Fetch categories and items for the dropdowns
        $categories = Category::orderBy('category_name', 'ASC')->get();
        $itemsForDropdown = Item::orderBy('item_name', 'ASC')->get();
        $units = Item::select('unit_of_measurement')->distinct()->pluck('unit_of_measurement');

    
        // Initialize the query with relationships and order by 'created_at'
        $itemsQuery = Item::with('category')->orderBy('created_at', 'ASC'); // You can use 'asc' for ascending order
    
        // Apply filters based on request parameters
        if ($request->filled('start_date')) {
            $itemsQuery->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->filled('end_date')) {
            $itemsQuery->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }
        if ($request->filled('item_name')) {
            $itemsQuery->where('item_name', $request->item_name);
        }
        if ($request->filled('category')) {
            $itemsQuery->where('cat_id', $request->category);
        }
        if ($request->filled('unit')) {
            $itemsQuery->where('unit_of_measurement', $request->unit);
        }
        
    
        // Execute the query and map the results
        $items = $itemsQuery->get()->map(function ($item) {
            return [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'cat_id' => $item->cat_id,
                'category_name' => $item->category->category_name ?? 'No Category',
                'quantity' => $item->qtyInStock,
                'unit' => $item->unit_of_measurement,
                'base_price' => $item->base_price,
                'selling_price' => $item->selling_price,
                'created_at' => $item->created_at,
                'updated_at' => now(),
            ];
        });
    
        // Insert mapped data into the total_item_report table
        TotalItemReport::insert($items->toArray());
    
        // Pass the sorted items to the view
        return view('inventory.total_items_report', compact('items', 'categories', 'itemsForDropdown','units'));
    }
    
    
    public function exportTotalItemReportPdf(Request $request)
    {
        // Retrieve filter inputs
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $categoryId = $request->input('category');
        $unit = $request->input('unit');
    
        // Initialize the query with category relationship
        $itemsQuery = Item::with('category');
    
        // Apply Start Date filter
        if ($startDate) {
            $itemsQuery->where('created_at', '>=', $startDate);
        }
    
        // Apply End Date filter
        if ($endDate) {
            $itemsQuery->where('created_at', '<=', $endDate);
        }
    
        // Apply Item Name filter
        if ($itemName) {
            $itemsQuery->where('item_name', $itemName);
        }
    
        // Apply Category filter
        if ($categoryId) {
            $itemsQuery->where('cat_id', $categoryId);
        }
        if ($request->filled('unit')) {
            $itemsQuery->where('unit_of_measurement', $request->unit);
        }
        
    
        // Fetch and group items by category name
        $items = $itemsQuery->get()->groupBy('category.category_name');
    
        // Retrieve category name for display, if a category filter is applied
        $categoryName = $categoryId ? Category::find($categoryId)->category_name : null;
    
        $units = Item::select('unit_of_measurement')->distinct()->pluck('unit_of_measurement');

        // Get the authenticated user's full name
        $userFullName = Auth::guard('inventory')->user()->full_name;
    
        // Get the current date and time
        $currentDate = Carbon::now()->format('m-d-Y h:i:s a');
    
        // Generate the PDF using the filtered data
        $pdf = PDF::loadView('inventory.total_items_report_pdf', [
            'items' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'itemName' => $itemName,
            'unit' => $unit,
            'categoryName' => $categoryName,
            'currentDate' => $currentDate,
            'userFullName' => $userFullName,
        ])
        ->setPaper('A4', 'portrait')
        ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Stream the PDF back to the browser
        return $pdf->stream('Total_Items_Report.pdf');
    }

    public function exportTotalItemReport(Request $request)
    {
        // Fetch filters from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $categoryId = $request->input('category');
        $unit = $request->input('unit');
    
        // Initialize the query with category relationship
        $itemsQuery = Item::with('category');
    
        // Apply Start Date filter
        if ($startDate) {
            $itemsQuery->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
        }
    
        // Apply End Date filter
        if ($endDate) {
            $itemsQuery->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }
    
        // Apply Item Name filter
        if ($itemName) {
            $itemsQuery->where('item_name', $itemName);
        }
    
        // Apply Category filter
        if ($categoryId) {
            $itemsQuery->where('cat_id', $categoryId);
        }
    
        // Apply Unit filter
        if ($unit) {
            $itemsQuery->where('unit_of_measurement', $unit);
        }
    
        // Fetch and group items by category name
        $items = $itemsQuery->get()->groupBy('category.category_name');
    
        // Retrieve category name for display, if a category filter is applied
        $categoryName = $categoryId ? Category::find($categoryId)->category_name : 'All Categories';
    
        // Prepare filters information
        $filters = [
            'date_range' => ($startDate ? Carbon::parse($startDate)->format('m-d-Y') : 'All Dates') . ' - ' . ($endDate ? Carbon::parse($endDate)->format('m-d-Y') : 'All Dates'),
            'item_name' => $itemName ?? 'All Items',
            'category_name' => $categoryName,
            'unit' => $unit ?? 'All Units',
        ];
    
        // Get the authenticated user's full name
        $userFullName = Auth::guard('inventory')->user()->full_name;
    
        // Get the current date and time
        $currentDate = Carbon::now()->format('m-d-Y h:i:s a');
    
        $formattedDate = Carbon::now()->format('F d, Y');
    
        // Create an instance of the export class
        $export = new TotalItemReportExport($request, $items, $filters, $userFullName, $currentDate, $formattedDate);
    
        // Generate and download the Excel file
        return Excel::download($export, 'Total_Items_Report_' . Carbon::now()->format('m_d_Y') . '.xlsx');
    }
    

    //for the damage item report
    public function damageItemReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        $query = DamageTransaction::with('category');
    
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
    
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    
        if ($itemName) {
            $query->where('item_name', $itemName);
        }
    
        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('category_name', $category);
            });
        }
    
        $damageItems = $query->get();
        $uniqueItemNames = DamageTransaction::distinct()->pluck('item_name');
        $uniqueCategories = Category::distinct()->pluck('category_name');
    
        return view('inventory.damage_items_report', compact('damageItems', 'startDate', 'endDate', 'uniqueItemNames', 'uniqueCategories'));
    }    

    public function exportDamageItemReportPdf(Request $request)
    {
        // Retrieve the filters from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        // Build the query with necessary relationships and filters
        $query = DamageTransaction::with('category');
    
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
    
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    
        if ($itemName) {
            $query->where('item_name', 'like', '%' . $itemName . '%');
        }
    
        if ($category) {
            $query->whereHas('category', function($q) use ($category) {
                $q->where('category_name', 'like', '%' . $category . '%');
            });
        }
    
        // Get the filtered damage items
        $damageItems = $query->get();
    
        // Get the full name of the authenticated user
        $userFullName = Auth::guard('inventory')->user()->full_name;
    
        // Generate the PDF with the filtered damage items
        $pdf = PDF::loadView('inventory.damage_items_report_pdf', compact('damageItems', 'startDate', 'endDate', 'itemName', 'category', 'userFullName'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // View the generated PDF
        return $pdf->stream('dwcc_college_bookstore_damage_item_report.pdf');
    }
    

    public function exportDamageItemReportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        // Pass the necessary filters to the export class
        return Excel::download(new DamageItemReportExport($startDate, $endDate, $itemName, $category), 'damage_item_report.xlsx');
    }

    //user profile function
    public function profile()
    {
        // Get the authenticated user
        $user = Auth::guard('inventory')->user();
        return view('inventory.user_profile', compact('user'));
    }

    public function changePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('inventory')->user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('inventory.login')->with('success', 'Password updated successfully. Please re-login.');
    }

    public function lowStockItemReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');

        // Build query for low stock items
        $query = Item::whereColumn('qtyInStock', '<=', 'low_stock_limit');

        // Check if start and end dates are the same
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate)->endOfDay());
        }
        

        if ($itemName) {
            $query->where('item_name', $itemName);
        }

        if ($category) {
            $query->where('cat_id', $category);
        }

        // Retrieve low stock items
        $lowStockItems = $query->get();

        // Retrieve all categories and item names for filters
        $categories = Category::orderBy('category_name', 'ASC')->get();
        $itemNames = Item::select('item_name')->distinct()->orderBy('item_name')->get();

        // Return the view with data
        return view('inventory.low_stock_items', compact('lowStockItems', 'categories', 'itemNames'));
    }

    public function exportLowStockItemReportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
        $categoryName = 'All Categories'; // Default value
    
        // Build query for low stock items
        $query = Item::whereColumn('qtyInStock', '<=', 'low_stock_limit');
    
        if ($startDate && $endDate && $startDate === $endDate) {
            $query->whereDate('created_at', $startDate);
        } elseif ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        if ($itemName) {
            $query->where('item_name', $itemName);
        }
    
        if ($category) {
            $query->where('cat_id', $category);
            // Retrieve the category name based on the category ID
            $categoryName = Category::where('id', $category)->value('category_name');
        }
    
        // Retrieve low stock items
        $lowStockItems = $query->get();
        // Get the full name of the authenticated user
        $userFullName = Auth::guard('inventory')->user()->full_name;
    
        // Load the PDF view
        $pdf = Pdf::loadView('inventory.low_stock_items_report_pdf', compact('lowStockItems', 'startDate', 'endDate', 'itemName', 'categoryName', 'userFullName'));
    
        // View the generated PDF
        return $pdf->stream('low_stock_item_report.pdf');
    }

    public function exportLowStockItemReportExcel(Request $request)
    {
        // Get filter parameters from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');

        // Pass the filters to the export class
        $export = new LowStockItemReportExport($startDate, $endDate, $itemName, $category);

        // Generate the Excel file for download
        return Excel::download($export, 'low_stock_item_report.xlsx');
    }

    public function transferItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
        ]);
    
        // Find the source item
        $sourceItem = Item::findOrFail($request->item_id);
    
        // Find the target item with the same name and either a higher or equal price
        $targetItem = Item::where('item_name', 'like', '%' . $sourceItem->item_name . '%')
            ->where(function($query) use ($sourceItem) {
                $query->where('base_price', '>=', $sourceItem->base_price)
                      ->orWhere('selling_price', '>=', $sourceItem->selling_price);
            })
            ->where('id', '!=', $sourceItem->id) // Ensure the target item is not the same as the source item
            ->first();
    
        if (!$targetItem) {
            // If no matching target item is found, return a failure message
            return back()->with('fail', 'No matching item found with higher or equal prices.');
        }
    
        // Update the target item's stock
        $targetItem->qtyInStock += $sourceItem->qtyInStock;
        $targetItem->save();
    
        // Log the transfer in the transfer logs table
        TransferItemLogs::create([
            'source_item_id' => $sourceItem->id,
            'target_item_id' => $targetItem->id,
            'item_name' => $sourceItem->item_name,
            'transfer_to' => $targetItem->item_name,
            'transferred_quantity' => $sourceItem->qtyInStock,
            'base_price' => $sourceItem->base_price,
            'selling_price' => $sourceItem->selling_price,
            'transferred_by' => Auth::guard('inventory')->user()->full_name,
        ]);

            // Delete the source item from the database
            $sourceItem->delete();
    
        // Redirect back with success message
        return redirect()->route('inventory.stockmanagement')->with('success', 'Item transferred successfully.');
    }

    
    public function getSimilarItems($itemId)
    {
        // Get the source item
        $sourceItem = Item::findOrFail($itemId);

        // Find higher-priced items using a "contains" match on the item name
        $higherPricedItems = Item::where('item_name', 'like', '%' . $sourceItem->item_name . '%')
            ->where('base_price', '>', $sourceItem->base_price)
            ->where('selling_price', '>', $sourceItem->selling_price)
            ->get();

        return response()->json(['higherPricedItems' => $higherPricedItems]);
    }

    public function expiredItemReport(Request $request)
    {
        // Step 1: Fetch expired items from the `items` table
        $expiredItems = Item::where('expiration_date', '<', now())->get();
    
        // Step 2: Save expired items to the `expired_items` table
        foreach ($expiredItems as $item) {
            // Check if the item already exists in `expired_items` to prevent duplicates
            if (!ExpiredItem::where('barcode', $item->barcode)->exists()) {
                ExpiredItem::create([
                    'barcode' => $item->barcode,
                    'item_name' => $item->item_name,
                    'category' => $item->category->category_name, // Assuming category relationship exists
                    'quantity' => $item->qtyInStock,
                    'date_encoded' => $item->created_at,
                    'expiration_date' => $item->expiration_date,
                ]);
            }
        }
    
        // Step 3: Apply filters to the `expired_items` table
        $query = ExpiredItem::query();
    
        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date); // Remove startOfDay()
            $endDate = Carbon::parse($request->end_date);   // Remove endOfDay()
    
            // Use whereDate to compare only the date part
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        }
    
        // Filter by item name
        if ($request->filled('item_name')) {
            $query->where('item_name', 'like', '%' . $request->item_name . '%');
        }
    
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
    
        // Step 4: Retrieve filtered expired items and order by item_name and category
        $expiredItemsFromDB = $query->orderBy('item_name')->orderBy('category')->get();
    
        // Fetch unique categories and item names for dropdown filters and sort them
        $categories = ExpiredItem::select('category')->distinct()->orderBy('category')->pluck('category');
        $itemNames = ExpiredItem::select('item_name')->distinct()->orderBy('item_name')->pluck('item_name');
    
        // Step 5: Pass data to the view
        return view('inventory.expired_item_report', compact('expiredItemsFromDB', 'categories', 'itemNames'));
    }
    public function exportExpiredItemReportPdf(Request $request)
    {
           // Step 1: Filter items from the `expired_items` table based on filters
        $query = ExpiredItem::query();
    
        // Collect filter values
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $itemName = $request->item_name;
        $category = $request->category;
    
         // Apply filters
        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        if ($itemName) {
           $query->where('item_name', 'like', '%' . $itemName . '%');
        }
    
        if ($category) {
           $query->where('category',$category);
        }

         // Fetch the filtered items
        $expiredItems = $query->get();
    
        // Get the user's full name
        $userFullName = Auth::guard('inventory')->user()->full_name;
    
        // Step 2: Generate the PDF using the filtered data and filters
        $pdf = Pdf::loadView('inventory.expired_item_report_pdf', compact(
            'expiredItems',
            'userFullName',
            'startDate',
            'endDate',
            'itemName',
            'category'
        ));
    
        // Step 3: Return the PDF for View
        return $pdf->stream('expired_items_report.pdf');
    }

    public function exportExpiredItemReportExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        // Pass the necessary filters to the export class
        return Excel::download(new ExpiredItemReportExport($startDate, $endDate, $itemName, $category), 'expired_item_report.xlsx');
    }
    
    
    public function modifyExpirationDate(Request $request)
    {
        // Validate the request
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'new_expiration_date' => 'required|date|after:today',
            'quantity_added' => 'required|integer|min:1',
        ]);
    
        // Retrieve the item
        $item = Item::findOrFail($request->item_id);
    
        // Store old values
        $oldExpirationDate = $item->expiration_date;
        $oldQtyInStock = $item->qtyInStock;
    
        // Add the new quantity to stock
        $item->qtyInStock += $request->quantity_added;
    
        // Update the status based on new stock
        if ($item->qtyInStock == 0) {
            $item->status = 'Out of Stock';
        } elseif ($item->qtyInStock <= $item->low_stock_limit) {
            $item->status = 'Low Stock';
        } else {
            $item->status = 'In Stock';
        }
    
        // Save the updated item (without changing the expiration date)
        $item->save();
    
        // Store the new expiration date in the modified_expiration_date_logs table
        ModifiedExpirationDateLog::create([
            'item_id' => $item->id,
            'item_name' => $item->item_name,
            'qty_in_stock' => $oldQtyInStock,
            'quantity_added' => $request->quantity_added,
            'new_expiration_date' => $request->new_expiration_date,
            'modified_by' => Auth::guard('inventory')->user()->full_name,
        ]);
    
        InventoryLog::create([
            'message' => "Added {$request->quantity_added} stock and logged new expiration date ({$request->new_expiration_date}) for item: {$item->item_name}",
            'type' => 'update',
            'user_id' => Auth::id(),
            'manage_by' => Auth::guard('inventory')->user()->full_name,
        ]);
    
        return redirect()->route('inventory.stockmanagement')->with('success', 'New expiration date logged and stock added successfully.');
    }

    public function togaRenting()
    {
        // Fetch all items for rent from the database
        $items = ItemForRent::all();

        // Pass items to the Blade view
        return view('inventory.toga_renting', compact('items'));
    }
    

    public function storeRentItem(Request $request)
    {
        // Validate the input
        $request->validate([
            'item_name' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'size' => 'required|string|max:50',
            'total_quantity' => 'required|integer|min:1',
        ]);

        // Concatenate item_name, color, and size
        $concatenatedItemName = "{$request->item_name} {$request->color} {$request->size}";

        // Check if the concatenated name already exists in the database
        $exists = ItemForRent::where('item_name', $concatenatedItemName)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This item already exists in the inventory.',
            ]);
        }

        // Create a new item in the database
        $item = ItemForRent::create([
            'item_name' => $concatenatedItemName,
            'total_quantity' => $request->total_quantity,
            'quantity_borrowed' => 0, // Default value
        ]);

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => 'Item added successfully.',
            'item' => $item,
        ]);
    }

    public function addStock(Request $request, $id)
    {
        $item = ItemForRent::find($id);
    
        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }
    
        $addedQuantity = $request->input('amount', 1);
        $item->total_quantity += $addedQuantity;
        $item->save();
    
        return response()->json([
            'message' => "Successfully added $addedQuantity to {$item->item_name}.",
            'total_quantity' => $item->total_quantity,
            'remaining_stock' => $item->total_quantity - $item->quantity_borrowed - $item->damaged_quantity - $item->lost_quantity
        ]);
        
    }
    public function getItem($id)
    {
        // Retrieve the item from the database
        $item = ItemForRent::findOrFail($id);

        // Return a JSON response with item details
        return response()->json([
            'item_name' => $item->item_name,
            'total_quantity' => $item->total_quantity,
        ]);
    }
    
    
    public function Services()
    {
        $services = Services::all();
        return view('inventory.services', compact('services'));
    }
    

    public function storeService(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string',
            'status' => 'required|boolean',
        ]);
    
        Services::create([
            'service_name' => $request->input('service_name'),
            'status' => $request->boolean('status') ? 1 : 0,
        ]);
    
        return redirect()->route('inventory.services')->with('success', 'Service added successfully.');
    }

    public function updateService(Request $request, $id)
    {
        $request->validate([
            'service_name' => 'required|string',
            'status' => 'required|boolean',
        ]);
    
        $service = Services::find($id);
        $service->update([
            'service_name' => $request->input('service_name'),
            'status' => $request->boolean('status') ? 1 : 0,
        ]);
    
        return redirect()->route('inventory.services')->with('success', 'Service updated successfully.');
    }

    public function addDetails(Request $request, Service $service)
    {
        $request->validate([
            'detail_key' => 'required|string|max:255',
            'detail_value' => 'required|string|max:255',
        ]);

        $service->details()->create([
            'detail_key' => $request->detail_key,
            'detail_value' => $request->detail_value,
        ]);

        return redirect()->route('inventory.services')->with('success', 'Service detail added successfully!');
    }

    public function showTransferItemsPrice()
    {
        // Fetch all transfer item logs with relationships
        $transferItemLogs = TransferItemLogs::with(['sourceItem', 'targetItem'])->get();
    
        // Pass the variable to the view
        return view('inventory.transfer_item_price', compact('transferItemLogs'));
    }
    
    /**
     * Show the form for updating prices.
     */
   public function showPriceUpdate()
    {
        // Fetch all price update logs, optionally with pagination
        $priceUpdates = ItemLog::with(['item', 'user'])
                                ->orderBy('created_at', 'desc')
                                ->paginate(15); // Adjust the number as needed

        return view('inventory.price_update', compact('priceUpdates'));
    }

    public function viewPriceUpdate($id)
{
    $priceUpdate = ItemLog::findOrFail($id);
    return view('inventory.price_update', compact('priceUpdate'));
}

    
}
