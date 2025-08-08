<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'user_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'final_price',
        'total_price',
        'regular_points',
        'pre_order_points'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    // Relationship: Cart belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relationship: Cart belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate cart item prices
     */
    public function calculateItem()
    {
        $product = $this->product;
        
        // Set unit price from product
        $this->unit_price = $product->price_per_kg;
        
        // Set discount percentage from product
        $this->discount_percentage = $product->product_discount;
        
        // Calculate discount amount
        $this->discount_amount = ($this->unit_price * $this->discount_percentage) / 100;
        
        // Calculate final price (after discount)
        $this->final_price = $this->unit_price - $this->discount_amount;
        
        // Calculate total price
        $this->total_price = $this->final_price * $this->quantity;
        
        // Points will be calculated at checkout based on order type
        $this->regular_points = 0;
        $this->pre_order_points = 0;
        
        return $this;
    }

    /**
     * Get cart summary for a user
     */
    public static function getCartSummary($userId)
    {
        $cartItems = self::with('product')->where('user_id', $userId)->get();
        
        $summary = [
            'items' => $cartItems,
            'total_items' => $cartItems->count(),
            'total_quantity' => $cartItems->sum('quantity'),
            'subtotal' => $cartItems->sum('total_price'),
            'total_discount' => $cartItems->sum('discount_amount'),
            'total_regular_points' => $cartItems->sum('regular_points'),
            'total_pre_order_points' => $cartItems->sum('pre_order_points'),
        ];
        
        return $summary;
    }
}
