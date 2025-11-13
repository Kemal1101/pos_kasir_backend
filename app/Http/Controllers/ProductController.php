<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Add a new product.
     */
    public function add_product(Request $request): JsonResponse
    {
        $data = $request->validate([
            'categories_id' => 'nullable|exists:categories,categories_id',
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric',
            'selling_price' => 'nullable|numeric',
            'product_images' => 'nullable|array',
            'stock' => 'nullable|integer',
            'barcode' => 'nullable|string|max:191',
        ]);

        if (isset($data['product_images']) && is_array($data['product_images'])) {
            $data['product_images'] = json_encode($data['product_images']);
        }

        $product = Product::create($data);

        // decode images for response
        if (! empty($product->product_images)) {
            $product->product_images = json_decode($product->product_images);
        }

        return response()->json([
            'message' => 'Product created',
            'data' => $product,
        ], 201);
    }

    /**
     * Edit an existing product.
     */
    public function edit_product(Request $request, $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $data = $request->validate([
            'categories_id' => 'nullable|exists:categories,categories_id',
            'name' => 'sometimes|required|string|max:191',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric',
            'selling_price' => 'nullable|numeric',
            'product_images' => 'nullable|array',
            'stock' => 'nullable|integer',
            'barcode' => 'nullable|string|max:191',
        ]);

        if (array_key_exists('product_images', $data) && is_array($data['product_images'])) {
            $data['product_images'] = json_encode($data['product_images']);
        }

        $product->update($data);

        $product = $product->fresh();
        if (! empty($product->product_images)) {
            $product->product_images = json_decode($product->product_images);
        }

        return response()->json([
            'message' => 'Product updated',
            'data' => $product,
        ]);
    }

    /**
     * Delete a product.
     */
    public function delete_product($id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    /**
     * List products with optional filters.
     */
    public function list_product(Request $request): JsonResponse
    {
        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('categories_id', $request->input('category_id'));
        }

        $products = $query->get();

        // decode images for each product
        $products->transform(function ($p) {
            if (! empty($p->product_images)) {
                $p->product_images = json_decode($p->product_images);
            }
            return $p;
        });

        return response()->json(['data' => $products]);
    }
}
