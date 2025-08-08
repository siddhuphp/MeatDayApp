<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id', 
        'user_id', 
        'bill_no', 
        'subtotal',
        'total_discount',
        'total_amount', 
        'order_type',
        'delivery_date',
        'status',
        'total_regular_points',
        'total_pre_order_points',
        'payment_method',
        'payment_status',
        'payu_txnid',
        'payu_hash',
        'payment_response',
        'payment_date'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'payment_date' => 'datetime',
        'payment_response' => 'array',
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_regular_points' => 'integer',
        'total_pre_order_points' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
            $model->bill_no = 'BILL-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        });
    }

    // Relationship: A transaction belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relationship: A transaction has many transaction items (products purchased)
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    // Generate PayU transaction ID
    public function generatePayUTxnId()
    {
        return 'TXN-' . date('YmdHis') . '-' . strtoupper(Str::random(8));
    }

    // Check if transaction is paid
    public function isPaid()
    {
        return $this->payment_status === 'success';
    }

    // Check if transaction is immediate order
    public function isImmediateOrder()
    {
        return $this->order_type === 'immediate';
    }

    // Check if transaction is pre-order
    public function isPreOrder()
    {
        return $this->order_type === 'pre_order';
    }
}
