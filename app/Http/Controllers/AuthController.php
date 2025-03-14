<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\Customer;

class AuthController extends Controller
{
    public function sendOtp(Request $request) {
        $request->validate(['mobile' => 'required|digits:10']);

        $otp = rand(100000, 999999);
        Cache::put('otp_'.$request->mobile, $otp, now()->addMinutes(5));

        // Here, integrate an SMS API (e.g., Twilio, Firebase)
        // Example: Http::post('sms-provider-url', ['mobile' => $request->mobile, 'otp' => $otp]);

        return response()->json(['message' => 'OTP sent successfully',"otp"=>$otp]);
    }

    public function verifyOtp(Request $request) {
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6'
        ]);

        $storedOtp = Cache::get('otp_'.$request->mobile);

        if (!$storedOtp || $storedOtp != $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }

        $customer = Customer::firstOrCreate(['mobile' => $request->mobile]);
        $customer->update(['verified_at' => now()]);

        $token = $customer->createToken('authToken')->plainTextToken;

        return response()->json(['message' => 'Login successful', 'token' => $token]);
    }


}
