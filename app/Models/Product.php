<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id',
        'category_id',
        'name',
        'price_per_kg',
        'regular_points',
        'pre_order_points',
        'product_discount',
        'product_image',
        'status'
    ];

    protected $appends = ['image_url'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    // Relationship: A product belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the full URL for the product image
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if ($this->product_image && Storage::disk('public')->exists($this->product_image)) {
            return Storage::disk('public')->url($this->product_image);
        }
        
        // Return default image URL
        return asset('images/MeatDay_shop_image.jpg');
    }

    /**
     * Get the full URL for the product image (alternative method)
     *
     * @return string
     */
    public function getProductImageUrlAttribute()
    {
        return $this->image_url;
    }

    /**
     * Scope to get only active products
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only inactive products
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Check if product is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if product is inactive
     *
     * @return bool
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }
}
