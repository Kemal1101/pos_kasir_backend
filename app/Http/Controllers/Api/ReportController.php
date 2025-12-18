<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Utils\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReportController extends Controller
{
    /**
     * Get sales report for a date range
     */
    public function salesByDateRange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $sales = Sale::with(['items.product', 'user', 'payment'])
                ->where('payment_status', 'paid')
                ->whereBetween('sale_date', [$validated['start_date'], $validated['end_date']])
                ->get();

            $totalRevenue = $sales->sum('total_amount');
            $totalSales = $sales->count();
            $totalItems = $sales->sum(function ($sale) {
                return $sale->items->sum('quantity');
            });

            $report = [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'total_items_sold' => $totalItems,
                'average_sale_value' => $totalSales > 0 ? $totalRevenue / $totalSales : 0,
                'sales' => $sales,
            ];

            return Response::success($report, 'Sales report generated successfully');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get product performance report
     */
    public function productPerformance(Request $request): JsonResponse
    {
        try {
            $products = Product::with('category')
                ->withCount(['saleItems as times_sold' => function ($query) {
                    $query->select(DB::raw('COALESCE(COUNT(*), 0)'));
                }])
                ->withSum('saleItems as total_quantity_sold', 'quantity')
                ->withSum('saleItems as total_revenue', DB::raw('subtotal - discount_amount'))
                ->get()
                ->map(function ($product) {
                    return [
                        'product_id' => $product->product_id,
                        'name' => $product->name,
                        'category' => $product->category->name ?? null,
                        'current_stock' => $product->stock,
                        'times_sold' => $product->times_sold ?? 0,
                        'total_quantity_sold' => $product->total_quantity_sold ?? 0,
                        'total_revenue' => $product->total_revenue ?? 0,
                        'cost_price' => $product->cost_price,
                        'selling_price' => $product->selling_price,
                    ];
                });

            return Response::success($products, 'Product performance report generated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get cashier performance report
     */
    public function cashierPerformance(Request $request): JsonResponse
    {
        try {
            $cashiers = User::whereHas('role', function ($query) {
                $query->where('name', 'Kasir');
            })
                ->withCount(['sales as total_sales' => function ($query) {
                    $query->where('payment_status', 'paid');
                }])
                ->withSum(['sales as total_revenue' => function ($query) {
                    $query->where('payment_status', 'paid');
                }], 'total_amount')
                ->get()
                ->map(function ($user) {
                    $avgSale = $user->total_sales > 0 ? $user->total_revenue / $user->total_sales : 0;
                    return [
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'total_sales' => $user->total_sales ?? 0,
                        'total_revenue' => $user->total_revenue ?? 0,
                        'average_sale_value' => $avgSale,
                    ];
                });

            return Response::success($cashiers, 'Cashier performance report generated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get profit analysis report
     */
    public function profitAnalysis(Request $request): JsonResponse
    {
        try {
            $salesItems = SaleItem::with(['product', 'sale'])
                ->whereHas('sale', function ($query) {
                    $query->where('payment_status', 'paid');
                })
                ->get();

            $totalRevenue = $salesItems->sum(function ($item) {
                return $item->subtotal - $item->discount_amount;
            });

            $totalCost = $salesItems->sum(function ($item) {
                return $item->product->cost_price * $item->quantity;
            });

            $profit = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

            $report = [
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'gross_profit' => $profit,
                'profit_margin_percentage' => round($profitMargin, 2),
            ];

            return Response::success($report, 'Profit analysis report generated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Get slow moving products
     */
    public function slowMovingProducts(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);

        try {
            $products = Product::with('category')
                ->whereDoesntHave('saleItems', function ($query) use ($days) {
                    $query->whereHas('sale', function ($saleQuery) use ($days) {
                        $saleQuery->where('sale_date', '>=', now()->subDays($days))
                            ->where('payment_status', 'paid');
                    });
                })
                ->orWhereHas('saleItems', function ($query) use ($days) {
                    $query->whereHas('sale', function ($saleQuery) use ($days) {
                        $saleQuery->where('sale_date', '>=', now()->subDays($days))
                            ->where('payment_status', 'paid');
                    })
                        ->havingRaw('SUM(quantity) < 5');
                })
                ->get()
                ->map(function ($product) use ($days) {
                    $quantitySold = SaleItem::where('product_id', $product->product_id)
                        ->whereHas('sale', function ($query) use ($days) {
                            $query->where('sale_date', '>=', now()->subDays($days))
                                ->where('payment_status', 'paid');
                        })
                        ->sum('quantity');

                    return [
                        'product_id' => $product->product_id,
                        'name' => $product->name,
                        'category' => $product->category->name ?? null,
                        'current_stock' => $product->stock,
                        'quantity_sold_last_' . $days . '_days' => $quantitySold ?? 0,
                        'selling_price' => $product->selling_price,
                    ];
                });

            return Response::success($products, 'Slow moving products report generated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }

    /**
     * Compare sales between two periods
     */
    public function comparePeriods(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period1_start' => 'required|date',
            'period1_end' => 'required|date|after_or_equal:period1_start',
            'period2_start' => 'required|date',
            'period2_end' => 'required|date|after_or_equal:period2_start',
        ]);

        try {
            $period1Sales = Sale::where('payment_status', 'paid')
                ->whereBetween('sale_date', [$validated['period1_start'], $validated['period1_end']])
                ->get();

            $period2Sales = Sale::where('payment_status', 'paid')
                ->whereBetween('sale_date', [$validated['period2_start'], $validated['period2_end']])
                ->get();

            $period1Revenue = $period1Sales->sum('total_amount');
            $period2Revenue = $period2Sales->sum('total_amount');
            $revenueDifference = $period2Revenue - $period1Revenue;
            $revenueGrowth = $period1Revenue > 0 ? (($period2Revenue - $period1Revenue) / $period1Revenue) * 100 : 0;

            $report = [
                'period1' => [
                    'start_date' => $validated['period1_start'],
                    'end_date' => $validated['period1_end'],
                    'total_sales' => $period1Sales->count(),
                    'total_revenue' => $period1Revenue,
                ],
                'period2' => [
                    'start_date' => $validated['period2_start'],
                    'end_date' => $validated['period2_end'],
                    'total_sales' => $period2Sales->count(),
                    'total_revenue' => $period2Revenue,
                ],
                'comparison' => [
                    'revenue_difference' => $revenueDifference,
                    'revenue_growth_percentage' => round($revenueGrowth, 2),
                    'sales_count_difference' => $period2Sales->count() - $period1Sales->count(),
                ],
            ];

            return Response::success($report, 'Period comparison report generated');
        } catch (Throwable $e) {
            return Response::error($e);
        }
    }
}
