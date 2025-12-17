<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request, default to current month
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startOfPeriod = Carbon::parse($startDate)->startOfDay();
        $endOfPeriod = Carbon::parse($endDate)->endOfDay();

        // Calculate previous period duration for comparison
        $periodDays = $startOfPeriod->diffInDays($endOfPeriod) + 1;
        $previousPeriodEnd = $startOfPeriod->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodDays - 1)->startOfDay();

        // Period Statistics (paid only)
        $totalSales = (float) Sale::whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $totalTransactions = (int) Sale::whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('payment_status', 'paid')
            ->count();

        $totalProductsSold = (int) SaleItem::whereHas('sale', function ($q) use ($startOfPeriod, $endOfPeriod) {
            $q->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
              ->where('payment_status', 'paid');
        })->sum('quantity');

        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Previous period statistics for comparison (paid only)
        $previousPeriodSales = (float) Sale::whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $previousPeriodTransactions = (int) Sale::whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('payment_status', 'paid')
            ->count();

        $previousPeriodProducts = (int) SaleItem::whereHas('sale', function ($q) use ($previousPeriodStart, $previousPeriodEnd) {
            $q->whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
              ->where('payment_status', 'paid');
        })->sum('quantity');

        // Calculate percentage changes
        $salesChange = $previousPeriodSales > 0
            ? (($totalSales - $previousPeriodSales) / $previousPeriodSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $transactionsChange = $previousPeriodTransactions > 0
            ? (($totalTransactions - $previousPeriodTransactions) / $previousPeriodTransactions) * 100
            : ($totalTransactions > 0 ? 100 : 0);

        $productsChange = $previousPeriodProducts > 0
            ? (($totalProductsSold - $previousPeriodProducts) / $previousPeriodProducts) * 100
            : ($totalProductsSold > 0 ? 100 : 0);

        // Daily sales data for chart (paid only)
        $dailySales = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare chart data (fill missing dates with 0)
        $chartLabels = [];
        $chartData = [];
        $currentDate = $startOfPeriod->copy();

        while ($currentDate <= $endOfPeriod) {
            $dateStr = $currentDate->format('Y-m-d');
            $chartLabels[] = $currentDate->format('d/m');

            $dayData = $dailySales->firstWhere('date', $dateStr);
            $chartData[] = $dayData ? (float) $dayData->total : 0;

            $currentDate->addDay();
        }

        // All selling products sorted by quantity (paid only)
        $topProducts = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($startOfPeriod, $endOfPeriod) {
                $q->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
                  ->where('payment_status', 'paid');
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->get();

        // Sales by category (paid only)
        $salesByCategory = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->join('categories', 'products.categories_id', '=', 'categories.categories_id')
            ->select(
                'categories.categories_id',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->whereBetween('sales.sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('sales.payment_status', 'paid')
            ->groupBy('categories.categories_id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->get();

        // Calculate Total HPP (Cost of Goods Sold) and Profit
        $profitData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->select(
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost')
            )
            ->whereBetween('sales.sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('sales.payment_status', 'paid')
            ->first();

        $totalRevenue = (float) ($profitData->total_revenue ?? 0);
        $totalCost = (float) ($profitData->total_cost ?? 0);
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Previous period profit for comparison
        $previousProfitData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->select(
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost')
            )
            ->whereBetween('sales.sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('sales.payment_status', 'paid')
            ->first();

        $previousProfit = (float) (($previousProfitData->total_revenue ?? 0) - ($previousProfitData->total_cost ?? 0));
        $profitChange = $previousProfit > 0
            ? (($totalProfit - $previousProfit) / $previousProfit) * 100
            : ($totalProfit > 0 ? 100 : 0);

        // Products sorted by profit (highest profit first)
        $topProfitProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->leftJoin('categories', 'products.categories_id', '=', 'categories.categories_id')
            ->select(
                'products.product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost'),
                DB::raw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * products.cost_price) as total_profit')
            )
            ->whereBetween('sales.sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('sales.payment_status', 'paid')
            ->groupBy('products.product_id', 'products.name', 'categories.name')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();

        // Products with zero sales in period (not selling products)
        $soldProductIds = SaleItem::whereHas('sale', function ($q) use ($startOfPeriod, $endOfPeriod) {
            $q->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
              ->where('payment_status', 'paid');
        })->pluck('product_id')->unique();

        $notSellingProducts = Product::with('category')
            ->whereNotIn('product_id', $soldProductIds)
            ->where('stock', '>', 0) // Only show products that have stock
            ->orderBy('name')
            ->get();

        return view('reports.sales', compact(
            'startDate',
            'endDate',
            'totalSales',
            'totalTransactions',
            'totalProductsSold',
            'averageTransaction',
            'salesChange',
            'transactionsChange',
            'productsChange',
            'chartLabels',
            'chartData',
            'topProducts',
            'salesByCategory',
            'startOfPeriod',
            'endOfPeriod',
            'totalCost',
            'totalProfit',
            'profitMargin',
            'profitChange',
            'topProfitProducts',
            'notSellingProducts'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get date range from request
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $startOfPeriod = Carbon::parse($startDate)->startOfDay();
        $endOfPeriod = Carbon::parse($endDate)->endOfDay();

        // Calculate previous period for comparison
        $periodDays = $startOfPeriod->diffInDays($endOfPeriod) + 1;
        $previousPeriodEnd = $startOfPeriod->copy()->subDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodDays - 1)->startOfDay();

        // Period Statistics (paid only)
        $totalSales = (float) Sale::whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $totalTransactions = (int) Sale::whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('payment_status', 'paid')
            ->count();

        $totalProductsSold = (int) SaleItem::whereHas('sale', function ($q) use ($startOfPeriod, $endOfPeriod) {
            $q->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
              ->where('payment_status', 'paid');
        })->sum('quantity');

        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Previous period statistics for comparison (paid only)
        $previousPeriodSales = (float) Sale::whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $previousPeriodTransactions = (int) Sale::whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('payment_status', 'paid')
            ->count();

        $previousPeriodProducts = (int) SaleItem::whereHas('sale', function ($q) use ($previousPeriodStart, $previousPeriodEnd) {
            $q->whereBetween('sale_date', [$previousPeriodStart, $previousPeriodEnd])
              ->where('payment_status', 'paid');
        })->sum('quantity');

        // Calculate percentage changes
        $salesChange = $previousPeriodSales > 0
            ? (($totalSales - $previousPeriodSales) / $previousPeriodSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $transactionsChange = $previousPeriodTransactions > 0
            ? (($totalTransactions - $previousPeriodTransactions) / $previousPeriodTransactions) * 100
            : ($totalTransactions > 0 ? 100 : 0);

        $productsChange = $previousPeriodProducts > 0
            ? (($totalProductsSold - $previousPeriodProducts) / $previousPeriodProducts) * 100
            : ($totalProductsSold > 0 ? 100 : 0);

        // All selling products sorted by quantity (paid only)
        $topProducts = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($startOfPeriod, $endOfPeriod) {
                $q->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
                  ->where('payment_status', 'paid');
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->get();

        // Sales by category (paid only)
        $salesByCategory = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->join('categories', 'products.categories_id', '=', 'categories.categories_id')
            ->select(
                'categories.categories_id',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->whereBetween('sales.sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('sales.payment_status', 'paid')
            ->groupBy('categories.categories_id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->get();

        // Calculate Total HPP (Cost of Goods Sold) and Profit
        $profitData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->select(
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost')
            )
            ->whereBetween('sales.sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('sales.payment_status', 'paid')
            ->first();

        $totalRevenue = (float) ($profitData->total_revenue ?? 0);
        $totalCost = (float) ($profitData->total_cost ?? 0);
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Previous period profit for comparison
        $previousProfitData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->select(
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost')
            )
            ->whereBetween('sales.sale_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('sales.payment_status', 'paid')
            ->first();

        $previousProfit = (float) (($previousProfitData->total_revenue ?? 0) - ($previousProfitData->total_cost ?? 0));
        $profitChange = $previousProfit > 0
            ? (($totalProfit - $previousProfit) / $previousProfit) * 100
            : ($totalProfit > 0 ? 100 : 0);

        // Products sorted by profit (highest profit first)
        $topProfitProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.product_id')
            ->leftJoin('categories', 'products.categories_id', '=', 'categories.categories_id')
            ->select(
                'products.product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as total_cost'),
                DB::raw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * products.cost_price) as total_profit')
            )
            ->whereBetween('sales.sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('sales.payment_status', 'paid')
            ->groupBy('products.product_id', 'products.name', 'categories.name')
            ->orderByDesc('total_profit')
            ->limit(10)
            ->get();

        // Products with zero sales in period
        $soldProductIds = SaleItem::whereHas('sale', function ($q) use ($startOfPeriod, $endOfPeriod) {
            $q->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
              ->where('payment_status', 'paid');
        })->pluck('product_id')->unique();

        $notSellingProducts = Product::with('category')
            ->whereNotIn('product_id', $soldProductIds)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        // Daily sales for the period (paid only)
        $dailySales = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('sale_date', [$startOfPeriod, $endOfPeriod])
            ->where('payment_status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $periodLabel = $startOfPeriod->format('d/m/Y') . ' - ' . $endOfPeriod->format('d/m/Y');

        $data = compact(
            'startDate',
            'endDate',
            'periodLabel',
            'totalSales',
            'totalTransactions',
            'totalProductsSold',
            'averageTransaction',
            'salesChange',
            'transactionsChange',
            'productsChange',
            'topProducts',
            'salesByCategory',
            'dailySales',
            'startOfPeriod',
            'endOfPeriod',
            'totalCost',
            'totalProfit',
            'profitMargin',
            'profitChange',
            'topProfitProducts',
            'notSellingProducts'
        );

        $pdf = Pdf::loadView('reports.sales-report-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('Laporan-Penjualan-' . $startDate . '-to-' . $endDate . '.pdf');
    }
}
