<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Public: មើលបញ្ជីទំនិញទាំងអស់ (មានភ្ជាប់ Category និង Variants)
    public function index():JsonResponse{
        $product = Product::with(['category', 'variants'])->paginate(10);

        return response()->json($product);
    }
    // Public: មើល Detail ទំនិញមួយ
    public function show($id):JsonResponse{
        $product = Product::with(['category', 'variants'])->findOrFail($id);

        return response()->json($product);
    }
    // Admin Only: បង្កើតទំនិញរួមជាមួយ Variants ដោយប្រើ DB Transaction
    public function store(StoreProductRequest $request):JsonResponse{
        $validated = $request->validated();
        // ហៅយកតែទិន្នន័យដែល validate ជោគជ័យតាម $request->validated()
        $product= DB::transaction(function () use ($validated) {
            // ១. បង្កើត Product
            $product= Product::create([
                'category_id' => $validated['category_id'],
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']) . '_' . Str::random(5),
                'description' => $validated['description'] ?? null,
                'base_price'  => $validated['base_price'],
        ]);
            // ២. Loop បង្កើត Variants នីមួយៗចេញពី $variant
            foreach ($validated['variants'] as $variant) {
            $product->variants()->create([
                'sku'=> $variant['sku'],
                'price'=> $variant['price'] ?? null,
                'stock'=> $variant['stock'],
                'attributes'=> $variant['attributes'],
            ]);
        }
            return $product->load(['category','variants']);
        });
        return response()->json([
            'message' => 'Product with variants created successfully',
            'data'    => $product
        ], 201);
    }
}
