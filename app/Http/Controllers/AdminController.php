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


class AdminController extends Controller
{
    // Admin Login Page
    public function adminlogin(){
        return view("admin.admin_login");
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

        // Attempt to authenticate the user with the credentials
        if (Auth::attempt($credentials)) {
            // Authentication passed, login the user and redirect
            $user = Auth::user(); // Get the authenticated user
            session(['loginId' => $user->id]); // Store user ID in session
            return redirect()->route('admin.dashboard')->with('success', 'Login Successful');
        }

        // If password is incorrect
        return back()->with('fail', 'The password is incorrect.');
    }

    // Admin Dashboard function
    public function adminDashboard()
    {
        // Get the currently authenticated user
        $loggedInUser = Auth::user();

        // If the user is not logged in, redirect to login page
        if (!$loggedInUser) {
            return redirect('adminlogin')->with('fail', 'You must be logged in.');
        }

       

        // Pass the counts to the view
        return view('admin.admin_dashboard', compact('loggedInUser'));
    }

    // Admin Logout function
    public function adminlogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.logout')->with('success', 'You have been logged out successfully.');
    }

    public function togaFines()
    {
        // Retrieve all borrowers from the database
        $borrowers = Borrower::all();
        $items = ItemForRent::all();

        return view('admin.toga_fines', compact('borrowers', 'items'));
    }

    public function addBorrower(Request $request)
    {
        // Validate request inputs
        $request->validate([
            'student_id' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'item_ids' => 'required|array', // Ensure exactly 3 items are selected
            'item_ids.*' => 'exists:item_for_rent,id',
            'date_issued' => 'required|date|before_or_equal:today',
            'expected_date_returned' => 'required|date|after:date_issued',
        ], [
            'item_ids.size' => 'You must select exactly 3 items.',
        ]);
    
        // Check if the student has already borrowed items
        $existingBorrower = Borrower::where('student_id', $request->student_id)->first();
        if ($existingBorrower) {
            return redirect()->back()->withErrors([
                'student_id' => 'This student has already borrowed items. Please return the previous items before borrowing again.',
            ]);
        }
    
        // Check availability of selected items
        $items = ItemForRent::whereIn('id', $request->item_ids)->get();
        $borrowedItems = [];
        $totalQuantity = count($items); // This is the number of items borrowed (should be 3)
    
        foreach ($items as $item) {
            $availableQuantity = $item->total_quantity - $item->quantity_borrowed;
            if ($availableQuantity <= 0) {
                return redirect()->back()->withErrors([
                    'item_ids' => "Sorry, '{$item->item_name}' is no longer available for borrowing.",
                ]);
            }
    
            $borrowedItems[] = $item->item_name;
    
            // Update the borrowed quantity for the item
            $item->increment('quantity_borrowed');
        }
    
        // Create the borrowing record and store the number of borrowed items
        Borrower::create([
            'student_id' => $request->student_id,
            'student_name' => $request->student_name,
            'item_names' => implode(', ', $borrowedItems), // Store item names as a comma-separated string
            'quantity' => $totalQuantity, // Store the total number of borrowed items (should be 3)
            'date_issued' => $request->date_issued,
            'expected_date_returned' => $request->expected_date_returned,
        ]);
    
        // Return success message
        return redirect()->route('admin.toga_fines')->with('success', 'Items borrowed successfully!');
    }  
    
    public function returnBorrower(Request $request, $id)
    {
        $borrower = Borrower::findOrFail($id);

        // Process returned items
        $returnedItems = $request->input('items', []);
        foreach ($returnedItems as $itemData) {
            if (!empty($itemData['returned'])) {
                $itemName = $itemData['returned']; // Get item name
                $condition = $itemData['condition']; // Get condition

                // Update inventory or save the return status
                logger("Item: {$itemName}, Condition: {$condition}");
            }
        }

        // Update the borrower return date
        $borrower->actual_date_returned = now();
        $borrower->save();

        return redirect()->route('admin.toga_fines')->with('success', 'Items returned successfully!');
    }

    
  
    
}
