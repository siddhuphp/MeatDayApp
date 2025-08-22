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
            'key' => config('payu.key'),
            'action' => config('payu.mode') == 'live' ? 'https://secure.payu.in/_payment' : 'https://test.payu.in/_payment',
        ];
    }

    /**
     * Generate PayU hash using the working format from another project
     */
    private function generateHash($txnid, $amount, $productinfo, $firstname, $email)
    {
        return strtolower(hash('sha512', 
            config('payu.key') . '|' . 
            $txnid . '|' . 
            $amount . '|' . 
            $productinfo . '|' . 
            $firstname . '|' . 
            $email . '|||||||||||' . 
            config('payu.salt')
        ));
    }

    /**
     * Verify PayU transaction
     */
    public function verifyTransaction($txnid)
    {
        $response = Http::asForm()->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post(config('payu.mode') == 'live' ? 'https://info.payu.in/merchant/postservice.php?form=2' : 'https://test.payu.in/merchant/postservice.php?form=2', [
            'key' => config('payu.key'),
            'command' => 'verify_payment',
            'var1' => $txnid,
            'hash' => strtolower(hash('sha512', 
                config('payu.key') . '|verify_payment|' . 
                $txnid . '|' . 
                config('payu.salt')
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
