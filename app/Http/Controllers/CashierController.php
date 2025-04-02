<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\VoidRecords;
use Session;
use Carbon\Carbon;
use App\Models\Borrower;
use App\Models\BorrowedItem;
use App\Models\ItemForRent;
use App\Models\FinesHistory;
use App\Models\ReturnedItem;
use App\Models\Category;
use App\Models\ServiceItem;
use App\Models\Services;
use App\Models\SalesReport;
use App\Exports\ReturnedItemsReportExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Exports\VoidItemReportExport;
use App\Exports\SalesReportExport;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLog;
use App\Exports\FinesReportExport;



class CashierController extends Controller
{
    /**
     * Show the cashier login view.
     */
    public function cashierlogin(){
        return view("cashier.cashier_login");
    }

    /**
     * Authenticate the cashier user.
     */
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

        // Check if the user has the 'Cashier' role
        if ($user->user_role !== 'Cashier') {
            return back()->with('fail', 'You are not authorized to access this section.');
        } else if ($user->user_status !== 'Active') {
            return back()->with('fail', 'This account is inactive, please contact the administrator.');
        }

         if (Auth::guard('cashier')->attempt($credentials)) {
            return redirect()->route('cashier.cashier_dashboard')->with([
                'success' => 'Login Successful',
                'full_name' => $user->full_name
            ]);
        }

