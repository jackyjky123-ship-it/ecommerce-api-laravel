<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
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
    public function update(UpdateProductRequest $request, $id): JsonResponse{
        $product=Product::findOrFail($id);
        $validated=$request->validated();

        $updatedProduct = DB::transaction(function () use($product, $validated) {
            // Origin Data Product
            $productData = collect($validated)->only(['category_id', 'name', 'description', 'base_price'])->toArray();

            if(isset($validated['name'])){
                $productData['slug'] = Str::slug($validated['name']) . '_' . Str::random(5);
            }

            $product->update($productData);

            // ២. Update ឬបង្កើត Variants ថ្មី (ប្រសិនបើមានការផ្ញើ variants មក)
            if (isset($validated['variants'])) {
                $existingVariantIds = [];

            foreach($validated['variants'] as $variantData){
                if(isset($variantData['id'])){
                    // បើមាន id មានន័យថាកែប្រែ Variant ចាស់
                    $variant = $product->variants()->where('id', $variantData['id'])->first();
                    if($variant){
                        $variant->update([
                            'sku'=> $validated['sku'],
                            'price'=> $validated['price'] ?? null,
                            'stock'=> $validated['stock'],
                            'attributes'=> $validated['attributes'],
                        ]);
                    $existingVariantIds[] = $variant->id;
                    }
                    } else {
                        // បើគ្មាន id មានន័យថាបន្ថែម Variant ថ្មី
                        $newVariant = $product->variants()->create([
                            'sku'        => $variantData['sku'],
                            'price'      => $variantData['price'] ?? null,
                            'stock'      => $variantData['stock'],
                            'attributes' => $variantData['attributes'],
                        ]);
                        $existingVariantIds[] = $newVariant->id;
                    }
                }
                // លុប variants ចាស់ៗចោល ប្រសិនបើ Admin ដកចេញពីបញ្ជី
                if (!empty($existingVariantIds)) {
                    $product->variants()->whereNotIn('id', $existingVariantIds)->delete();
                }
            }
            return $product->load(['category', 'variants']);
        });
        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => $updatedProduct
        ]);
    }
    // Admin Only: លុបទំនិញ រួមទាំង Variants ដែលពាក់ព័ន្ធ
    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);

        DB::transaction(function () use ($product) {
            // លុប Variants ចោលមុនសិន ដើម្បីកុំឱ្យជាប់ Foreign Key
            $product->variants()->delete();
            $product->delete();
        });

        return response()->json([
            'message' => 'Product and its variants deleted successfully'
        ]);
    }
}
