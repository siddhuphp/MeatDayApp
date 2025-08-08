<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransactionItem extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id', 
        'transaction_id', 
        'product_id', 
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'final_price',
        'total_price',
        'regular_points',
        'pre_order_points',
        'order_type'
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'regular_points' => 'integer',
        'pre_order_points' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    // Relationship: A transaction item belongs to a transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relationship: A transaction item is linked to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate item prices and points
     */
    public function calculateItem($orderType = null)
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
        
        // Set order type if provided
        if ($orderType) {
            $this->order_type = $orderType;
        }
        
        // Calculate points based on order type
        if ($this->order_type === 'immediate') {
            $this->regular_points = $product->regular_points * $this->quantity;
            $this->pre_order_points = 0;
        } else {
            $this->regular_points = 0;
            $this->pre_order_points = $product->pre_order_points * $this->quantity;
        }
        
        return $this;
    }
}