        return back()->with('fail', 'The password is incorrect.');
    }

    public function changePassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('cashier')->user();

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('cashier_login')->with('success', 'Password updated successfully. Please re-login.');
    }

    /**
     * Show the cashier dashboard.
     */
    public function cashierDashboard()
    {
        // Total Sales Today excluding services
        $totalSalesToday = Transaction::whereDate('created_at', now())
            ->whereIn('status', ['Completed', 'Not Paid'])
            ->whereDoesntHave('serviceItems') // Exclude transactions with service items
            ->sum('total');
        
        // Sales by Payment Method (Cash, GCash, Credit), excluding services
        $cashSalesToday = Transaction::whereDate('created_at', now())
            ->where('status', 'Completed')
            ->where('payment_method', 'Cash')
            ->whereDoesntHave('serviceItems') // Exclude transactions with service items
            ->sum('total');
    
        $gcashSalesToday = Transaction::whereDate('created_at', now())
            ->where('status', 'Completed')
            ->where('payment_method', 'Gcash')
            ->whereDoesntHave('serviceItems') // Exclude transactions with service items
            ->sum('total');
    
        $creditSalesToday = Transaction::whereDate('created_at', now())
            ->where('status', 'Not Paid')
            ->where('payment_method', 'Credit')
            ->whereDoesntHave('serviceItems') // Exclude transactions with service items
            ->sum('total');
        
        // Daily Sales Data for Each Month excluding services
        $dailySalesData = Transaction::selectRaw('DAY(created_at) as day, MONTH(created_at) as month, SUM(total) as sales')
            ->whereIn('status', ['Completed', 'Paid'])
            ->whereDoesntHave('serviceItems') // Exclude transactions with service items
            ->groupBy('day', 'month')
            ->orderBy('month')
            ->get();
        
        // Prepare data for the chart
        $months = [];
        $dailySales = ['All' => []];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = \Carbon\Carbon::createFromFormat('m', $i)->format('M'); // Month abbreviation
            $daysInMonth = \Carbon\Carbon::createFromDate(now()->year, $i, 1)->daysInMonth;
    
            // Collect daily sales for the month
            $monthlySales = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $sales = $dailySalesData->where('month', $i)->where('day', $d)->sum('sales');
                $monthlySales[] = $sales; // Default to 0 if no sales data for the day
            }
            $dailySales[$i] = $monthlySales;
            $dailySales['All'] = array_merge($dailySales['All'], $monthlySales);
        }
    
        $users = User::with(['logs'])->get();
    
        return view('cashier.cashier_dashboard', [
            'totalSalesToday' => $totalSalesToday,
            'cashSalesToday' => $cashSalesToday,
            'gcashSalesToday' => $gcashSalesToday,
            'creditSalesToday' => $creditSalesToday,
            'months' => $months,
            'dailySales' => $dailySales,
            'users',
        ]);
    }
    
    /**
     * Logout the cashier.
     */
    public function cashierlogout(Request $request)
    {
         // Get the logged-in cashier's ID
         $userId = Auth::guard('cashier')->user()->id;
    
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
     
         // Log out the user and destroy the session
         Auth::logout();
         $request->session()->invalidate();
         $request->session()->regenerateToken();
     
         // Redirect to the cashier login page with a success message
         return redirect()->route('cashier_login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Save the transaction details.
     */
    public function saveTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'paymentMethod' => 'required|string|in:Cash,GCash,Credit',
            'cashTendered' => 'required_if:paymentMethod,Cash|nullable|numeric|min:0',
            'gcashReference' => 'required_if:paymentMethod,GCash|nullable|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'chargeType' => 'required_if:paymentMethod,Credit|in:Department,Employee',
            // Add validation rules for credit-specific fields
            'fullName' => 'required_if:chargeType,Department|nullable|string|max:255',
            'idNumber' => 'required_if:chargeType,Department|nullable|string|max:255',
            'contactNumber' => 'required_if:chargeType,Department|nullable|string|max:255',
            'department' => 'required_if:chargeType,Department|nullable|string|max:255',
            'facultyName' => 'required_if:chargeType,Employee|nullable|string|max:255',
            'facultyIdNumber' => 'required_if:chargeType,Employee|nullable|string|max:255',
            'facultyContactNumber' => 'required_if:chargeType,Employee|nullable|string|max:255',
        ]);
        // Additional validation: Ensure cashTendered is sufficient if paymentMethod is cash
        if ($request->paymentMethod === 'Cash' && $request->cashTendered < $request->total) {
            return response()->json(['success' => false, 'message' => 'Cash tendered is less than the total amount.'], 400);
        }

        $status = ($request->paymentMethod === 'Credit') ? 'Not Paid' : 'Completed';
    
        // Use DB Transaction to ensure data integrity
        DB::beginTransaction();
        try {
            // Process the transaction items
            foreach ($request->items as $item) {
                $cashierItem = Item::find($item['id']);
                
                if ($cashierItem) {
                    // Check stock and update item quantity
                    if ($cashierItem->qtyInStock < $item['quantity']) {
                        \Log::warning('Insufficient stock for item: ' . $cashierItem->item_name);
                        return response()->json(['success' => false, 'message' => 'Insufficient stock for item: ' . $cashierItem->item_name], 400);
                    }
    
                    $cashierItem->qtyInStock -= $item['quantity'];
    
                    if ($cashierItem->qtyInStock <= 0) {
                        $cashierItem->status = 'Out of Stock';
                    } elseif ($cashierItem->qtyInStock <= $cashierItem->low_stock_limit) {
                        $cashierItem->status = 'Low Stock';
                    } else {
                        $cashierItem->status = 'In Stock';
                    }
    
                    $cashierItem->save();
                } else {
                    \Log::error('Item not found: ' . $item['id']);
                    return response()->json(['success' => false, 'message' => 'Item not found: ' . $item['id']], 404);
                }
            }
    
            // Calculate change if payment method is cash
            $change = 0;
            if ($request->paymentMethod === 'Cash') {
                $change = $request->cashTendered - $request->total;
            }
    
            // Save the transaction details
            $transaction = Transaction::create([
                'transaction_no' => 'TRX' . time(),
                'user_id' => Auth::guard('cashier')->user()->id,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount,
                'total' => $request->total,
                'payment_method' => $request->paymentMethod,
                'cash_tendered' => $request->paymentMethod === 'Cash' ? $request->cashTendered : null,
                'gcash_reference' => $request->paymentMethod === 'GCash' ? $request->gcashReference : null,
                'charge_type' => $request->chargeType,
                'full_name' => $request->fullName,
                'id_number' => $request->chargeType === 'Department' ? $request->idNumber : ($request->chargeType === 'Employee' ? $request->facultyIdNumber : null),
                'contact_number' => $request->chargeType === 'Department' ? $request->contactNumber : ($request->chargeType === 'Employee' ? $request->facultyContactNumber : null),
                'department' => $request->department,
                'faculty_name' => $request->facultyName,     
                'status' => $status, 
                'change' => $change,
            ]);
    
            // Loop through items to save transaction items
            foreach ($request->items as $item) {
                $cashierItem = Item::find($item['id']);
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item['id'],
                    'item_name' => $cashierItem ? $cashierItem->item_name : 'Unknown Item',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);
            }
    
            DB::commit();
            return response()->json(['success' => true,  'transaction_no' =>'TRX' . time(), 'message' => 'Transaction saved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving transaction: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while saving the transaction.'], 500);
        }
    }
    

    /**
     * Fetch an item by barcode.
     */
    public function fetchItemByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $item = Item::where('barcode', $request->barcode)
                    ->where(function($query) {
                        $query->whereNull('expiration_date')
                              ->orWhere('expiration_date', '>', now());
                    })
                    ->first();

        if ($item) {
            return response()->json(['success' => true, 'item' => $item]);
        } else {
            return response()->json(['success' => false, 'message' => 'Item not found.']);
        }
    }

    /**
     * Fetch items and display the sales view.
     */
    public function fetchItem()
    {
        $items = Item::orderBy('item_name', 'ASC')->get();// Fetch all items or apply any necessary filtering
        $services = Services::orderBy('service_name', 'ASC')->get();
        return view('cashier.sales', compact('items','services')); // Pass items to the sales view
    }

    /**
     * Show void records view.
     */
    public function voidRecords(){
         // Fetch all void records from the database
         $voidRecords = VoidRecords::orderBy('voided_at', 'ASC')->get();

    // Pass the void records to the view
    return view('cashier.void_records', compact('voidRecords'));
    }

    public function saveVoidRecords(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string',
            'price' => 'required|numeric',
            'user_id' => 'required|exists:users,id', // Validate user ID
            'voided_by' => 'required|string', // Validate full name
        ]);
    
        try {
            // Save the void record
            \DB::table('void_records')->insert([
                'item_name' => $request->item_name,
                'price' => $request->price,
                'user_id' => $request->user_id, // Store user ID
                'voided_by' => Auth::guard('cashier')->user()->full_name, // Store full name
                'voided_at' => now(), // Use current timestamp
            ]);
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error saving void record: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save void record.'], 500);
        }
    }

    //fething the data onthe sales history
  public function salesHistory()
{
    // Fetch transactions that have TransactionItems and do not have ServiceItems
    $transactions = Transaction::whereHas('items') // Ensure the transaction has TransactionItems
        ->whereDoesntHave('serviceItems') // Exclude transactions that have ServiceItems
        ->select('transaction_no', 'created_at')
        ->orderBy('created_at', 'ASC')
        ->get();

    // Pass the filtered transactions to the view
    return view('cashier.sales_history', compact('transactions'));
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
        return view('cashier.void_report', compact('voidRecords', 'items', 'categories'));
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
        $userFullName = Auth::guard('cashier')->user()->full_name;
    
        // Generate the PDF with the filtered damage items
        $pdf = PDF::loadView('cashier.void_report_pdf', compact('voidRecords', 'startDate', 'endDate', 'itemName', 'category', 'userFullName'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);
    
        // Download the generated PDF
        return $pdf->stream('dwcc_college_bookstore_void_item_report.pdf');
    }

    public function exportVoidItemReportExcel(Request $request)
    {
        $userFullName = Auth::guard('cashier')->user()->full_name;
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
    
        // Pass the necessary filters to the export class
        return Excel::download(new VoidItemReportExport($startDate, $endDate, $itemName, $category), 'void_item_report.xlsx');
    }

    public function userProfile()
    {
        // Retrieve the currently authenticated user
        $user = Auth::guard('cashier')->user();
        return view('cashier.userProfile', compact('user'));
    }


    //this functiion is for credit transaction
    public function credit()
    {
        $creditTransactions = Transaction::where('payment_method', 'Credit')->get();
        return view('cashier.credit_transaction',compact('creditTransactions'));
    }

    public function finesTransaction()
    {
        // Fetch borrowers and their related borrowed items
        $borrowers = Borrower::with('borrowedItems.item')->get(); // Eager load borrowedItems and their related item
    return view('cashier.fines_transaction', compact('borrowers'));
    }
    
    public function returnItem(Request $request)
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
            $lateFeeItems = [];
            $processedItemIds = []; // To track processed items and prevent double counting
    
            foreach ($request->item_ids as $itemId) {
                // Skip if the item has already been processed
                if (in_array($itemId, $processedItemIds)) {
                    continue;
                }
                
                // Mark the item as processed
                $processedItemIds[] = $itemId;
    
                $borrowedItem = BorrowedItem::findOrFail($itemId);
                $borrowerId = $borrowedItem->borrower_id;
                $condition = $request->conditions[$itemId];
                $fee = $request->fees[$itemId] ?? 0;
                $returnDate = Carbon::parse($borrowedItem->return_date)->startOfDay();
                $currentDate = now()->startOfDay();
                $daysLate = 0;
                $lateFee = 0;
    
                if ($currentDate->greaterThan($returnDate)) {
                    $daysLate = $currentDate->diffInDays($returnDate);
                    $lateFee = $daysLate * $lateFeePerDay;
                }
    
                $itemData = [
                    'item_id' => $itemId,
                    'student_id' => $borrowedItem->borrower->student_number,
                    'student_name' => $borrowedItem->borrower->student_name,
                    'item_name' => $borrowedItem->item->item_name,
                    'days_late' => $daysLate,
                    'late_fee' => $lateFee,
                    'additional_fee' => $fee,
                    'total_fee' => $lateFee + $fee,
                    'condition' => $condition,
                ];
    
                if ($condition === 'Good') {
                    // Increment the quantity by 1 only once per unique item
                    $borrowedItem->item->decrement('quantity_borrowed', 1);      
                    $borrowedItem->actual_return_date = now();
                    $borrowedItem->status = 'Returned';
                    $borrowedItem->save();
    
                    // Add to late fee items only if late fee or additional fees are present
                    if ($daysLate > 0 || $fee > 0) {
                        $lateFeeItems[] = $itemData;
                    }
                    $processedItems++;
    
                } else {
                    // Handle items with problems like Damaged or Lost
                    $borrowedItem->item->decrement('quantity_borrowed', 1);
                    $lateFeeItems[] = $itemData;
                    $problematicItems[] = $itemData;
                }
            }
    
            if (!empty($problematicItems) && !empty($lateFeeItems)) {
                return redirect()->back()->with([
                    'late_fee_modal' => true,
                    'late_fee_items' => $lateFeeItems,
                    'problematic_items' => $problematicItems
                ]);
            }
    
            // If there are items with late fees in Good condition, prompt for payment
            if (!empty($lateFeeItems)) {
                return redirect()->back()->with([
                    'late_fee_modal' => true,
                    'late_fee_items' => $lateFeeItems,
                ]);
            }
    
            if ($processedItems > 0) {
                $remainingItems = BorrowedItem::where('borrower_id', $borrowerId)
                    ->where('status', '!=', 'Returned')
                    ->count();
    
                if ($remainingItems === 0) {
                    Borrower::destroy($borrowerId);
                }
    
                return redirect()->back()->with('success', 'Good condition item(s) returned successfully.');
            }
    
            return redirect()->back()->with('info', 'No items were processed.');
        } catch (\Exception $e) {
            \Log::error('Return Item Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing the return.');
        }
    }
    
    
    public function payLateFees(Request $request)
    {
        $validated = $request->validate([
            'late_fee_items' => 'required|array|min:1',
            'late_fee_items.*.item_id' => 'exists:borrowed_items,id',
            'late_fee_items.*.total_fee' => 'required|numeric|min:0',
            'late_fee_items.*.days_late' => 'required|integer|min:0',
            'payment_method' => 'required|in:cash,gcash',
            'gcash_reference_number_hidden' => 'required_if:payment_method,gcash',
            'cash_tendered_hidden' => 'required_if:payment_method,cash|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
        ]);

        
    
        try {
            $paymentMethod = $request->payment_method;
            $gcashReferenceNumber = $request->gcash_reference_number_hidden ?? null;
            $cashTendered = $request->cash_tendered_hidden ?? 0;
            $changeAmount = $request->change_amount ?? 0;
            $cashierName = Auth::guard('cashier')->user()->full_name;
            // Group late fee items by borrower ID for efficient processing
            $lateFeeItemsGrouped = collect($request->late_fee_items)->groupBy(function ($item) {
                return BorrowedItem::find($item['item_id'])->borrower_id;
            });
    
            \DB::beginTransaction();
    
            foreach ($lateFeeItemsGrouped as $borrowerId => $items) {
                $borrower = Borrower::findOrFail($borrowerId);
    
                foreach ($items as $item) {
                    $borrowedItem = BorrowedItem::findOrFail($item['item_id']);
    
                    // Record in FinesHistory
                    FinesHistory::create([
                        'student_id' => $borrower->student_number,
                        'student_name' => $borrower->student_name,
                        'item_borrowed' => $borrowedItem->item->item_name,
                        'quantity' => 1,
                        'days_late' => $item['days_late'],
                        'fines_amount' => $item['total_fee'],
                        'payment_method' => ucfirst($paymentMethod),
                        'cash_tendered' => $paymentMethod === 'cash' ? $cashTendered : 0,
                        'change' => $paymentMethod === 'cash' ? $changeAmount : 0,
                        'gcash_reference_number' => $paymentMethod === 'gcash' ? $gcashReferenceNumber : null,
                        'actual_return_date' => now(),
                        'condition' => $item['condition'] ?? 'Good',
                        'borrowed_date' => $borrowedItem->borrowed_date, // Include borrowed_date
                        'expected_return_date' => $borrowedItem->return_date, // Include return_date
                        'cashier_name' => $cashierName,
                    ]);
                    
    
                    $borrowedItem->update(['status' => 'Returned']);
                }
    
                // Check if all items for this borrower are returned
                $remainingItems = BorrowedItem::where('borrower_id', $borrowerId)
                    ->where('status', '!=', 'Returned')
                    ->count();
    
                if ($remainingItems === 0) {
                    $borrower->delete();
                }
            }
    
            \DB::commit();
            return redirect()->back()->with('success', 'Late fees paid and items returned successfully.');
    
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Pay Late Fees Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while processing the late fee payment.');
        }
    }
    
    
    public function processPayment(Request $request)
    {
        // Validate the payment input
        $validated = $request->validate([
            'total_fee' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,gcash',
            'gcash_reference_number_hidden' => 'required_if:payment_method,gcash',
            'cash_tendered_hidden' => 'required_if:payment_method,cash|numeric|min:0',
            'change_amount' => 'required|numeric|min:0',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:borrowed_items,id',
            'conditions' => 'required|array',
            'conditions.*' => 'in:Good,Damaged,Lost',
            'fees' => 'nullable|array',
            'fees.*' => 'nullable|numeric|min:0',
        ]);
    
        try {
            $totalFee = $request->total_fee;
            $paymentMethod = $request->payment_method;
            $gcashReferenceNumber = $request->gcash_reference_number_hidden;
            $cashTendered = $request->cash_tendered_hidden;
            $changeAmount = $request->change_amount;
    
            foreach ($request->item_ids as $itemId) {
                $borrowedItem = BorrowedItem::findOrFail($itemId);
                $condition = $request->conditions[$itemId];
                $fee = $request->fees[$itemId] ?? 0;
    
                // Calculate late fees
                $returnDate = Carbon::parse($borrowedItem->return_date)->startOfDay();
                $currentDate = now()->startOfDay();
                $daysLate = 0;
                $lateFee = 0;
    
                if ($currentDate->greaterThan($returnDate)) {
                    $daysLate = $currentDate->diffInDays($returnDate);
                    $lateFee = $daysLate * 10; // Assuming 10 PHP per day
                }
    
                // Update total_quantity for "Good" condition
                if ($condition === 'Good') {
                    $borrowedItem->item->increment('total_quantity');
                }
    
                // Update BorrowedItem
                $borrowedItem->actual_return_date = now();
                $borrowedItem->status = 'Returned';
                $borrowedItem->save();
    
                // Record in FinesHistory
                FinesHistory::create([
                    'student_id' => $borrowedItem->borrower->student_number,
                    'student_name' => $borrowedItem->borrower->student_name,
                    'item_borrowed' => $borrowedItem->item->item_name,
                    'quantity' => 1, // Assuming one item per record
                    'days_late' => $daysLate,
                    'fines_amount' => $lateFee + $fee,
                    'payment_method' => ucfirst($paymentMethod),
                    'cash_tendered' => $paymentMethod === 'cash' ? $cashTendered : 0,
                    'change' => $paymentMethod === 'cash' ? $changeAmount : 0,
                    'gcash_reference_number' => $paymentMethod === 'gcash' ? $gcashReferenceNumber : null,
                ]);
            }
    
            // Optionally, delete the borrower if no more borrowed items
            $borrowerId = BorrowedItem::where('borrower_id', $borrowedItem->borrower_id)
                ->where('status', '!=', 'Returned')
                ->count();
    
            if ($borrowerId === 0) {
                Borrower::find($borrowedItem->borrower_id)->delete();
            }
    
            return redirect()->back()->with('success', 'Payment processed and items returned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while processing the payment.');
        }
    }
    

    public function finesHistory()
    {
        $finesHistory = FinesHistory::with('borrower')->get(); 
        return view('cashier.fines_history', compact('finesHistory'));
    }

 

    public function getTransactionItems(Request $request)
    {
        $transactionNo = $request->transaction_no;
    
        // Fetch the transaction by transaction_no and eager load its items and user (cashier)
        $transaction = Transaction::with(['items', 'user'])
            ->where('transaction_no', $transactionNo)
            ->first();
    
        // If no transaction is found, return an error response
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ]);
        }
    
        // Structure the transaction items data
        $transactionItems = $transaction->items->map(function ($item) {
            return [
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'price' => number_format($item->price, 2),
                'total' => number_format($item->total, 2),
            ];
        });
    
        // Prepare the transaction details to return in the response
        $subtotal = number_format($transaction->subtotal, 2);
        $discount = number_format($transaction->discount, 2);
        $total = number_format($transaction->total, 2);
        $paymentMethod = $transaction->payment_method;
        $cashTendered = number_format($transaction->cash_tendered, 2);
        $change = number_format($transaction->change, 2);
        $gcashReference = $paymentMethod === 'GCash' ? $transaction->gcash_reference : null;
        $chargeType = $transaction->charge_type ?? 'N/A';
        $status = $transaction->status;
    
        // Prepare charge details (if applicable)
        $chargeDetails = null;
        if ($chargeType === 'Department') {
            $chargeDetails = [
                'full_name' => $transaction->full_name,
                'id_number' => $transaction->id_number,
                'contact_number' => $transaction->contact_number,
                'department' => $transaction->department,
            ];
        } elseif ($chargeType === 'Employee') {
            $chargeDetails = [
                'faculty_name' => $transaction->faculty_name, // Note: Ensure this is retrieved and assigned from the database
                'facultyIdNumber' => $transaction->id_number, // Use proper property name, ensure it exists
                'facultyContactNumber' => $transaction->contact_number, // Use proper property name, ensure it exists
                    ];
        }
    
        // Prepare the response data
        return response()->json([
            'success' => true,
            'transaction_items' => $transactionItems,
            'transaction_date_time' => $transaction->created_at->format('m-d-Y h:i:s a'),
            'payment_method' => $paymentMethod,
            'cashier_name' => $transaction->user ? $transaction->user->full_name : 'N/A',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'cash_tendered' => $cashTendered,
            'change' => $change,
            'gcash_reference' => $gcashReference,
            'charge_type' => $chargeType,
            'charge_details' => $chargeDetails, // Send charge details if applicable
            'status' => $status, 
        ]);
    }

    public function returns()
    {
        // Retrieve all returned items from the database
        $returnedItems = \App\Models\ReturnedItem::all();
    
        // Pass the data to the view
        return view('cashier.return_transaction', ['returnedItems' => $returnedItems]);
    }

    public function fetchTransactionItems(Request $request)
    {
        $transactionNo = $request->input('transaction_no');

        // Fetch the transaction with its items
        $transaction = Transaction::with('items')
            ->where('transaction_no', $transactionNo)
            ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Map the items for the frontend
        $items = $transaction->items->map(function ($item) {
            return [
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'type' => 'Replacement',
                'replacement_item' => $item->item_name,
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function processReturn(Request $request)
    {
        $validated = $request->validate([
            'transaction_no' => 'required',
            'selected_items' => 'required|array',
            'selected_items.*' => 'integer',
            'return_quantity.*' => 'nullable|numeric|min:1',
            'reason.*' => 'nullable|string',
        ]);
    
        $selectedItems = $request->input('selected_items');
    
        foreach ($selectedItems as $index) {
            $returnQuantity = $request->input("return_quantity.$index");
            $reason = $request->input("reason.$index");
            $replacementItem = $request->input("replacement_item.$index");
    
            // Process return logic
            // Example: Log return details, update stock, etc.
            ReturnedItem::create([
                'transaction_no' => $request->transaction_no,
                'item_name' => $replacementItem,
                'return_quantity' => $returnQuantity,
                'reason' => $reason,
                'replacement_item' => $replacementItem,
            ]);
    
            // Update stock levels
            $item = Item::where('item_name', $replacementItem)->first();
            if ($item) {
                $item->qtyInStock -= $returnQuantity;
                $item->save();
            }
        }
    
        return redirect()->back()->with('success', 'Selected items returned successfully.');
    } 

    public function getCreditTransactionDetails($id)
    {
        try {
            // Find the transaction and eager load related data
            $transaction = Transaction::with([
                'items',             // Relation for regular POS items
                'serviceItems.service' // Relation for service items (and nested service details)
                                       // Add 'department' or 'faculty' relations if needed for charge_to details
                                       // and they are not already directly on the transaction model
                // 'department',
                // 'faculty'
             ])->findOrFail($id);


            // Prepare data for JSON response
            $responseData = [
                'success' => true,
                'transaction_no' => $transaction->transaction_no,
                'created_at' => $transaction->created_at->format('m-d-Y h:i:s a'),
                'charge_type' => $transaction->charge_type,
                'status' => $transaction->status,

                // Department Info (if applicable)
                'department' => $transaction->charge_type == 'Department' ? $transaction->department : null,
                'full_name' => $transaction->charge_type == 'Department' ? $transaction->full_name : null,
                'id_number' => $transaction->id_number, // Assuming ID number is common
                'contact_number' => $transaction->contact_number, // Assuming contact number is common

                // Faculty Info (if applicable)
                'faculty_name' => $transaction->charge_type == 'Faculty' ? $transaction->faculty_name : null,

                // Items
                'items' => $transaction->items->map(function ($item) {
                    return [
                        'item_name' => $item->item_name, // Adjust field names if needed
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                    ];
                }),

                // Service Items
                'serviceItems' => $transaction->serviceItems->map(function ($serviceItem) {
                     return [
                        'service_name' => $serviceItem->service->service_name ?? 'N/A', // Access nested relation
                        'service_type' => $serviceItem->service_type,
                        'number_of_copies' => $serviceItem->number_of_copies,
                        'number_of_hours' => $serviceItem->number_of_hours,
                        'price' => $serviceItem->price,
                        'total' => $serviceItem->total,
                    ];
                }),

                // Calculate total amount
                'totalAmount' => $transaction->items->sum('total') + $transaction->serviceItems->sum('total'),
            ];

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error("Error fetching credit transaction details for ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not retrieve transaction details.'], 500);
        }
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
    
        return view('cashier.return_item_report', compact('returnedItems', 'items', 'categories'));
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
        $userFullName = Auth::guard('cashier')->user()->full_name;
    
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
            new ReturnedItemsReportExport($itemName, $startDate, $endDate, $categoryName),
            'Returned_Items_Report.xlsx'
        );
    }

    public function sales_report(Request $request)
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
        return view('cashier.sales_report', compact('transactions', 'categories', 'items', 'totalSales', 'paymentMethod', 'category', 'itemName'));
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
    
        // Retrieve cashier name
        $userFullName = Auth::guard('cashier')->user()->full_name;
    
        // Generate PDF
        $pdf = Pdf::loadView('cashier.sales_report_pdf', compact(
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
    
        return Excel::download(new SalesReportExport($transactions, $startDate, $endDate, $categoryName, $selectedPaymentMethod, $itemName, $categoryId), 'sales_report.xlsx');
    }
    
    
    public function services()
    {
        $services = Services::orderBy('service_name', 'ASC')->get();
        return view('cashier.services', compact('services'));
    }

    public function saveServices(Request $request)
{
    // Validate incoming request
    $request->validate([
        'services' => 'required|array|min:1',
        'services.*.id' => 'required|exists:services,id',
        'services.*.name' => 'required|string',
        'services.*.service_type' => 'required|string',
        'services.*.price' => 'required|numeric|min:0',
        'services.*.feeStructure' => 'required|string|in:per_copy,per_hour,fee_amount',
        'services.*.number_of_copies' => 'nullable|integer|min:1',
        'services.*.number_of_hours' => 'nullable|numeric|min:0.1',
        'services.*.total' => 'required|numeric|min:0',
        'paymentMethod' => 'required|string|in:Cash,GCash,Credit',
        'cashTendered' => 'required_if:paymentMethod,Cash|nullable|numeric|min:0',
        'gcashReference' => 'required_if:paymentMethod,GCash|nullable|string|max:255',
        'subtotal' => 'required|numeric|min:0',
        'discount' => 'required|numeric|min:0',
        'total' => 'required|numeric|min:0',
        'chargeType' => 'required_if:paymentMethod,Credit|in:Department,Employee',
        // Add validation rules for credit-specific fields
        'fullName' => 'required_if:chargeType,Department|nullable|string|max:255',
        'idNumber' => 'required_if:chargeType,Department|nullable|string|max:255',
        'contactNumber' => 'required_if:chargeType,Department|nullable|string|max:255',
        'department' => 'required_if:chargeType,Department|nullable|string|max:255',
        'facultyName' => 'required_if:chargeType,Employee|nullable|string|max:255',
        'facultyIdNumber' => 'required_if:chargeType,Employee|nullable|string|max:255',
        'facultyContactNumber' => 'required_if:chargeType,Employee|nullable|string|max:255',
    ]);

    // Ensure cashTendered is sufficient if paymentMethod is cash
    if ($request->paymentMethod === 'Cash' && $request->cashTendered < $request->total) {
        return response()->json(['success' => false, 'message' => 'Cash tendered is less than the total amount.'], 400);
    }

    // Set status based on payment method
    $status = ($request->paymentMethod === 'Credit') ? 'Not Paid' : 'Completed';
    $change = 0;

    if ($request->paymentMethod === 'Cash') {
        $change = $request->cashTendered - $request->total;
    }

    // Start database transaction
    DB::beginTransaction();

    try {
        // Create the Transaction
        $transaction = Transaction::create([
            'transaction_no' => 'TRX' . time(), // Robust transaction number
            'user_id' => Auth::guard('cashier')->user()->id,
            'subtotal' => $request->subtotal,
            'discount' => $request->discount,
            'total' => $request->total,
            'payment_method' => $request->paymentMethod,
            'cash_tendered' => $request->paymentMethod === 'Cash' ? $request->cashTendered : null,
            'gcash_reference' => $request->paymentMethod === 'GCash' ? $request->gcashReference : null,
            'charge_type' => $request->chargeType,
            'full_name' => $request->fullName,
            'id_number' => $request->chargeType === 'Department' ? $request->idNumber : ($request->chargeType === 'Employee' ? $request->facultyIdNumber : null),
            'contact_number' => $request->chargeType === 'Department' ? $request->contactNumber : ($request->chargeType === 'Employee' ? $request->facultyContactNumber : null),
            'department' => $request->department,
            'faculty_name' => $request->facultyName,
            'status' => $status,
            'change' => $change,
        ]);

        // Save services to the transaction
        foreach ($request->services as $service) {
            ServiceItem::create([
                'transaction_id' => $transaction->id,
                'service_id' => $service['id'],
                'service_name' => $service['name'],
                'service_type' => $service['service_type'],
                'price' => $service['price'],
                'fee_structure' => $service['feeStructure'],
                'number_of_copies' => $service['number_of_copies'] ?? null,
                'number_of_hours' => $service['number_of_hours'] ?? null,
                'total' => $service['total'],
            ]);
        }

        // Commit the transaction
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Transaction saved successfully.',
            'transaction_no' => $transaction->transaction_no,
        ]);

    } catch (\Exception $e) {
        // Rollback if there is an error
        DB::rollBack();

        // Log the error with details
        \Log::error('Error saving services transaction: ', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'An error occurred while saving the transaction. Please try again.',
        ], 500);
    }
}

public function servicesHistory()
{
    // Fetch transactions that have related service items
    $transactions = Transaction::whereHas('serviceItems')->orderBy('created_at', 'ASC')->get();

    return view('cashier.services_history', compact('transactions'));
}

/**
 * Fetch the details of a specific service transaction.
 */
public function getServiceTransactionItems(Request $request)
{
    $transactionNo = $request->input('transaction_no');

    $transaction = Transaction::where('transaction_no', $transactionNo)
        ->with('serviceItems')
        ->with('user') // Assuming a 'user' relationship exists
        ->first();

    if (!$transaction) {
        return response()->json(['success' => false, 'message' => 'Transaction not found.']);
    }

    // Prepare the data
    $response = [
        'success' => true,
        'transaction_no' => $transaction->transaction_no,
        'cashier_name' => $transaction->user->full_name, // Assuming 'full_name' exists
        'transaction_date_time' => $transaction->created_at->format('m-d-Y h:i:s a'),
        'payment_method' => $transaction->payment_method,
        'discount' => $transaction->discount,
        'total' => $transaction->total,
        'cash_tendered' => $transaction->cash_tendered,
        'change' => $transaction->change,
        'gcash_reference' => $transaction->gcash_reference,
        'charge_type' => $transaction->charge_type,
        'charge_details' => [
            'full_name' => $transaction->full_name,
            'id_number' => $transaction->charge_type === 'Department' ? $transaction->id_number : ($transaction->charge_type === 'Employee' ? $transaction->id_number : null),
            'contact_number' => $transaction->charge_type === 'Department' ? $transaction->contact_number : ($transaction->charge_type === 'Employee' ? $transaction->contact_number : null),
            'department' => $transaction->department,
            'faculty_name' => $transaction->faculty_name,
        ],
        'status' => $transaction->status,
        'transaction_items' => $transaction->serviceItems->map(function ($item) {
            return [
                'item_name' => $item->service ? $item->service->service_name : 'N/A',
                'service_type' => $item->service_type,
                'number_of_copies' => $item->number_of_copies, // Added
                'number_of_hours' => $item->number_of_hours,   // Added
                'price' => number_format($item->price, 2),
                'total' => number_format($item->total, 2),
            ];
        }),
        'total_quantity' => $transaction->serviceItems->sum(function ($item) {
            return $item->number_of_copies ?? $item->number_of_hours ?? 1;
        }),
    ];

    return response()->json($response);
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

    $totalFines = $query->sum('fines_amount');
    // Get all items for dropdown
    $items = ItemForRent::pluck('item_name', 'item_name');

    return view('cashier.togafines_report', compact('finesReport', 'items', 'totalFines'));
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

    $pdf = Pdf::loadView('cashier.togafines_report_pdf', compact('finesReport', 'totalFines', 'startDate', 'endDate', 'itemName', 'condition', 'paymentMethod'))
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
    return Excel::download(new FinesReportExport($startDate, $endDate, $itemName, $condition, $paymentMethod), 'Toga_Fines_Report.xlsx');
}


}