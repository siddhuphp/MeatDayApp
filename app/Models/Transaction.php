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
    protected $fillable = ['id', 'customer_id', 'bill_no', 'total_amount', 'points_earned'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    // Relationship: A transaction belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship: A transaction has many transaction items (products purchased)
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
