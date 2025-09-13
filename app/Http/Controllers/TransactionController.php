<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    use HttpResponses;
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

    /**
     * Get all orders for admin with filters, search, and pagination
     */
    public function getAllOrders(Request $request)
    {
        // Check if user has admin access
        if (!$request->user()->isAdmin()) {
            return $this->error(['message' => 'Unauthorized access. Admin role required.'], 'Unauthorized', 403);
        }

        $query = Transaction::with(['user', 'items.product']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                    ->orWhere('payu_txnid', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_no', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by order type
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by delivery date range
        if ($request->filled('delivery_date_from')) {
            $query->whereDate('delivery_date', '>=', $request->delivery_date_from);
        }

        if ($request->filled('delivery_date_to')) {
            $query->whereDate('delivery_date', '<=', $request->delivery_date_to);
        }

        // Filter by amount range
        if ($request->filled('amount_min')) {
            $query->where('total_amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total_amount', '<=', $request->amount_max);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'total_amount', 'delivery_date', 'status', 'payment_status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->get('per_page', 100);
        $perPage = min($perPage, 500); // Max 500 per page for performance

        $orders = $query->paginate($perPage);

        // Transform the data for better frontend consumption
        $transformedOrders = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'bill_no' => $order->bill_no,
                'customer' => [
                    'id' => $order->user->user_id ?? null,
                    'name' => $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'N/A',
                    'email' => $order->user->email ?? 'N/A',
                    'phone' => $order->user->phone_no ?? 'N/A',
                ],
                'order_details' => [
                    'subtotal' => $order->subtotal,
                    'total_discount' => $order->total_discount,
                    'total_amount' => $order->total_amount,
                    'order_type' => $order->order_type,
                    'delivery_date' => $order->delivery_date,
                    'status' => $order->status,
                ],
                'payment_details' => [
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'payu_txnid' => $order->payu_txnid,
                    'payment_date' => $order->payment_date,
                ],
                'points' => [
                    'total_regular_points' => $order->total_regular_points,
                    'total_pre_order_points' => $order->total_pre_order_points,
                ],
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        // Get summary statistics
        $summary = [
            'total_orders' => $orders->total(),
            'total_amount' => Transaction::sum('total_amount'),
            'status_counts' => Transaction::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'payment_status_counts' => Transaction::select('payment_status', DB::raw('count(*) as count'))
                ->groupBy('payment_status')
                ->pluck('count', 'payment_status'),
        ];

        return $this->success([
            'orders' => $transformedOrders,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
            'summary' => $summary,
            'filters_applied' => $request->only([
                'search',
                'status',
                'payment_status',
                'order_type',
                'payment_method',
                'date_from',
                'date_to',
                'delivery_date_from',
                'delivery_date_to',
                'amount_min',
                'amount_max',
                'sort_by',
                'sort_order'
            ])
        ], 'Orders retrieved successfully', 200);
    }
}
