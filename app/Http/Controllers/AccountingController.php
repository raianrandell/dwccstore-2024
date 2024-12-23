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

class AccountingController extends Controller
{
    public function accountinglogin(){
        return view("accounting.accounting_login");
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
    
            // Check if the user has the 'Inventory' role
            if ($user->user_role !== 'Accounting') {
                return back()->with('fail', 'You are not authorized to access this section.');
            }else if ($user->user_status !== 'Active'){
                return back()->with('fail', 'This account is inactive, please contact the administrator');
            }
            
    
            // Attempt to authenticate the user with the password
            if (Auth::attempt($credentials)) {
                // Authentication passed, redirect to inventory login with message
                return redirect()->route('accounting.chargeTransaction')->with('success', 'Login Successful');
            }
    
    
            // If password is incorrect
            return back()->with('fail', 'The password is incorrect.');
        }
    
    
        public function accountinglogout(Request $request)
        {
            Auth::logout();
        
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        
            return redirect()->route('accounting_login')->with('success', 'You have been logged out successfully.');
        }  
        
        public function chargeTransaction()
        {
            $transactions = Transaction::where('payment_method', 'credit')->get();
        
            return view('accounting.charge_transaction', compact('transactions'));
        } 
        public function getTransactionDetails($transactionId)
        {
            // Find the transaction along with its related items and service items
            $transaction = Transaction::with(['items', 'serviceItems.service'])->findOrFail($transactionId);
            $items = $transaction->items;
            $serviceItems = $transaction->serviceItems;
            $totalAmount = $items->sum('total') + $serviceItems->sum('total'); // Adjust total calculation
        
            // Prepare the response data
            $transactionDetails = [
                'chargeType' => $transaction->charge_type,
                'department' => $transaction->department,
                'full_name' => $transaction->full_name,
                'faculty_name' => $transaction->faculty_name,
                'id_number' => $transaction->id_number,
                'contact_number' => $transaction->contact_number,
                'items' => $items,
                'serviceItems' => $serviceItems->map(function($serviceItem) {
                    return [
                        'service_type' => $serviceItem->service_type,
                        'number_of_copies' => $serviceItem->number_of_copies ?? 0, // Ensure default value
                        'number_of_hours' => $serviceItem->number_of_hours ?? 0,   // Ensure default value
                        'price' => $serviceItem->price,
                        'total' => $serviceItem->total,
                        'service' => $serviceItem->service
                    ];
                }),
                'totalAmount' => $totalAmount // Send as number, format on frontend
            ];
        
            return response()->json($transactionDetails);
        }
        
        

        public function updateTransactionStatus(Request $request, $id)
        {
            $transaction = Transaction::find($id);
        
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found.']);
            }
        
            $cashPayment = $request->input('cashPayment');
        
            if ($cashPayment < $transaction->total) {
                return response()->json(['success' => false, 'message' => 'Payment amount is less than the total.']);
            }
        
            $transaction->status = 'Paid'; // Mark as Paid
            $transaction->cash_tendered = $cashPayment;
            $transaction->change = $cashPayment - $transaction->total;
            $transaction->save();
        
            return response()->json(['success' => true, 'message' => 'Transaction updated successfully.']);
        }
        

        

        public function damageItems(Request $request)
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
        
            return view('accounting.damage_items_report', compact('damageItems', 'startDate', 'endDate', 'uniqueItemNames', 'uniqueCategories'));
        }

        public function salesReport(Request $request)
        {
            // Retrieve filter values
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $category = $request->input('category');
            $paymentMethod = $request->input('payment');
        
            // Query Transactions with filters
            $transactions = Transaction::with(['items', 'user'])
                ->when($startDate, function ($query, $startDate) {
                    return $query->whereDate('created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query, $endDate) {
                    return $query->whereDate('created_at', '<=', $endDate);
                })
                ->when($paymentMethod, function ($query, $paymentMethod) {
                    return $query->where('payment_method', $paymentMethod);
                })
                ->whereHas('items', function ($query) use ($category) {
                    $query->when($category, function ($query, $category) {
                        $query->whereHas('item', function ($query) use ($category) {
                            $query->where('cat_id', $category);
                        });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();
        
            // Calculate total sales
            $totalSales = $transactions->sum('total');
        
            // Retrieve categories as key-value pairs
            $categories = Category::pluck('category_name', 'id');
        
            // Pass data to the view
            return view('accounting.sales_report', compact('transactions', 'categories', 'totalSales', 'paymentMethod', 'category'));
        }

        public function returnReport(Request $request)
        {
             // Fetch query parameters for filtering
            $itemName = $request->get('item_name');
    
             // Fetch all unique items for the dropdown
            $items = DB::table('returned_items')
            ->select('item_name')
            ->distinct()
            ->orderBy('item_name', 'asc')
            ->get();
    
            // Build the query
            $query = ReturnedItem::with(['item.category'])
                ->select([
                    'transaction_no',
                    'item_name',
                    'return_quantity',
                    'reason',
                    'replacement_item',
                ]);
    
            // Apply filters if provided
            if (!empty($itemName)) {
                $query->where('item_name', 'LIKE', "%$itemName%");
            }
    
            // Execute the query and get the results
            $returnedItems = $query->get();
    
            return view('accounting.returned_item_report', compact('returnedItems', 'items'));
        }

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
    
            $items = Item::all();
            $categories = Category::all();
        
            // Pass the records and filters to the view
            return view('accounting.void_report', compact('voidRecords', 'items', 'categories'));
        }
}