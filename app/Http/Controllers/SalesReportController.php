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
        // Get selected month and year from request, default to current month
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $date = Carbon::parse($selectedMonth . '-01');

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Previous month for comparison
        $previousMonth = $date->copy()->subMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();

        // Monthly Statistics
        $totalSales = (float) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $totalTransactions = (int) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->count();

        $totalProductsSold = (int) SaleItem::whereHas('sale', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('sale_date', [$startOfMonth, $endOfMonth]);
        })->sum('quantity');

        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Previous month statistics for comparison
        $previousMonthSales = (float) Sale::whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount');

        $previousMonthTransactions = (int) Sale::whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $previousMonthProducts = (int) SaleItem::whereHas('sale', function ($q) use ($previousMonthStart, $previousMonthEnd) {
            $q->whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd]);
        })->sum('quantity');

        // Calculate percentage changes
        $salesChange = $previousMonthSales > 0
            ? (($totalSales - $previousMonthSales) / $previousMonthSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $transactionsChange = $previousMonthTransactions > 0
            ? (($totalTransactions - $previousMonthTransactions) / $previousMonthTransactions) * 100
            : ($totalTransactions > 0 ? 100 : 0);

        $productsChange = $previousMonthProducts > 0
            ? (($totalProductsSold - $previousMonthProducts) / $previousMonthProducts) * 100
            : ($totalProductsSold > 0 ? 100 : 0);

        // Daily sales data for chart
        $dailySales = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Prepare chart data (fill missing dates with 0)
        $chartLabels = [];
        $chartData = [];
        $daysInMonth = $startOfMonth->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $chartLabels[] = $day;

            $dayData = $dailySales->firstWhere('date', $currentDate);
            $chartData[] = $dayData ? (float) $dayData->total : 0;
        }

        // All selling products sorted by quantity
        $topProducts = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('sale_date', [$startOfMonth, $endOfMonth]);
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->get();

        // Sales by category
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
            ->whereBetween('sales.sale_date', [$startOfMonth, $endOfMonth])
            ->groupBy('categories.categories_id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Generate month options for dropdown (last 12 months)
        $monthOptions = [];
        for ($i = 0; $i < 12; $i++) {
            $monthDate = now()->subMonths($i);
            $monthOptions[] = [
                'value' => $monthDate->format('Y-m'),
                'label' => $monthDate->locale('id')->translatedFormat('F Y')
            ];
        }

        return view('reports.sales', compact(
            'selectedMonth',
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
            'monthOptions',
            'startOfMonth',
            'endOfMonth'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get selected month from request
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $date = Carbon::parse($selectedMonth . '-01');

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Previous month for comparison
        $previousMonth = $date->copy()->subMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();

        // Monthly Statistics
        $totalSales = (float) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $totalTransactions = (int) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->count();

        $totalProductsSold = (int) SaleItem::whereHas('sale', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('sale_date', [$startOfMonth, $endOfMonth]);
        })->sum('quantity');

        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Previous month statistics for comparison
        $previousMonthSales = (float) Sale::whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount');

        $previousMonthTransactions = (int) Sale::whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $previousMonthProducts = (int) SaleItem::whereHas('sale', function ($q) use ($previousMonthStart, $previousMonthEnd) {
            $q->whereBetween('sale_date', [$previousMonthStart, $previousMonthEnd]);
        })->sum('quantity');

        // Calculate percentage changes
        $salesChange = $previousMonthSales > 0
            ? (($totalSales - $previousMonthSales) / $previousMonthSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $transactionsChange = $previousMonthTransactions > 0
            ? (($totalTransactions - $previousMonthTransactions) / $previousMonthTransactions) * 100
            : ($totalTransactions > 0 ? 100 : 0);

        $productsChange = $previousMonthProducts > 0
            ? (($totalProductsSold - $previousMonthProducts) / $previousMonthProducts) * 100
            : ($totalProductsSold > 0 ? 100 : 0);

        // All selling products sorted by quantity
        $topProducts = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('sale_date', [$startOfMonth, $endOfMonth]);
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->get();

        // Sales by category
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
            ->whereBetween('sales.sale_date', [$startOfMonth, $endOfMonth])
            ->groupBy('categories.categories_id', 'categories.name')
            ->orderByDesc('total_quantity')
            ->get();

        // Daily sales for the period
        $dailySales = Sale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $periodLabel = $date->locale('id')->translatedFormat('F Y');

        $data = compact(
            'selectedMonth',
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
            'startOfMonth',
            'endOfMonth'
        );

        $pdf = Pdf::loadView('reports.sales-report-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('Laporan-Penjualan-' . $selectedMonth . '.pdf');
    }
}
