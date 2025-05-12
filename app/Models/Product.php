<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id', 'subcategory_id', 'name', 'price_per_kg', 'regular_points', 'pre_order_points'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    // Relationship: A product belongs to a subcategory
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    // Relationship: A subcategory has many products
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
