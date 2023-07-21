<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_per_uom',
        'uom',
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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_materials')->withPivot(["material_quantity"]);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function($material) {
            if(!$material) {
                return false;
            }

            $relationships = ['products'];

            foreach ($relationships as $relationship) {
                $material->$relationship()->detach();
            }

            return true;
        });
    }
}
