<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_sell',
        'price_cost',
        'price_diff',
        'user_id',
        'category_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')->withPivot(["material_quantity"])->withTimestamps();
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function($product) {
            if(!$product) {
                return false;
            }

            $relationships = ['materials'];

            foreach ($relationships as $relationship) {
                $product->$relationship()->detach();
            }

            return true;
        });
    }
}
