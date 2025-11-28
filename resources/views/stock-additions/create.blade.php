@extends('layouts.app')

@section('content')
<div x-data="stockAdditionData()">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mt-2 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">
                Tambah Stok Produk
            </h2>
            <p class="text-sm text-gray-700 opacity-90 md:text-purple-100">
                Tambah stok untuk produk yang dipilih
            </p>
        </div>
        <a href="{{ route('stock-additions.index') }}"
           class="px-4 py-2 text-sm font-semibold text-white transition bg-gray-600 rounded-lg shadow-md hover:bg-gray-700">
            <i class="mr-1 fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- FORM --}}
    <div class="max-w-3xl p-6 bg-white border border-gray-100 shadow-md rounded-xl">
        <h3 class="mb-6 text-lg font-semibold text-gray-800">Form Tambah Stok</h3>

        @if(session('error'))
            <div class="p-4 mb-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                <i class="mr-2 fa-solid fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 mb-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                <ul class="pl-5 list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stock-additions.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- SEARCHABLE PRODUCT SELECTION --}}
            <div class="relative">
                <label class="block mb-2 text-sm font-semibold text-gray-700">
                    Produk <span class="text-red-500">*</span>
                </label>

                <input type="hidden" name="product_id" x-model="selectedProductId" required>

                <div class="relative" @click.away="open = false">
                    <input type="text"
                           x-model="search"
                           @focus="open = true"
                           @input="open = true"
                           placeholder="Ketik untuk mencari produk..."
                           class="w-full px-4 py-3 pr-10 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           autocomplete="off">

                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                        <i class="text-gray-400 fa-solid fa-magnifying-glass" x-show="!selectedProductId"></i>
                        <i class="text-green-500 fa-solid fa-check-circle" x-show="selectedProductId"></i>
                    </div>

                    <div x-show="open"
                         x-transition.opacity
                         class="absolute z-10 w-full mt-1 overflow-auto bg-white border border-gray-200 rounded-lg shadow-xl max-h-60">

                        <ul class="divide-y divide-gray-100">
                            <template x-for="product in filteredProducts" :key="product.product_id">
                                <li @click="selectProduct(product)"
                                    class="px-4 py-3 cursor-pointer hover:bg-purple-50 group">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-800 group-hover:text-purple-700" x-text="product.name"></p>
                                            <p class="text-xs text-gray-500" x-text="'Kategori: ' + (product.category ? product.category.name : '-')"></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded" x-text="'Stok: ' + product.stock"></span>
                                    </div>
                                </li>
                            </template>

                            <li x-show="filteredProducts.length === 0" class="px-4 py-3 text-sm text-center text-gray-500">
                                Produk tidak ditemukan.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Product Info Display --}}
            <div x-show="selectedProductId"
                 x-transition
                 class="p-4 border border-purple-200 bg-purple-50 rounded-lg">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Kategori</p>
                        <p class="text-sm font-medium text-gray-800" x-text="productCategory"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Stok Saat Ini</p>
                        <p class="text-sm font-bold text-purple-600" x-text="productStock + ' pcs'"></p>
                    </div>
                </div>
            </div>

            {{-- Quantity --}}
            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-700">
                    Jumlah Tambahan <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number"
                           name="quantity"
                           min="1"
                           value="{{ old('quantity') }}"
                           x-model="quantity"
                           required
                           placeholder="Masukkan jumlah stok yang ditambahkan"
                           class="w-full px-4 py-3 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                        <span class="text-sm text-gray-500">pcs</span>
                    </div>
                </div>
            </div>

            {{-- New Stock Preview --}}
            <div x-show="selectedProductId && quantity > 0"
                 x-transition
                 class="p-4 border border-green-200 bg-green-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Stok Setelah Ditambah</p>
                        <p class="text-2xl font-bold text-green-700" x-text="(parseInt(productStock) + parseInt(quantity)) + ' pcs'"></p>
                    </div>
                    <div class="p-3 text-green-600 rounded-full bg-green-200">
                        <i class="fa-solid fa-arrow-trend-up fa-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block mb-2 text-sm font-semibold text-gray-700">
                    Catatan (Opsional)
                </label>
                <textarea name="notes"
                          rows="3"
                          placeholder="Tambahkan catatan jika diperlukan..."
                          class="w-full px-4 py-3 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('notes') }}</textarea>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('stock-additions.index') }}"
                   class="px-6 py-2.5 text-sm font-semibold text-gray-800 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold text-white transition bg-green-600 rounded-lg shadow-md hover:bg-green-700">
                    <i class="mr-1 fa-solid fa-check"></i> Simpan Penambahan Stok
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function stockAdditionData() {
    return {
        // Ambil data produk dari Laravel dan parsing ke JSON
        products: @json($products),

        search: '',
        open: false,
        selectedProductId: '{{ old('product_id') }}',

        productStock: 0,
        productCategory: '',
        quantity: {{ old('quantity', 0) }},

        init() {
            // Jika ada old input (misal validasi gagal), set data awal
            if (this.selectedProductId) {
                const found = this.products.find(p => p.product_id == this.selectedProductId);
                if (found) {
                    this.selectProduct(found);
                }
            }
        },

        // Computed property untuk filter produk berdasarkan pencarian
        get filteredProducts() {
            if (this.search === '') {
                return this.products;
            }
            return this.products.filter(product => {
                return product.name.toLowerCase().includes(this.search.toLowerCase());
            });
        },

        // Fungsi saat user klik salah satu produk
        selectProduct(product) {
            this.selectedProductId = product.product_id;
            this.search = product.name; // Tampilkan nama di input text
            this.productStock = product.stock;
            this.productCategory = product.category ? product.category.name : '-';
            this.open = false; // Tutup dropdown
        }
    }
}
</script>
@endsection
