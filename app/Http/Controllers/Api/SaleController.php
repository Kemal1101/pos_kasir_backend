<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Utils\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Throwable;

class SaleController extends Controller
{
    /**
     * Create an empty sale (draft) with zero totals.
     * - If authenticated, uses current user's user_id; otherwise requires user_id in payload.
     */
    public function create_sale(Request $request): JsonResponse
    {
        try {
            // Prefer JWT token to authenticate and get user_id, fallback to payload user_id
            $userId = null;
            try {
                $user = JWTAuth::parseToken()->authenticate();
                if ($user) {
                    $userId = $user->user_id;
                }
            } catch (Throwable $e) {
                // ignore, will fallback to request input
            }

            $userId = $userId ?? $request->input('user_id');
            if (! $userId) {
                return Response::unauthorized('Unable to resolve user from token or payload');
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'payment_id' => $request->input('payment_id'), // may be null at this stage
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'payment_status' => 'draft',
                'sale_date' => now(),
            ]);

            return Response::success($sale->fresh(), 'Sale created', 201);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    public function add_item(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,sale_id',
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'nullable|integer|min:1',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $sale = Sale::lockForUpdate()->findOrFail($validated['sale_id']);
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

                $qty = $validated['quantity'] ?? 1;
                $discount = (float) ($validated['discount_amount'] ?? 0);

                // Ensure sufficient stock, prevent negative inventory
                if ($product->stock < $qty) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => ["Insufficient stock for product '{$product->name}' (available: {$product->stock}, requested: {$qty})"],
                    ]);
                }

                // Base line amount per item is unit price * qty; keep it in sale_items.subtotal
                $lineSubtotal = (float) $product->selling_price * $qty;

                // Deduct product stock atomically under the same transaction
                $product->decrement('stock', $qty);

                $item = SaleItem::create([
                    'sale_id' => $sale->sale_id,
                    'product_id' => $product->product_id,
                    'name_product' => $product->name,
                    'quantity' => $qty, // quantity purchased
                    'discount_amount' => $discount,
                    'subtotal' => $lineSubtotal,
                ]);

                // Recalculate sale totals
                $sumSubtotal = (float) SaleItem::where('sale_id', $sale->sale_id)->sum('subtotal');
                $sumDiscount = (float) SaleItem::where('sale_id', $sale->sale_id)->sum('discount_amount');
                $tax = (float) $sale->tax_amount; // unchanged here
                $total = $sumSubtotal - $sumDiscount + $tax;

                $sale->update([
                    'subtotal' => $sumSubtotal,
                    'discount_amount' => $sumDiscount,
                    'total_amount' => $total,
                ]);

                return Response::success($sale->load(['items.product']), 'Item added to sale', 201);
            });
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Remove an item from a sale and recalc totals.
     */
    public function remove_item($saleItemId): JsonResponse
    {
        try {
            $item = SaleItem::find($saleItemId);
            if (! $item) {
                return Response::notFound('Sale item not found');
            }

            return DB::transaction(function () use ($item) {
                $saleId = $item->sale_id;
                $qty = $item->quantity ?? 0;

                // Restore product stock under lock to avoid race conditions
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product && $qty > 0) {
                    $product->increment('stock', $qty);
                }

                // Now delete the sale item
                $item->delete();

                $sale = Sale::lockForUpdate()->findOrFail($saleId);
                $sumSubtotal = (float) SaleItem::where('sale_id', $saleId)->sum('subtotal');
                $sumDiscount = (float) SaleItem::where('sale_id', $saleId)->sum('discount_amount');
                $tax = (float) $sale->tax_amount;
                $total = $sumSubtotal - $sumDiscount + $tax;

                $sale->update([
                    'subtotal' => $sumSubtotal,
                    'discount_amount' => $sumDiscount,
                    'total_amount' => $total,
                ]);

                return Response::success($sale->load(['items.product']), 'Item removed from sale');
            });
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get a sale with its items.
     */
    public function get_sale($saleId): JsonResponse
    {
        try {
            $sale = Sale::with(['items.product', 'user', 'payment'])->find($saleId);
            if (! $sale) {
                return Response::notFound('Sale not found');
            }

            return Response::success($sale);
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Delete a sale and restore product stock.
     * Only draft sales can be deleted.
     */
    public function delete_sale($saleId): JsonResponse
    {
        try {
            $sale = Sale::with('items')->find($saleId);
            if (! $sale) {
                return Response::notFound('Sale not found');
            }

            // Prevent cancellation of already cancelled sales
            if ($sale->payment_status === 'cancelled') {
                return Response::error(null, 'Sale is already cancelled', 400);
            }

            return DB::transaction(function () use ($sale) {
                // Restore stock for all items
                foreach ($sale->items as $item) {
                    $product = Product::lockForUpdate()->find($item->product_id);
                    if ($product && $item->quantity > 0) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                // Mark sale as cancelled instead of deleting
                $sale->update([
                    'payment_status' => 'cancelled',
                ]);

                return Response::success($sale, 'Sale cancelled');
            });
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Confirm payment for a sale.
     * Updates payment status and payment_id.
     */
    public function confirm_payment(Request $request, $saleId): JsonResponse
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,payment_id',
        ]);

        try {
            return DB::transaction(function () use ($saleId, $validated) {
                $sale = Sale::lockForUpdate()->find($saleId);

                if (!$sale) {
                    return Response::notFound('Sale not found');
                }

                // Prevent re-confirming completed sales
                if ($sale->payment_status === 'paid') {
                    return Response::error(null, 'Sale already confirmed', 400);
                }

                // Check if sale has items
                $itemsCount = SaleItem::where('sale_id', $saleId)->count();
                if ($itemsCount === 0) {
                    return Response::error(null, 'Cannot confirm payment for sale without items', 400);
                }

                // Update sale with payment info
                $sale->update([
                    'payment_id' => $validated['payment_id'],
                    'payment_status' => 'paid',
                    'sale_date' => now(),
                ]);

                return Response::success($sale->fresh()->load(['items.product', 'user', 'payment']), 'Payment confirmed successfully');
            });
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }
}
