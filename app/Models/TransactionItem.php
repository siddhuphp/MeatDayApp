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
    protected $fillable = ['id', 'transaction_id', 'product_id', 'quantity', 'price'];

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
}
