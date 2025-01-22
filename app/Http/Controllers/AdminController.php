<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\VoidRecords;
use Session;
use Carbon\Carbon;
use App\Models\Borrower;
use App\Models\ItemForRent;
use App\Models\FinesHistory;
use App\Models\ReturnedItem;
use App\Models\Category;
use App\Models\Services;
use App\Models\SalesReport;
use App\Exports\ReturnedItemsReportExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Exports\VoidItemReportExport;
use App\Exports\SalesReportExport;
use App\Models\DamageTransaction;
use Illuminate\Support\Facades\Hash;
use App\Models\Section;
use App\Models\TotalItemReport;
use App\Models\ItemLog;
use App\Exports\TotalItemReportExport;
use App\Exports\DamageItemReportExport;
use App\Exports\ExpiredItemReportExport;
use App\Models\InventoryLog;
use App\Models\ExpirationDateChange;
use App\Models\StockLog;
use App\Models\TransferItemLogs;
use App\Models\ExpiredItem;
use App\Models\ModifiedExpirationDateLog;
use App\Exports\TotalItemReportExportAdmin;
use App\Exports\SalesReportExportAdmin;
use App\Exports\VoidItemReportExportAdmin;
use App\Exports\ReturnedItemsReportExportAdmin;
use App\Exports\DamageItemReportExportAdmin;
use App\Models\BorrowedItem;
use App\Models\UserLog;
use App\Exports\FinesReportExportAdmin;



class AdminController extends Controller
{
    // Admin Login Page
    public function adminlogin(){
        return view("admin.admin_login");
    }

    public function adminDashboard(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        $query = Transaction::query();
    
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
    
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    
        // Total Sales
        $totalSales = $query->count();
    
        // Total Items
        $totalItems = Item::count();
    
        // Damage Items
        $damageItems = DamageTransaction::count();
    
        // Grand Total
        $grandTotal = $query->with('items')
            ->get()
            ->sum(function ($transaction) {
                return $transaction->items->sum('total');
            });
    
        // Quantity Sold per Day
        $salesData = $query->with('items')
            ->get()
            ->groupBy(function ($transaction) {
                return $transaction->created_at->format('Y-m-d'); // Group by date
            })
            ->map(function ($transactions) {
                return $transactions->sum(function ($transaction) {
                    return $transaction->items->sum('quantity'); // Sum item quantities
                });
            });
    
        $dates = $salesData->keys()->map(function ($date) {
            return Carbon::parse($date)->format('F d, Y'); // Format dates
        })->toArray();
    
        $salesQuantities = $salesData->values()->toArray();
    
        $users = User::with(['logs'])->get();
    
        return view('admin.admin_dashboard', compact(
            'totalSales',
            'totalItems',
            'damageItems',
            'grandTotal',
            'dates',
            'salesQuantities', // Pass sales quantities to the view
            'users'
        ));
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
        if ($user->user_role !== 'Admin') {
            return back()->with('fail', 'You are not authorized to access this section.');
        } else if ($user->user_status !== 'Active') {
            return back()->with('fail', 'This account is inactive, please contact the administrator.');
        }

        if (Auth::guard('admin')->attempt($credentials)) {

            // Store the logged-in user's ID in the session
            $request->session()->put('loginId', $user->id);

            return redirect()->route('admin.dashboard')->with([
                'success' => 'Login Successful',
                'full_name' => $user->full_name
            ]);
        }

        // If password is incorrect
        return back()->with('fail', 'The password is incorrect.');
    }

    public function userProfile()
        {
            // Retrieve the currently authenticated user
            $user = Auth::guard('admin')->user();
            return view('admin.userProfile', compact('user'));
        }

        public function changePassword(Request $request)
        {
            // Validate the request
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);
    
            $user = Auth::guard('admin')->user();
    
            // Check if the current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
    
            // Update the password
            $user->password = Hash::make($request->new_password);
            $user->save();
    
