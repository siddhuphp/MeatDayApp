<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function createBill(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1', // Allow grams (e.g., 0.5 for 500g)
        ]);

        $billNo = 'MD' . Str::random(6);
        $totalAmount = 0;
        $pointsEarned = 0;

        $transaction = Transaction::create([
            'id' => Str::uuid(),
            'customer_id' => $request->customer_id,
            'bill_no' => $billNo,
            'total_amount' => 0,
            'points_earned' => 0,
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            // Calculate price based on grams (convert grams to kg)
            $price = $product->price_per_kg * $item['quantity'];

            $totalAmount += $price;

            TransactionItem::create([
                'id' => Str::uuid(),
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'], // Store as kg (0.5 for 500g)
                'price' => $price,
            ]);
        }

        // Update total amount and reward points
        $transaction->update([
            'total_amount' => $totalAmount,
            'points_earned' => floor($totalAmount / 100), // 1 point per ₹100
        ]);

        return response()->json([
            'bill_no' => $billNo,
            'total_amount' => $totalAmount,
            'points_earned' => $transaction->points_earned,
        ]);
    }
}
