<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;
use App\Utils\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Add a new category.
     */
    public function add_category(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect("/category")
            ->with('success', 'Kategori berhasil ditambahkan!');
    }


    /**
     * Edit an existing category.
     */
    public function edit_category(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);

            $data = $request->validate([
                'name' => 'required|string|max:191',
                'description' => 'nullable|string',
            ]);

            $category->update($data);

            return redirect("/category")
                ->with('success', 'Kategori berhasil diperbarui!');
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Kategori tidak ditemukan');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a category.
     */
    public function delete_category($id)
    {
        try {
            // Cari data, kalau tidak ada akan throw ModelNotFoundException
            $category = Category::findOrFail($id);
            if ($category->products()->exists()) {
                return back()->with('error', 'Kategori memiliki relasi dengan product');
            }

            // Hapus kategori
            $category->delete();

            // Redirect kembali ke halaman kategori
            return redirect("/category")
                ->with('success', 'Kategori berhasil dihapus!');
        } catch (ModelNotFoundException $e) {
            return back()->with('error', 'Kategori tidak ditemukan');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }



    /**
     * Return all categories.
     */
    public function list_categories(): JsonResponse
    {
        try {
            $categories = Category::all();
            return Response::success($categories);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }
}
