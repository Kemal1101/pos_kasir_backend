<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\StockAddition;
use App\Utils\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Throwable;

class StockAdditionController extends Controller
{
    /**
     * Create a new stock addition and update product stock
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            // Get authenticated user
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return Response::unauthorized('Unable to authenticate user');
            }

            return DB::transaction(function () use ($validated, $user) {
                // Lock product for update
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

                // Create stock addition record
                $stockAddition = StockAddition::create([
                    'product_id' => $validated['product_id'],
                    'user_id' => $user->user_id,
                    'quantity' => $validated['quantity'],
                    'notes' => $validated['notes'] ?? null,
                    'added_at' => now(),
                ]);

                // Update product stock
                $product->increment('stock', $validated['quantity']);

                return Response::success(
                    $stockAddition->load(['product', 'user']),
                    'Stock added successfully',
                    201
                );
            });
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get all stock additions with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = StockAddition::with(['product', 'user']);

            // Filter by product
            if ($request->filled('product_id')) {
                $query->where('product_id', $request->input('product_id'));
            }

            // Filter by user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            // Filter by date range
            if ($request->filled('start_date')) {
                $query->where('added_at', '>=', $request->input('start_date'));
            }
            if ($request->filled('end_date')) {
                $query->where('added_at', '<=', $request->input('end_date'));
            }

            $stockAdditions = $query->orderBy('added_at', 'desc')->get();

            return Response::success($stockAdditions);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get a specific stock addition by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $stockAddition = StockAddition::with(['product', 'user'])->find($id);

            if (!$stockAddition) {
                return Response::notFound('Stock addition not found');
            }

            return Response::success($stockAddition);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }
}
