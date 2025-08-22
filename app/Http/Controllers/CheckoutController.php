<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CheckoutRequest;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Cart;
use App\Models\User;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    use HttpResponses;

    /**
     * Initialize checkout process
     */
    public function checkout(CheckoutRequest $request)
    {
        $userId = $request->user()->user_id;
        
        // Get user's cart
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();
        
        if ($cartItems->isEmpty()) {
            return $this->error(['message' => 'Your cart is empty'], 'Empty Cart', 400);
        }

        // Validate pre-order date
        if ($request->order_type === 'pre_order') {
            if (!$request->delivery_date) {
                return $this->error(['message' => 'Delivery date is required for pre-orders'], 'Validation Error', 422);
            }
            
            $minDate = Carbon::tomorrow()->addDay(); // 2 days from now
            if (Carbon::parse($request->delivery_date)->lt($minDate)) {
                return $this->error(['message' => 'Delivery date must be at least 2 days from today'], 'Validation Error', 422);
            }
        }

        // Calculate totals
        $subtotal = $cartItems->sum('total_price');
        $totalDiscount = $cartItems->sum('discount_amount');
        $totalAmount = $subtotal;

        // Create transaction
        $transaction = new Transaction([
            'user_id' => $userId,
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_amount' => $totalAmount,
            'order_type' => $request->order_type,
            'delivery_date' => $request->delivery_date,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);

        $transaction->save();

        // Create transaction items
        $totalRegularPoints = 0;
        $totalPreOrderPoints = 0;

        foreach ($cartItems as $cartItem) {
            $transactionItem = new TransactionItem([
                'transaction_id' => $transaction->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
            ]);

            $transactionItem->calculateItem($request->order_type);
            $transactionItem->save();

            // Calculate total points
            if ($request->order_type === 'immediate') {
                $totalRegularPoints += $transactionItem->regular_points;
            } else {
                $totalPreOrderPoints += $transactionItem->pre_order_points;
            }
        }

        // Update transaction with points
        $transaction->update([
            'total_regular_points' => $totalRegularPoints,
            'total_pre_order_points' => $totalPreOrderPoints,
        ]);

        // Handle payment based on method
        if ($request->payment_method === 'payu') {
            return $this->initiatePayUPayment($request, $transaction);
        } else {
            // COD - mark as confirmed
            $transaction->update([
                'payment_status' => 'success',
                'status' => 'confirmed',
                'payment_date' => now(),
            ]);

            // Clear cart
            Cart::where('user_id', $userId)->delete();

            return $this->success([
                'message' => 'Order placed successfully with COD',
                'transaction' => $transaction->load('items.product'),
                'payment_status' => 'success'
            ], 'Order placed successfully');
        }
    }

    /**
     * Initiate PayU payment
     */
    private function initiatePayUPayment(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        
        // Generate PayU transaction ID
        $payuTxnId = $transaction->generatePayUTxnId();
        
        // Prepare payment data
        $amount = number_format($transaction->total_amount, 2, '.', '');
        $productinfo = "MeatDay Order - " . $transaction->bill_no;
        
        $firstname = $request->delivery_name ?? $user->first_name;
        $email = $user->email;
        $phone = $request->delivery_phone ?? $user->phone ?? '9999999999';
        
        $surl = config('payu.success_url');
        $furl = config('payu.failure_url');

        // Generate hash using the working format from another project
        $hash = strtolower(hash('sha512', 
            config('payu.key') . '|' . 
            $payuTxnId . '|' . 
            $amount . '|' . 
            $productinfo . '|' . 
            $firstname . '|' . 
            $email . '|||||||||||' . 
            config('payu.salt')
        ));

        $paymentData = [
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

        // Update transaction with PayU details
        $transaction->update([
            'payu_txnid' => $payuTxnId,
            'payu_hash' => $hash,
        ]);

        return $this->success([
            'message' => 'Payment initiated',
            'transaction' => $transaction->load('items.product'),
            'payment_data' => $paymentData,
            'payment_status' => 'pending'
        ], 'Payment initiated successfully');
    }

    /**
     * Verify PayU transaction
     */
    public function verifyPayUTransaction(Request $request)
    {
        $request->validate([
            'txnid' => 'required|string'
        ]);

        $response = Http::asForm()->withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post(config('payu.mode') == 'live' ? 'https://info.payu.in/merchant/postservice.php?form=2' : 'https://test.payu.in/merchant/postservice.php?form=2', [
            'key' => config('payu.key'),
            'command' => 'verify_payment',
            'var1' => $request->txnid,
            'hash' => strtolower(hash('sha512', 
                config('payu.key') . '|verify_payment|' . 
                $request->txnid . '|' . 
                config('payu.salt')
            )),
        ]);

        if ($response->successful()) {
            $data = json_decode($response->body(), true);
            
            // Find transaction
            $transaction = Transaction::where('payu_txnid', $request->txnid)->first();
            
            if ($transaction) {
                $this->updateTransactionStatus($transaction, $data);
                
                return $this->success([
                    'message' => 'Transaction verified successfully',
                    'transaction' => $transaction->load('items.product'),
                    'payment_data' => $data
                ], 'Transaction verified successfully');
            } else {
                return $this->error(['message' => 'Transaction not found'], 'Not Found', 404);
            }
        } else {
            return $this->error([
                'message' => 'Transaction verification failed',
                'response' => $response->body()
            ], 'Verification Failed', 400);
        }
    }

    /**
     * Update transaction status based on PayU response
     */
    private function updateTransactionStatus(Transaction $transaction, array $payuResponse)
    {
        $status = $payuResponse['status'] ?? 'failed';
        
        if ($status === 'success') {
            $transaction->update([
                'payment_status' => 'success',
                'status' => 'confirmed',
                'payment_date' => now(),
                'payment_response' => $payuResponse,
            ]);

            // Clear user's cart
            Cart::where('user_id', $transaction->user_id)->delete();
        } else {
            $transaction->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
                'payment_response' => $payuResponse,
            ]);
        }
    }

    /**
     * Get transaction details
     */
    public function getTransaction(Request $request, $transactionId)
    {
        $userId = $request->user()->user_id;
        
        $transaction = Transaction::with('items.product')
            ->where('id', $transactionId)
            ->where('user_id', $userId)
            ->first();

        if (!$transaction) {
            return $this->error(['message' => 'Transaction not found'], 'Not Found', 404);
        }

        return $this->success([
            'transaction' => $transaction
        ], 'Transaction retrieved successfully');
    }

    /**
     * Get user's transaction history
     */
    public function getTransactionHistory(Request $request)
    {
        $userId = $request->user()->user_id;
        
        $transactions = Transaction::with('items.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->success([
            'transactions' => $transactions
        ], 'Transaction history retrieved successfully');
    }
}
