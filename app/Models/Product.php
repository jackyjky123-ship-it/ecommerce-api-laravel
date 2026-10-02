<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'description',
    'base_price',
    ])]
class Product extends Model
{
    public function category():BelongsTo{
        return $this->belongsTo(Category::class);
    }
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function scopeFilter($query, $filters)
    {
        // Search by name
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($subQuery) use ($search){
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
        // Filter by category
        $query->when($filters['category'] ?? null, function ($q, $category_id) {
            $q->where('category_id', $category_id);
        });
        // Filter by min price
        $query->when($filters['min_price'] ?? null, function ($q, $min_price) {
            $q->where('base_price', '>=', $min_price);
        });
        // Filter by max price
        $query->when($filters['max_price'] ?? null, function ($q, $max_price){
            $q->where('base_price', '<=', $max_price);
        });
        // Sorting
        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'oldest'     => $query->oldest(),
            'latest'     => $query->latest(),
            default      => $query->latest(),
        };
        return $query;
    }
}
