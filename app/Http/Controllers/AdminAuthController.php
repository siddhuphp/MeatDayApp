<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function registerAdmin(Request $request) {
        $request->validate([
            'mobile' => 'required|digits:10|unique:customers,mobile',
            'name' => 'required|string',
            'password' => 'required|min:6'
        ]);

        $admin = Customer::create([
            'mobile' => $request->mobile,
            'name' => $request->name,
            'password' => $request->password,
            'is_admin' => true,
            'verified_at' => now()
        ]);

        return response()->json(['message' => 'Admin registered successfully']);
    }

    public function adminLogin(Request $request) {
        $request->validate([
            'mobile' => 'required|digits:10',
            'password' => 'required'
        ]);
    
        $admin = Customer::where('mobile', $request->mobile)->where('is_admin', true)->first();
    
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    
        $token = $admin->createToken('adminToken')->plainTextToken;
    
        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }
}
