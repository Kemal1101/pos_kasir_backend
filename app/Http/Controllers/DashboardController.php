<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $totalSalesToday = (float) Sale::whereDate('sale_date', $today)
            ->sum('total_amount');
        $totalSalesYesterday = (float) Sale::whereDate('sale_date', $yesterday)
            ->sum('total_amount');

        $transactionCountToday = (int) Sale::whereDate('sale_date', $today)
            ->count();

        $avgTransactionToday = $transactionCountToday > 0
            ? $totalSalesToday / $transactionCountToday
            : 0.0;

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $productsSoldThisMonth = (int) SaleItem::whereHas('sale', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('sale_date', [$startOfMonth, $endOfMonth]);
        })
            ->sum('quantity');

        $productsSoldLastMonth = (int) SaleItem::whereHas('sale', function ($q) use ($lastMonthStart, $lastMonthEnd) {
            $q->whereBetween('sale_date', [$lastMonthStart, $lastMonthEnd]);
        })
            ->sum('quantity');

        $recentSales = Sale::with(['payment', 'items'])
            ->orderByDesc('sale_date')
            ->limit(5)
            ->get();

        $recentTransactions = $recentSales->map(function (Sale $sale) {
            $itemsCount = (int) $sale->items->sum('quantity');
            $time = $sale->sale_date ? Carbon::parse($sale->sale_date)->format('H:i') : '';
            return [
                'code' => 'TRX' . str_pad((string) $sale->sale_id, 3, '0', STR_PAD_LEFT),
                'time' => $time,
                'items' => $itemsCount,
                'amount' => (float) $sale->total_amount,
                'method' => optional($sale->payment)->payment_method ?? '—',
            ];
        });

        $lowStockThreshold = 10;
        $lowStockProducts = Product::with('category')
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->limit(4)
            ->get();

        // Monthly sales totals
        $salesThisMonthTotal = (float) Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->sum('total_amount');
        $salesLastMonthTotal = (float) Sale::whereBetween('sale_date', [$lastMonthStart, $lastMonthEnd])->sum('total_amount');

        // Percentage calculations with safe zero division handling
        $percentSalesToday = $totalSalesYesterday > 0
            ? (($totalSalesToday - $totalSalesYesterday) / $totalSalesYesterday) * 100
            : ($totalSalesToday > 0 ? 100 : 0);

        $daysInLastMonth = $lastMonthStart->daysInMonth;
        $transactionsLastMonthTotal = (int) Sale::whereBetween('sale_date', [$lastMonthStart, $lastMonthEnd])->count();
        $transactionsLastMonthDailyAvg = $daysInLastMonth > 0 ? $transactionsLastMonthTotal / $daysInLastMonth : 0;
        $percentTransactionsTodayVsLastMonth = $transactionsLastMonthDailyAvg > 0
            ? (($transactionCountToday - $transactionsLastMonthDailyAvg) / $transactionsLastMonthDailyAvg) * 100
            : ($transactionCountToday > 0 ? 100 : 0);

        $percentProductsSoldMonthVsLastMonth = $productsSoldLastMonth > 0
            ? (($productsSoldThisMonth - $productsSoldLastMonth) / $productsSoldLastMonth) * 100
            : ($productsSoldThisMonth > 0 ? 100 : 0);

        $percentSalesThisMonthVsLastMonth = $salesLastMonthTotal > 0
            ? (($salesThisMonthTotal - $salesLastMonthTotal) / $salesLastMonthTotal) * 100
            : ($salesThisMonthTotal > 0 ? 100 : 0);

        // Helper to pick a color class
        $colorClass = function ($percent) {
            if ($percent > 0) return 'text-green-500';
            if ($percent < 0) return 'text-red-500';
            return 'text-gray-400';
        };

        $salesTodayChangeClass = $colorClass($percentSalesToday);
        $transactionsTodayChangeClass = $colorClass($percentTransactionsTodayVsLastMonth);
        $productsSoldMonthChangeClass = $colorClass($percentProductsSoldMonthVsLastMonth);
        $salesMonthChangeClass = $colorClass($percentSalesThisMonthVsLastMonth);

        return view('pages.dashboard', [
            'totalSalesToday' => $totalSalesToday,
            'transactionCountToday' => $transactionCountToday,
            'avgTransactionToday' => $avgTransactionToday,
            'productsSoldThisMonth' => $productsSoldThisMonth,
            'recentTransactions' => $recentTransactions,
            'lowStockProducts' => $lowStockProducts,
            'lowStockThreshold' => $lowStockThreshold,
            'salesThisMonthTotal' => $salesThisMonthTotal,
            'percentSalesToday' => $percentSalesToday,
            'percentTransactionsTodayVsLastMonth' => $percentTransactionsTodayVsLastMonth,
            'percentProductsSoldMonthVsLastMonth' => $percentProductsSoldMonthVsLastMonth,
            'percentSalesThisMonthVsLastMonth' => $percentSalesThisMonthVsLastMonth,
            'salesTodayChangeClass' => $salesTodayChangeClass,
            'transactionsTodayChangeClass' => $transactionsTodayChangeClass,
            'productsSoldMonthChangeClass' => $productsSoldMonthChangeClass,
            'salesMonthChangeClass' => $salesMonthChangeClass,
        ]);
    }

    public function category(Request $request)
    {
        $search = $request->input('search');

        $categories = Category::withCount('products')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->orderBy('categories_id', 'asc')
            ->paginate(5) // jumlah per halaman
            ->appends(['search' => $search]);

        $produk = Product::count();

        $mostCategory = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->first();

        return view('categories.index', [
            "categories" => $categories,
            "produk" => $produk,
            "mostCategory" => $mostCategory,
        ]);
    }
}
