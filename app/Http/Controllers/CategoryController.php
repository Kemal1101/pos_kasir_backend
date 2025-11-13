<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

use function Symfony\Component\String\s;

class CategoryController extends Controller
{
    /**
     * Add a new category.
     */
    public function add_category(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($data);

        return response()->json([
            'message' => 'Category created',
            'data' => $category,
        ], 201);
    }

    /**
     * Edit an existing category.
     */
    public function edit_category(Request $request, $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:191',
            'description' => 'nullable|string',
        ]);

        $category->update($data);

        return response()->json([
            'message' => 'Category updated',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Delete a category.
     */
    public function delete_category($id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    /**
     * Return all categories.
     */
    public function list_categories(): JsonResponse
    {
        $categories = Category::all();

        return response()->json([
            'data' => $categories,
        ], status: 200);
    }
}
