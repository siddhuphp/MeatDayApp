<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PayUService
{
    /**
     * Generate PayU payment data
     */
    public function generatePaymentData($transaction, $user, $request)
    {
        $payuTxnId = $transaction->generatePayUTxnId();
        $amount = number_format($transaction->total_amount, 2, '.', '');
        $productinfo = "MeatDay Order - " . $transaction->bill_no;
        
        $firstname = $request->delivery_name ?? $user->first_name;
        $email = $user->email;
        $phone = $request->delivery_phone ?? $user->phone ?? '9999999999';
        
        $surl = config('payu.success_url');
        $furl = config('payu.failure_url');

        // Generate hash
        $hash = $this->generateHash($payuTxnId, $amount, $productinfo, $firstname, $email);

        return [
            'txnid' => $payuTxnId,
            'amount' => $amount,
            'productinfo' => $productinfo,
            'firstname' => $firstname,
            'email' => $email,
            'phone' => $phone,
            'surl' => $surl . "?txnid=" . $payuTxnId,
            'furl' => $furl . "?txnid=" . $payuTxnId,
            'hash' => $hash,
            'key' => config('payu.' . config('payu.mode') . '.key'),
            'action' => config('payu.' . config('payu.mode') . '.action'),
        ];
    }

    /**
     * Generate PayU hash
     */
    private function generateHash($txnid, $amount, $productinfo, $firstname, $email)
    {
        return strtolower(hash('sha512', 
            config('payu.' . config('payu.mode') . '.key') . '|' . 
            $txnid . '|' . 
            $amount . '|' . 
            $productinfo . '|' . 
            $firstname . '|' . 
            $email . '|||||||||||' . 
            config('payu.' . config('payu.mode') . '.salt')
        ));
    }

    /**
     * Verify PayU transaction
     */
    public function verifyTransaction($txnid)
    {
        $response = Http::asForm()->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post(config('payu.' . config('payu.mode') . '.verify_url'), [
            'key' => config('payu.' . config('payu.mode') . '.key'),
            'command' => 'verify_payment',
            'var1' => $txnid,
            'hash' => strtolower(hash('sha512', 
                config('payu.' . config('payu.mode') . '.key') . '|verify_payment|' . 
                $txnid . '|' . 
                config('payu.' . config('payu.mode') . '.salt')
            )),
        ]);

        return [
            'success' => $response->successful(),
            'data' => json_decode($response->body(), true),
            'status_code' => $response->status(),
        ];
    }

    /**
     * Check if payment is successful
     */
    public function isPaymentSuccessful($payuResponse)
    {
        return isset($payuResponse['status']) && $payuResponse['status'] === 'success';
    }
}
