<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\Product;
use App\Utils\Response;
use Throwable;

class ProductController extends Controller
{
    /**
     * Add a new product.
     */
    public function add_product(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'categories_id' => 'required|integer',
                'name' => 'required|string|max:191',
                'cost_price' => 'required|numeric',
                'selling_price' => 'required|numeric',
                'stock' => 'required|integer',
                'description' => 'nullable|string',
                'barcode' => 'nullable|string|max:100',
                'product_images' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Upload ke Cloudinary jika ada gambar
            if ($request->hasFile('product_images')) {
                $data['product_images'] = Cloudinary::upload(
                    $request->file('product_images')->getRealPath(),
                    ['folder' => 'products']
                )->getSecurePath();
            }

            $product = Product::create($data);

            return Response::success($product, 'Product created');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Edit an existing product.
     */
    public function edit_product(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::find($id);
            if (! $product) return Response::notFound('Product not found');

            $data = $request->validate([
                'categories_id' => 'sometimes|integer',
                'name' => 'sometimes|string|max:191',
                'cost_price' => 'sometimes|numeric',
                'selling_price' => 'sometimes|numeric',
                'stock' => 'sometimes|integer',
                'description' => 'nullable|string',
                'barcode' => 'nullable|string|max:100',
                'product_images' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Jika ada file gambar baru
            if ($request->hasFile('product_images')) {
                $data['product_images'] = Cloudinary::upload(
                    $request->file('product_images')->getRealPath(),
                    ['folder' => 'products']
                )->getSecurePath();
            }

            $product->update($data);

            return Response::success($product->fresh(), 'Product updated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Delete a product.
     */
    public function delete_product($id): JsonResponse
    {
        try {
            $product = Product::find($id);
            if (! $product) return Response::notFound('Product not found');

            $product->delete();

            return Response::success(null, 'Product deleted');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * List products with optional filters.
     */
    public function list_product(Request $request): JsonResponse
    {
        try {
            $query = Product::with('category');

            if ($request->filled('category_id')) {
                $query->where('categories_id', $request->input('category_id'));
            }

            $products = $query->get();

            return Response::success($products);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get product detail by ID.
     */
    public function detail_product($id): JsonResponse
    {
        try {
            $product = Product::with('category')->find($id);

            if (!$product) {
                return Response::notFound('Product not found');
            }

            return Response::success($product);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }
}
