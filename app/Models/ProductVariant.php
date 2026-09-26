<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'sku',
    'price',
    'stock',
    'attributes'
])]
class ProductVariant extends Model
{
    public function casts():array {
        return [
            'attributes'=> 'array', // Cast JSON string ទៅជា PHP Array ស្វ័យប្រវត្តិ
            'price'=> 'decimal:2',
            'stock'=> 'integer'
        ];
    }

    public function product():BelongsTo{
        return $this->belongsTo(Product::class);
    }
}
