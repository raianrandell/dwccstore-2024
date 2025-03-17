<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserLog;
use Session;

class SuperAdminController extends Controller
{
    public function superadminlogin(){
        // Check if Super Admin exists
        $existingSuperAdmin = User::where('username', 'administrator')->first();
    
        if (!$existingSuperAdmin) {
            // Create a default Super Admin user
            $superAdmin = new User();
            $superAdmin->full_name = 'SUPERADMIN';
            $superAdmin->emp_id = 'administrator';
            $superAdmin->username = 'administrator';
            $superAdmin->password = Hash::make('@dwcc2025'); // Securely hash the password
            $superAdmin->user_role = 'Super Admin';
            $superAdmin->user_status = 'Active';
            $superAdmin->save();
        }
    
        return view("auth.superadminlogin");
    }
    

    public function superadminregistration(){
        return view("auth.superadminregistration");
    }

    //superadmin registration
    public function register_superadmin(Request $request){
        $request->validate([
            'full_name'=>'required',
            'emp_id'=>'required|unique:users',
            'username'=>'required',
            'password'=>'required|min:5|max:12',
            'confirmpassword'=>'required|same:password',
        ]);

        $user = new User();
        $user->full_name = $request->full_name;
        $user->emp_id = $request->emp_id;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->user_role = 'Super Admin';
        $user->user_status = 'Active';
        $res = $user->save();

        if ($res){
            return back()->with('success', 'Account created sucessfully!');
        }
        else{
            return back()->with('fail', 'Something Wrong!');
        }
    }

    //superadmin login function
    public function login_superadmin(Request $request){
        // Validate input
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:5|max:12',
        ]);

        // Check if the user exists
        $user = User::where('username', $request->username)->first();

        if ($user) {
            // Check if the password matches
            if (Hash::check($request->password, $user->password)) {
                // Store user ID in session
                $request->session()->put('loginId', $user->id);

                // Redirect to the dashboard after login
                return redirect()->route('superadmin.dashboard')->with('success', 'Login Successful');
            } else {
                return back()->with('fail', 'Incorrect Password!');
            }
        } else {
            return back()->with('fail', 'Username is not registered!');
        }
    }

    //superadmin dashboard function 
    public function superadmin_dashboard(){
        // Retrieve the logged-in user's ID from the session
        $userId = Session::get('loginId');

        // Check if the user is logged in
        if ($userId) {
            $loggedInUser = User::find($userId); // Fetch user by ID
        } else {
            return redirect('superadminlogin')->with('fail', 'You must be logged in.');
        }

        // **Calculate the counts**
        $totalUsers = User::where('user_role', '!=', 'Super Admin')->count();
        $activeUsers = User::where('user_status', 'Active')->where('user_role', '!=', 'Super Admin')->count();
        $inactiveUsers = User::where('user_status', 'Inactive')->where('user_role', '!=', 'Super Admin')->count();

        // **Pass the counts to the view**
        return view('auth.superadmin_dashboard', compact('loggedInUser', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    //superadmin user_management function 
    public function user_management()
    {
        $users = User::with(['logs'])->get(); // Retrieve all users
        $userId = Session::get('loginId');
    
        if ($userId) {
            $loggedInUser = User::find($userId);
        } else {
            return redirect('superadminlogin')->with('fail', 'You must be logged in.');
        }
    
        return view("auth.user_management", compact('users', 'loggedInUser'));
    }
    

    public function add_user(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'employeeId' => 'required|string|max:255|unique:users,emp_id',
            'fullName' => 'required|string|max:255',
            'role' => 'required|in:Cashier,Inventory,Admin,Accounting',
            'status' => 'required|in:Active,Inactive',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:5|max:12|confirmed',
        ], [
            // Custom error messages (optional)
            'employeeId.required' => 'Employee ID is required.',
            'employeeId.unique' => 'This Employee ID is already in use.',
            'password.confirmed' => 'The password confirmation does not match.',
            // Add more custom messages as needed
        ]);
    
        // Create and save the new user
        $user = new User();
        $user->emp_id = $validatedData['employeeId'];
        $user->full_name = $validatedData['fullName'];
        $user->user_role = $validatedData['role'];
        $user->user_status = $validatedData['status'];
        $user->username = $validatedData['username'];
        $user->password = Hash::make($validatedData['password']);
        $user->save();
    
        // Redirect back with success message
        return redirect()->back()->with('success', 'User added successfully!');
    }

    public function logout(Request $request)
    {
        // Destroy the user session
        $request->session()->forget('loginId');
        $request->session()->flush();

        // Redirect to login page
        return redirect('/superadminlogin')->with('success', 'You have been logged out successfully.');
    }
    
    public function edit_user(Request $request, $id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);
    
        // Validate the request data
        $validatedData = $request->validate([
            'fullName' => 'required|string|max:255',
            'role' => 'required|in:Cashier,Inventory,Admin,Accounting',
            'status' => 'required|in:Active,Inactive',
            'username' => 'required|string|max:255|unique:users,username,' . $id,  // Add validation for the username
        ]);
    
        // Update the user
        $user->full_name = $validatedData['fullName'];
        $user->user_role = $validatedData['role'];
        $user->user_status = $validatedData['status'];
        $user->username = $validatedData['username']; 
        $user->save();
    
        // Redirect back with success message
        return redirect()->back()->with('success', 'User updated successfully!');
    }

    public function change_password(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'newPassword' => 'required|string|min:5|max:12|confirmed',
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        // Update the user's password
        $user->password = Hash::make($validatedData['newPassword']);
        $user->save();

        // Redirect back with success message
        return redirect()->back()->with('success', 'Password changed successfully!');
    }
      
}
