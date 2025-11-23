<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Models\Product;
use App\Models\SaleItem;
use App\Utils\Response;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Ambil kategori untuk dropdown / info
        $categories = Category::all();

        // Query produk dengan search
        $products = Product::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('product_id', 'asc')
            ->paginate(10) // jumlah per halaman
            ->appends(['search' => $search]); // supaya query string tetap ada di pagination

        // Produk terbanyak terjual
        $mostSoldProduct = SaleItem::select('product_id', 'name_product')
            ->selectRaw('SUM(quantity) as total_sold')  // ini tetap perlu raw karena SUM() adalah fungsi agregat
            ->groupBy('product_id', 'name_product')
            ->orderByDesc('total_sold')
            ->first();

        return view('products.index', compact('products', 'categories', 'mostSoldProduct'));
    }

    /**
     * Add a new product.
     */
    public function add_product(Request $request): JsonResponse
    {
        $request->validate([
            'categories_id' => 'required|integer',
            'name' => 'required|string',
            'cost_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer',
            'product_images' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        // Upload ke Cloudinary jika ada gambar
        $imageUrl = null;

        if ($request->hasFile('product_images')) {
            $image = $request->file('product_images');

            // Upload ke Cloudinary
            $uploadedFileUrl = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'products'
            ])->getSecurePath();

            $imageUrl = $uploadedFileUrl;
        }

        // Simpan produk
        $product = Product::create([
            'categories_id' => $request->categories_id,
            'name' => $request->name,
            'description' => $request->description,
            'cost_price' => $request->cost_price,
            'selling_price' => $request->selling_price,
            'product_images' => $imageUrl,
            'stock' => $request->stock,
            'barcode' => $request->barcode,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product,
        ]);
    }

    /**
     * Edit an existing product.
     */
    public function edit_product(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validasi minimal (opsional)
        $request->validate([
            'categories_id' => 'sometimes|exists:categories,categories_id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'cost_price' => 'sometimes|numeric',
            'selling_price' => 'sometimes|numeric',
            'stock' => 'sometimes|numeric',
            'barcode' => 'sometimes|string|max:100',
            'product_images' => 'sometimes|file|image|max:2048'
        ]);

        // Ambil data product sebelumnya
        $imageUrl = $product->product_images;

        // Jika ada file gambar baru
        if ($request->hasFile('product_images')) {
            $image = Cloudinary::upload(
                $request->file('product_images')->getRealPath(),
                ['folder' => 'products']
            )->getSecurePath();

            $imageUrl = $image;
        }

        // Update hanya field yang dikirim user
        $product->update(array_merge(
            $request->only([
                'categories_id',
                'name',
                'description',
                'cost_price',
                'selling_price',
                'stock',
                'barcode'
            ]),
            ['product_images' => $imageUrl] // gambar selalu disimpan (lama/baru)
        ));

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'data' => $product
        ]);
    }

    /**
     * Delete a product.
     */
    public function delete_product($id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (! $product) {
                return Response::notFound('Product not found');
            }

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

            // decode images for each product
            $products->transform(function ($p) {
                if (! empty($p->product_images)) {
                    $p->product_images = json_decode($p->product_images);
                }
                return $p;
            });

            return Response::success($products);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }
}