            return redirect()->route('admin_login')->with('success', 'Password updated successfully. Please re-login.');
        }

    // Admin Logout function
    public function adminlogout(Request $request)
    {   
        // Get the logged-in admin's ID
        $userId = Auth::guard('admin')->user()->id;

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

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin_login')->with('success', 'You have been logged out successfully.');
    }

    public function togaFines()
    {
        $borrowers = Borrower::with('borrowedItems.item')->get(); // Eager load borrowedItems and their related item
        $items = ItemForRent::all();
        return view('admin.toga_fines', compact('borrowers', 'items'));
    }

    public function addBorrower(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the request
            $request->validate([
                'student_id' => 'required|string|max:255',
                'student_name' => 'required|string|max:255',
                'date_issued' => 'required|date',
                'expected_date_returned' => 'required|date|after_or_equal:date_issued',
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'exists:item_for_rent,id',
            ]);
    
            // Check if the student already has active borrowed items
            $activeBorrowed = BorrowedItem::whereHas('borrower', function ($query) use ($request) {
                $query->where('student_number', $request->student_id);
            })->where('status', 'Borrowed')->exists();
    
            if ($activeBorrowed) {
                throw new \Exception("This student has already borrowed items and has not returned them yet.");
            }
    
            // Prepare the borrowed items and validate stock
            $borrowedItems = [];
            foreach ($request->item_ids as $itemId) {
                $item = ItemForRent::findOrFail($itemId);
    
                $availableQuantity = $item->total_quantity - $item->quantity_borrowed;
                if ($availableQuantity <= 0) {
                    throw new \Exception("The item '{$item->item_name}' is currently out of stock.");
                }
    
                $borrowedItems[] = [
                    'item_id' => $item->id,
                    'quantity' => 1, // Only one quantity per checkbox
                ];
            }
    
            // Create the borrower record
            $borrower = Borrower::create([
                'student_number' => $request->student_id,
                'student_name' => $request->student_name,
            ]);
    
            // Deduct stock and create borrowed item records
            foreach ($borrowedItems as $borrowedItemData) {
                BorrowedItem::create([
                    'borrower_id' => $borrower->id,
                    'borrowed_date' => $request->date_issued,
                    'return_date' => $request->expected_date_returned,
                    'status' => 'Borrowed',
                    'item_id' => $borrowedItemData['item_id'],
                ]);
    
                ItemForRent::findOrFail($borrowedItemData['item_id'])->increment('quantity_borrowed', $borrowedItemData['quantity']);
            }
    
            DB::commit();
    
            return redirect()->back()->with('success', 'Borrower and items successfully added!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    

    public function returnBorrower(Request $request)
    {
        $validated = $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:borrowed_items,id',
            'conditions' => 'required|array|min:1',
            'conditions.*' => 'in:Good,Damaged,Lost',
            'fees' => 'nullable|array',
            'fees.*' => 'nullable|numeric|min:0',
        ]);

        try {
            $borrowerId = null;
            $problematicItems = [];
            $processedItems = 0;
            $lateFeePerDay = 10;

            DB::beginTransaction();

            foreach ($request->item_ids as $itemId) {
                $borrowedItem = BorrowedItem::with('item')->findOrFail($itemId);
                $borrowerId = $borrowedItem->borrower_id;

                $condition = $request->conditions[$itemId];
                $fee = $request->fees[$itemId] ?? 0;

                $returnDate = Carbon::parse($borrowedItem->return_date)->startOfDay();
                $currentDate = Carbon::now()->startOfDay();

                $daysLate = 0;
                $lateFee = 0;

                if ($currentDate->greaterThan($returnDate)) {
                    $daysLate = $currentDate->diffInDays($returnDate);
                    $lateFee = $daysLate * $lateFeePerDay;
                }

                if ($condition === 'Good' && $daysLate === 0) {
                    // Decrement the borrowed quantity
                    $borrowedItem->item->decrement('quantity_borrowed', 1);
                    // Delete the borrowed item record
                    $borrowedItem->delete();
                    $processedItems++;
                } else {
                    // Accumulate problematic items for further processing
                    $problematicItems[] = [
                        'item_name' => $borrowedItem->item->item_name,
                        'condition' => $condition,
                        'fee' => $fee,
                        'days_late' => $daysLate,
                        'late_fee' => $lateFee,
                    ];
                }
            }

            if (!empty($problematicItems)) {
                DB::rollBack();
                return redirect()->back()->with([
                    'error_modal' => true,
                    'problematic_items' => $problematicItems,
                ]);
            }

            if ($processedItems > 0) {
                // Check if the borrower has any remaining borrowed items
                $remainingItems = BorrowedItem::where('borrower_id', $borrowerId)->count();
                if ($remainingItems === 0) {
                    Borrower::find($borrowerId)->delete();
                }

                DB::commit();
                return redirect()->back()->with('success', 'Good condition item(s) returned successfully.');
            }

            DB::commit();
            return redirect()->back()->with('info', 'No items were processed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while processing the return.');
        }
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
        return view('admin.total_items_report', compact('items', 'categories', 'itemsForDropdown','units'));
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
        $userFullName = Auth::guard('admin')->user()->full_name;
    
        // Get the current date and time
        $currentDate = Carbon::now()->format('m-d-Y h:i:s a');
    
        // Generate the PDF using the filtered data
        $pdf = PDF::loadView('admin.total_items_report_pdf', [
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
        $userFullName = Auth::guard('admin')->user()->full_name;
    
        // Get the current date and time
        $currentDate = Carbon::now()->format('m-d-Y h:i:s a');
    
        $formattedDate = Carbon::now()->format('F d, Y');
    
        // Create an instance of the export class
        $export = new TotalItemReportExportAdmin($request, $items, $filters, $userFullName, $currentDate, $formattedDate);
    
        // Generate and download the Excel file
        return Excel::download($export, 'Total_Items_Report_' . Carbon::now()->format('m_d_Y') . '.xlsx');
    }

    public function salesReport(Request $request)
    {
               // Retrieve filter values
               $startDate = $request->input('start_date');
               $endDate = $request->input('end_date');
               $category = $request->input('category');
               $paymentMethod = $request->input('payment');
               $itemName = $request->input('item_name'); // New filter
           
               // Query Transactions with filters
               $transactions = Transaction::with(['user', 'items.item.category' => function($query) use ($category) {
                // Apply filters to the related items
                if ($category) {
                    $query->where('id', $category);
                }
            }])
                   ->when($startDate, function ($query, $startDate) {
                       return $query->whereDate('created_at', '>=', $startDate);
                   })
                   ->when($endDate, function ($query, $endDate) {
                       return $query->whereDate('created_at', '<=', $endDate);
                   })
                   ->when($paymentMethod, function ($query, $paymentMethod) {
                       return $query->where('payment_method', $paymentMethod);
                   })
                   ->when($category, function ($query, $category) {
                       return $query->whereHas('items.item', function ($q) use ($category) {
                           $q->where('cat_id', $category);
                       });
                   })
                   ->when($itemName, function ($query, $itemName) {
                       return $query->whereHas('items.item', function ($q) use ($itemName) {
                           $q->where('item_name', $itemName);
                       });
                   })
                   ->orderBy('created_at', 'desc')
                   ->get();
           
               // Filter items within each transaction based on the applied filters
               $transactions->each(function ($transaction) use ($category, $itemName) {
                   $transaction->items = $transaction->items->filter(function ($item) use ($category, $itemName) {
                       $matches = true;
                       if ($category && $item->item->cat_id != $category) {
                           $matches = false;
                       }
                       if ($itemName && $item->item->item_name != $itemName) {
                           $matches = false;
                       }
                       return $matches;
                   });
               });
           
               // Calculate total sales based on the filtered items
               $totalSales = $transactions->sum(function ($transaction) {
                   return $transaction->items->sum('total');
               });
           
               // Retrieve categories and items as key-value pairs
               $categories = Category::pluck('category_name', 'id');
               $items = Item::orderBy('item_name', 'ASC')->pluck('item_name', 'id');
    
        // Pass data to the view
        return view('admin.sales_report', compact('transactions', 'categories', 'items', 'totalSales', 'paymentMethod', 'category', 'itemName'));
    }

    public function exportSalesReportPdf(Request $request)
    {
        // Retrieve filter values for the report
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $categoryId = $request->input('category');
        $paymentMethod = $request->input('payment');
        $itemName = $request->input('item_name'); // New filter
    
        // Fetch filtered transactions
        $transactions = Transaction::with(['items.item.category', 'user'])
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($paymentMethod, function ($query, $paymentMethod) {
                return $query->where('payment_method', $paymentMethod);
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->whereHas('items.item', function ($q) use ($categoryId) {
                    $q->where('cat_id', $categoryId);
                });
            })
            ->when($itemName, function ($query, $itemName) {
                return $query->whereHas('items.item', function ($q) use ($itemName) {
                    $q->where('item_name', 'like', "%$itemName%"); // Flexible search
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    
        // **Add Collection-Level Filtering Here**
        $transactions->each(function ($transaction) use ($categoryId, $itemName) {
            $transaction->items = $transaction->items->filter(function ($item) use ($categoryId, $itemName) {
                $matches = true;
                if ($categoryId && $item->item->cat_id != $categoryId) {
                    $matches = false;
                }
                if ($itemName && stripos($item->item->item_name, $itemName) === false) { // Case-insensitive partial match
                    $matches = false;
                }
                return $matches;
            });
        });
    
        // **Ensure Transactions with No Items After Filtering Are Removed**
        $transactions = $transactions->filter(function ($transaction) {
            return $transaction->items->isNotEmpty();
        });
    
        // Calculate total sales
        $totalSales = $transactions->sum(function ($transaction) {
            return $transaction->items->sum('total');
        });
    
        // Retrieve category name if filter is applied
        $categoryName = $categoryId 
            ? Category::find($categoryId)->category_name 
            : 'All Categories';
    
        // Retrieve item name if filter is applied
        $itemNameLabel = $itemName 
            ? $itemName 
            : 'All Items';
    
        // Retrieve admin name
        $userFullName = Auth::guard('admin')->user()->full_name;
    
        // Generate PDF
        $pdf = Pdf::loadView('admin.sales_report_pdf', compact(
            'transactions', 
            'totalSales', 
            'userFullName', 
            'paymentMethod', 
            'categoryName', 
            'itemNameLabel', 
            'startDate', 
            'endDate'
        ));
    
        // View PDF
        return $pdf->stream('sales_report.pdf');
    }
    

    public function exportSalesReportExcel(Request $request)
    {
        // Get filter values
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedPaymentMethod = $request->input('payment');
        $categoryId = $request->input('category');
        $itemName = $request->input('item_name'); // New filter
    
        // Build the query
        $query = Transaction::query();
    
        // Date Range Filtering
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }
    
        // Payment Method Filtering
        if ($selectedPaymentMethod) {
            $query->where('payment_method', $selectedPaymentMethod);
        }
    
        // Category Filtering
        if ($categoryId) {
            $query->whereHas('items.item', function ($q) use ($categoryId) {
                $q->where('cat_id', $categoryId);
            });
        }
    
        // Item Name Filtering
        if ($itemName) {
            $query->whereHas('items.item', function ($q) use ($itemName) {
                $q->where('item_name', $itemName);
            });
        }
    
        // Load relationships and get results
        $transactions = $query->with(['items.item.category', 'user'])->get();
    
        // Pass filter parameters to SalesReportExport
        $categoryName = $categoryId ? Category::find($categoryId)->category_name : 'All Categories';
        $itemNameLabel = $itemName ? $itemName : 'All Items'; // Pass the itemName string directly
    
        return Excel::download(new SalesReportExportAdmin($transactions, $startDate, $endDate, $categoryName, $selectedPaymentMethod, $itemName, $categoryId), 'sales_report.xlsx');
    }

    
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
    
        return view('admin.damage_items_report', compact('damageItems', 'startDate', 'endDate', 'uniqueItemNames', 'uniqueCategories'));
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
        $userFullName = Auth::guard('admin')->user()->full_name;
    
        // Generate the PDF with the filtered damage items
        $pdf = PDF::loadView('admin.damage_items_report_pdf', compact('damageItems', 'startDate', 'endDate', 'itemName', 'category', 'userFullName'))
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
        return Excel::download(new DamageItemReportExportAdmin($startDate, $endDate, $itemName, $category), 'damage_item_report.xlsx');
    }
  
     /**
     * Show void report view.
     */
    public function void_report(Request $request)
    {
        // Query the VoidRecords model
        $query = VoidRecords::with(['items.category']);
    
        // Apply filters based on the request inputs
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('voided_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59',
            ]);
        }
    
        if ($request->filled('item_name')) {
            $query->where('item_name', $request->input('item_name'));
        }
    
        if ($request->filled('category')) {
            $query->whereHas('items.category', function ($q) use ($request) {
                $q->where('category_name', $request->input('category'));
            });
        }
    
        // Fetch filtered records and sort by the latest voided_at
        $voidRecords = $query->orderBy('voided_at', 'desc')->get();

        $items = Item::orderBy('item_name', 'ASC')->get();
        $categories = Category::orderBy('category_name', 'ASC')->get();
    
        // Pass the records and filters to the view
        return view('admin.void_report', compact('voidRecords', 'items', 'categories'));
    }

    public function exportVoidItemReportPdf(Request $request)
    {
        // Retrieve the filters from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        // Build the query with necessary relationships and filters
        $query = VoidRecords::with(['items.category']);
    
        if ($startDate) {
            $query->whereDate('voided_at', '>=', $startDate);
        }
    
        if ($endDate) {
            $query->whereDate('voided_at', '<=', $endDate);
        }
    
        if ($itemName) {
            $query->where('item_name', 'like', '%' . $itemName . '%');
        }
    
        if ($category) {
            $query->whereHas('items.category', function($q) use ($category) {
                $q->where('category_name', 'like', '%' . $category . '%');
            });
        }
    
        // Get the filtered damage items
        $voidRecords = $query->get();
    
        // Get the full name of the authenticated user
        $userFullName = Auth::guard('admin')->user()->full_name;
    
        // Generate the PDF with the filtered damage items
        $pdf = PDF::loadView('admin.void_report_pdf', compact('voidRecords', 'startDate', 'endDate', 'itemName', 'category', 'userFullName'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Download the generated PDF
        return $pdf->stream('dwcc_college_bookstore_void_item_report.pdf');
    }

    public function exportVoidItemReportExcel(Request $request)
    {
        $userFullName = Auth::guard('admin')->user()->full_name;
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        // Pass the necessary filters to the export class
        return Excel::download(new VoidItemReportExportAdmin($startDate, $endDate, $itemName, $category), 'void_item_report.xlsx');
    }

     
    public function returnReport(Request $request)
    {
        // Fetch query parameters for filtering
        $itemName = $request->get('item_name');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $categoryName = $request->get('category');
    
        // Fetch all unique items for the dropdown
        $items = DB::table('returned_items')
            ->select('item_name')
            ->distinct()
            ->orderBy('item_name', 'asc')
            ->get();
    
        // Fetch all unique categories for the dropdown
        $categories = Category::orderBy('category_name')->get(); // Assuming you have a Category model
    
        // Build the query
        $query = ReturnedItem::with(['item.category'])
            ->select([
                'transaction_no',
                'item_name',
                'return_quantity',
                'reason',
                'replacement_item',
                'created_at', // Include the return date column
            ]);
    
        // Apply filters if provided
        if (!empty($itemName)) {
            $query->where('item_name', 'LIKE', "%$itemName%");
        }
    
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('created_at', '>=', $startDate)
                  ->whereDate('created_at', '<=', $endDate);
        } elseif (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    
        if (!empty($categoryName)) {
            $query->whereHas('item.category', function ($q) use ($categoryName) {
                $q->where('category_name', $categoryName);
            });
        }
    
        // Execute the query and get the results
        $returnedItems = $query->get();
    
        return view('admin.return_item_report', compact('returnedItems', 'items', 'categories'));
    }
    

    public function exportReturnedItemReportPdf(Request $request)
    {
        // Retrieve the filtered data
        $itemName = $request->input('item_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $categoryName = $request->input('category');
    
        // Query the returned items based on the filter
        $returnedItemsQuery = ReturnedItem::query()->with('item.category');
    
        if ($itemName) {
            $returnedItemsQuery->where('item_name', $itemName);
        }
    
        if ($startDate && $endDate) {
            $returnedItemsQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($startDate) {
            $returnedItemsQuery->where('created_at', '>=', $startDate . ' 00:00:00');
        } elseif ($endDate) {
            $returnedItemsQuery->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        if ($categoryName) {
            $returnedItemsQuery->whereHas('item.category', function ($q) use ($categoryName) {
                $q->where('category_name', $categoryName);
            });
        }
    
        $returnedItems = $returnedItemsQuery->get();
    
        // Get the full name of the authenticated user
        $userFullName = Auth::guard('admin')->user()->full_name;
    
        // Generate the PDF with the filtered damage items
        $pdf = PDF::loadView('cashier.return_item_report_pdf', compact('returnedItems', 'startDate', 'endDate', 'itemName', 'categoryName', 'userFullName'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // View the generated PDF
        return $pdf->stream('dwcc_college_bookstore_return_item_report.pdf');
    }

    public function exportReturnedItemReportExcel(Request $request)
    {
        $itemName = $request->query('item_name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $categoryName = $request->query('category');
    
        return Excel::download(
            new ReturnedItemsReportExportAdmin($itemName, $startDate, $endDate, $categoryName),
            'Returned_Items_Report.xlsx'
        );
    }

    public function finesReport(Request $request)
{
    $query = FinesHistory::with('borrower');

    // Apply filters
    if ($request->has('start_date') && $request->start_date) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->has('end_date') && $request->end_date) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }
    if ($request->has('item_name') && $request->item_name) {
        $query->where('item_borrowed', $request->item_name);
    }
    if ($request->has('condition') && $request->condition) {
        $query->where('condition', $request->condition);
    }
    if ($request->has('payment') && $request->payment) {
        $query->where('payment_method', $request->payment);
    }

    $finesReport = $query->get();
    // $userFullName = Auth::guard('cashier')->user()->full_name;

    $totalFines = $query->sum('fines_amount');
    // Get all items for dropdown
    $items = ItemForRent::pluck('item_name', 'item_name');

    return view('admin.togafines_report', compact('finesReport', 'items', 'totalFines'));
}

public function exportFinesReportPdf(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $itemName = $request->input('item_name'); // Ensure this matches the form's input name
    $condition = $request->input('condition');
    $paymentMethod = $request->input('payment');
    

    $query = FinesHistory::with('borrower');

    // Apply filters (same as finesReport)
    if ($request->has('start_date') && $request->start_date) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }
    if ($request->has('end_date') && $request->end_date) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }
    if ($request->has('item_name') && $request->item_name) {
        $query->where('item_borrowed', $request->item_name);
    }
    if ($request->has('condition') && $request->condition) {
        $query->where('condition', $request->condition);
    }
    if ($request->has('payment') && $request->payment) {
        $query->where('payment_method', $request->payment);
    }

    $finesReport = $query->get();
    $totalFines = $query->sum('fines_amount');

    $pdf = Pdf::loadView('admin.togafines_report_pdf', compact('finesReport', 'totalFines', 'startDate', 'endDate', 'itemName', 'condition', 'paymentMethod'))
        ->setPaper('A4', 'portrait');
    return $pdf->stream('Toga_Fines_Report.pdf');
}

public function exportFinesReportExcel(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $itemName = $request->input('item_name');
    $condition = $request->input('condition');
    $paymentMethod = $request->input('payment');

    // Pass all parameters to the FinesReportExport constructor
    return Excel::download(new FinesReportExportAdmin($startDate, $endDate, $itemName, $condition, $paymentMethod), 'Toga_Fines_Report.xlsx');
}
    

}
