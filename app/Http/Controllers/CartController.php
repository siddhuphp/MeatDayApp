<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Traits\HttpResponses;
use Carbon\Carbon;

class CartController extends Controller
{
    use HttpResponses;

    /**
     * Add item to cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001', // Minimum 1 gram
        ]);

        $userId = $request->user()->user_id;
        $product = Product::findOrFail($request->product_id);

        // Check if item already exists in cart
        $existingCartItem = Cart::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingCartItem) {
            // Update quantity if item exists
            $existingCartItem->quantity += $request->quantity;
            $existingCartItem->calculateItem();
            $existingCartItem->save();

            return $this->success([
                'message' => 'Cart item updated successfully',
                'cart_item' => $existingCartItem->load('product')
            ], 'Cart item updated successfully');
        }

        // Create new cart item
        $cartItem = new Cart([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        $cartItem->calculateItem();
        $cartItem->save();

        return $this->success([
            'message' => 'Item added to cart successfully',
            'cart_item' => $cartItem->load('product')
        ], 'Item added to cart successfully', 201);
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.001',
        ]);

        $userId = $request->user()->user_id;
        $cartItem = Cart::where('id', $cartItemId)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return $this->error(['message' => 'Cart item not found'], 'Not Found', 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->calculateItem();
        $cartItem->save();

        return $this->success([
            'message' => 'Cart item updated successfully',
            'cart_item' => $cartItem->load('product')
        ], 'Cart item updated successfully');
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request, $cartItemId)
    {
        $userId = $request->user()->user_id;
        $cartItem = Cart::where('id', $cartItemId)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return $this->error(['message' => 'Cart item not found'], 'Not Found', 404);
        }

        $cartItem->delete();

        return $this->success([
            'message' => 'Item removed from cart successfully'
        ], 'Item removed from cart successfully');
    }

    /**
     * View user's cart
     */
    public function viewCart(Request $request)
    {
        $userId = $request->user()->user_id;
        $cartSummary = Cart::getCartSummary($userId);

        return $this->success([
            'cart' => $cartSummary
        ], 'Cart retrieved successfully');
    }

    /**
     * Clear user's cart
     */
    public function clearCart(Request $request)
    {
        $userId = $request->user()->user_id;
        Cart::where('user_id', $userId)->delete();

        return $this->success([
            'message' => 'Cart cleared successfully'
        ], 'Cart cleared successfully');
    }

    /**
     * Get cart item details
     */
    public function getCartItem(Request $request, $cartItemId)
    {
        $userId = $request->user()->user_id;
        $cartItem = Cart::with('product')->where('id', $cartItemId)
            ->where('user_id', $userId)
            ->first();

        if (!$cartItem) {
            return $this->error(['message' => 'Cart item not found'], 'Not Found', 404);
        }

        return $this->success([
            'cart_item' => $cartItem
        ], 'Cart item retrieved successfully');
    }
}
