<?php

namespace App\Http\Controllers;

use App\Models\StockAddition;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdditionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $stockAdditions = StockAddition::with(['product.category', 'user'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('added_at', 'desc')
            ->paginate(10);

        // Statistics
        $totalAdditions = StockAddition::count();
        $totalQuantityAdded = StockAddition::sum('quantity');
        $recentAdditions = StockAddition::where('added_at', '>=', now()->subDays(7))->count();

        return view('stock-additions.index', compact(
            'stockAdditions',
            'totalAdditions',
            'totalQuantityAdded',
            'recentAdditions'
        ));
    }

    public function create()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('stock-additions.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Create stock addition record
            $stockAddition = StockAddition::create([
                'product_id' => $validated['product_id'],
                'user_id' => Auth::id(),
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'],
                'added_at' => now()
            ]);

            // Update product stock
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $product->stock += $validated['quantity'];
            $product->save();

            DB::commit();

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok berhasil ditambahkan!',
                    'data' => $stockAddition
                ]);
            }

            return redirect()->route('stock-additions.index')
                ->with('success', 'Stok berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambah stok: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal menambah stok: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stockAddition = StockAddition::findOrFail($id);

            // Don't allow deletion if it would make stock negative
            $product = Product::findOrFail($stockAddition->product_id);
            if ($product->stock < $stockAddition->quantity) {
                return back()->with('error', 'Tidak dapat menghapus, stok produk tidak mencukupi!');
            }

            DB::beginTransaction();

            // Reduce product stock
            $product->stock -= $stockAddition->quantity;
            $product->save();

            // Delete the record
            $stockAddition->delete();

            DB::commit();

            return redirect()->route('stock-additions.index')
                ->with('success', 'Data penambahan stok berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
