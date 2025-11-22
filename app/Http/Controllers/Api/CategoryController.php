<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;
use App\Utils\Response;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Add a new category.
     */
    public function add_category(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:191',
                'description' => 'nullable|string',
            ]);

            $category = Category::create($data);

            return Response::success($category, 'Category created', 201);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Edit an existing category.
     */
    public function edit_category(Request $request, $id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (! $category) {
                return Response::notFound('Category not found');
            }

            $data = $request->validate([
                'name' => 'sometimes|required|string|max:191',
                'description' => 'nullable|string',
            ]);

            $category->update($data);

            return Response::success($category->fresh(), 'Category updated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Delete a category.
     */
    public function delete_category($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (! $category) {
                return Response::notFound('Category not found');
            }

            $category->delete();

            return Response::success(null, 'Category deleted');
        } catch (Throwable $e) {
            return Response::error($e);
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
