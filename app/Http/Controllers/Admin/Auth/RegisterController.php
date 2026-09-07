<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function index(){
        $title = 'register';
        return view('admin.auth.register',compact('title'));
    }

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email', // Ensure email is unique
            'password' => 'required|max:200|confirmed',
        ]);

        // Create the user but do not log them in yet
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'approved' => false, // Set user approval status to false
        ]);

        // Assign role to the user
        $user->assignRole('sales-person');

        // Do not attempt to log the user in, instead show a pending approval message
        return redirect()->route('login')->with('status', 'Registration successful. Please wait for admin approval.');
    }
}
