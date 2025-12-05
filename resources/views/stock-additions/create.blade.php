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
                Masukkan jumlah tambahan stok untuk setiap produk
            </p>
        </div>
        <a href="{{ route('stock-additions.index') }}"
           class="px-4 py-2 text-sm font-semibold text-white transition bg-gray-600 rounded-lg shadow-md hover:bg-gray-700">
            <i class="mr-1 fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 mb-4 text-red-800 bg-red-100 border border-red-200 rounded-lg">
            <i class="mr-2 fa-solid fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 mb-4 text-green-800 bg-green-100 border border-green-200 rounded-lg">
            <i class="mr-2 fa-solid fa-check-circle"></i> {{ session('success') }}
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

    {{-- SUMMARY CARD --}}
    <div x-show="totalProductsWithStock > 0"
         x-transition
         class="p-4 mb-6 border border-green-200 bg-green-50 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="p-3 text-green-600 bg-green-200 rounded-full">
                    <i class="fa-solid fa-boxes-stacked fa-lg"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Produk yang akan ditambah stok</p>
                    <p class="text-2xl font-bold text-green-700" x-text="totalProductsWithStock + ' produk'"></p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Total Tambahan Stok</p>
                <p class="text-2xl font-bold text-green-700" x-text="totalQuantityToAdd + ' pcs'"></p>
            </div>
        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="p-6 bg-white border border-gray-100 shadow-md rounded-xl">

        {{-- SEARCH & FILTER --}}
        <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
            <div class="relative flex-1 max-w-md">
                <input type="text"
                       x-model="search"
                       placeholder="Cari produk berdasarkan nama..."
                       class="w-full px-4 py-3 pl-10 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="text-gray-400 fa-solid fa-magnifying-glass"></i>
                </div>
                <button x-show="search.length > 0"
                        @click="search = ''"
                        type="button"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            {{-- Category Filter --}}
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-600">Kategori:</label>
                <select x-model="selectedCategory"
                        class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->categories_id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- PRODUCT LIST --}}
        <div class="overflow-hidden border border-gray-200 rounded-lg">
            {{-- Table Header --}}
            <div class="hidden px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-100 md:grid md:grid-cols-12 md:gap-4">
                <div class="col-span-5">Produk</div>
                <div class="col-span-2 text-center">Kategori</div>
                <div class="col-span-2 text-center">Stok Saat Ini</div>
                <div class="col-span-3 text-center">Tambah Stok</div>
            </div>

            {{-- Product Items --}}
            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                {{-- Products with stock input first --}}
                <template x-for="product in sortedProducts" :key="product.product_id">
                    <div class="px-4 py-3 transition hover:bg-gray-50"
                         :class="{ 'bg-green-50 border-l-4 border-green-500': isConfirmed(product.product_id) }">

                        {{-- Mobile Layout --}}
                        <div class="flex flex-col gap-3 md:hidden">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-gray-800" x-text="product.name"></p>
                                    <p class="text-xs text-gray-500" x-text="product.category ? product.category.name : '-'"></p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold text-purple-600 bg-purple-100 rounded"
                                      x-text="'Stok: ' + product.stock"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">Tambah:</label>
                                <div class="flex items-center flex-1">
                                    <button type="button"
                                            @click="decrementQuantity(product.product_id)"
                                            class="px-3 py-2 text-gray-600 transition bg-gray-200 rounded-l-lg hover:bg-gray-300">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number"
                                           :value="getQuantity(product.product_id)"
                                           @input="setQuantity(product.product_id, $event.target.value)"
                                           @blur="confirmInput(product.product_id)"
                                           @keydown.enter="confirmInput(product.product_id); $event.target.blur()"
                                           min="0"
                                           class="w-20 py-2 text-center border-t border-b border-gray-300 focus:ring-0 focus:border-purple-500">
                                    <button type="button"
                                            @click="incrementQuantity(product.product_id)"
                                            class="px-3 py-2 text-gray-600 transition bg-gray-200 rounded-r-lg hover:bg-gray-300">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div x-show="getQuantity(product.product_id) > 0"
                                 x-transition
                                 class="flex items-center justify-between p-2 text-sm bg-green-100 rounded">
                                <span class="text-gray-600">Stok Setelah:</span>
                                <span class="font-bold text-green-700" x-text="(product.stock + getQuantity(product.product_id)) + ' pcs'"></span>
                            </div>
                        </div>

                        {{-- Desktop Layout --}}
                        <div class="items-center hidden gap-4 md:grid md:grid-cols-12">
                            {{-- Product Name --}}
                            <div class="col-span-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 text-purple-600 bg-purple-100 rounded-lg"
                                         x-show="!product.product_images">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                    <img x-show="product.product_images"
                                         :src="product.product_images"
                                         class="object-cover w-10 h-10 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800" x-text="product.name"></p>
                                        <p class="text-xs text-gray-500" x-text="product.barcode ? 'Barcode: ' + product.barcode : ''"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Category --}}
                            <div class="col-span-2 text-center">
                                <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded"
                                      x-text="product.category ? product.category.name : '-'"></span>
                            </div>

                            {{-- Current Stock --}}
                            <div class="col-span-2 text-center">
                                <span class="px-3 py-1 text-sm font-semibold rounded"
                                      :class="product.stock <= 10 ? 'text-red-600 bg-red-100' : 'text-purple-600 bg-purple-100'"
                                      x-text="product.stock + ' pcs'"></span>
                            </div>

                            {{-- Add Stock Input --}}
                            <div class="col-span-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                            @click="decrementQuantity(product.product_id)"
                                            class="p-2 text-gray-600 transition bg-gray-200 rounded-lg hover:bg-gray-300 hover:text-gray-800">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number"
                                           :value="getQuantity(product.product_id)"
                                           @input="setQuantity(product.product_id, $event.target.value)"
                                           @blur="confirmInput(product.product_id)"
                                           @keydown.enter="confirmInput(product.product_id); $event.target.blur()"
                                           min="0"
                                           placeholder="0"
                                           class="w-20 py-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                    <button type="button"
                                            @click="incrementQuantity(product.product_id)"
                                            class="p-2 text-gray-600 transition bg-gray-200 rounded-lg hover:bg-gray-300 hover:text-gray-800">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>

                                    {{-- New stock preview --}}
                                    <div x-show="getQuantity(product.product_id) > 0"
                                         x-transition
                                         class="flex items-center gap-1 ml-2">
                                        <i class="text-green-500 fa-solid fa-arrow-right"></i>
                                        <span class="font-bold text-green-600" x-text="(product.stock + getQuantity(product.product_id))"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Empty State --}}
                <div x-show="sortedProducts.length === 0" class="px-4 py-12 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 mb-4 text-gray-400 bg-gray-100 rounded-full">
                        <i class="fa-solid fa-box-open fa-2x"></i>
                    </div>
                    <p class="text-gray-500">Tidak ada produk yang ditemukan.</p>
                    <p class="text-sm text-gray-400">Coba ubah kata kunci pencarian atau filter kategori.</p>
                </div>
            </div>
        </div>

        {{-- SUBMIT SECTION --}}
        <div class="flex flex-col gap-4 pt-6 mt-6 border-t md:flex-row md:items-center md:justify-between">
            {{-- Notes --}}
            <div class="flex-1 max-w-md">
                <label class="block mb-2 text-sm font-semibold text-gray-700">
                    Catatan (Opsional)
                </label>
                <input type="text"
                       x-model="notes"
                       placeholder="Tambahkan catatan jika diperlukan..."
                       class="w-full px-4 py-2 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3">
                <button type="button"
                        @click="clearAll()"
                        x-show="totalProductsWithStock > 0"
                        class="px-4 py-2.5 text-sm font-semibold text-red-600 transition bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                    <i class="mr-1 fa-solid fa-trash"></i> Reset
                </button>
                <a href="{{ route('stock-additions.index') }}"
                   class="px-4 py-2.5 text-sm font-semibold text-gray-800 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100">
                    Batal
                </a>
                <button type="button"
                        @click="submitStock()"
                        :disabled="totalProductsWithStock === 0"
                        :class="totalProductsWithStock === 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                        class="px-6 py-2.5 text-sm font-semibold text-white transition rounded-lg shadow-md">
                    <i class="mr-1 fa-solid fa-check"></i>
                    Simpan (<span x-text="totalProductsWithStock"></span> produk)
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden Form for Submission --}}
    <form id="stockForm" action="{{ route('stock-additions.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="product_id" x-model="currentSubmitProductId">
        <input type="hidden" name="quantity" x-model="currentSubmitQuantity">
        <input type="hidden" name="notes" x-model="notes">
    </form>

</div>

<script>
function stockAdditionData() {
    return {
        products: @json($products),
        search: '',
        selectedCategory: '',
        stockInputs: {}, // { product_id: quantity }
        confirmedInputs: {}, // { product_id: true } - untuk tracking input yang sudah dikonfirmasi
        notes: '',
        currentSubmitProductId: '',
        currentSubmitQuantity: '',
        submissionQueue: [],
        isSubmitting: false,

        init() {
            // Initialize stockInputs for all products
            this.products.forEach(p => {
                this.stockInputs[p.product_id] = 0;
                this.confirmedInputs[p.product_id] = false;
            });
        },

        // Get quantity for a product
        getQuantity(productId) {
            return this.stockInputs[productId] || 0;
        },

        // Check if input is confirmed (untuk sorting)
        isConfirmed(productId) {
            return this.confirmedInputs[productId] === true && this.getQuantity(productId) > 0;
        },

        // Set quantity for a product (saat mengetik, belum konfirmasi)
        setQuantity(productId, value) {
            const qty = parseInt(value) || 0;
            this.stockInputs[productId] = qty >= 0 ? qty : 0;
            // Belum konfirmasi, hanya update nilai
        },

        // Konfirmasi input (dipanggil saat blur/enter)
        confirmInput(productId) {
            if (this.getQuantity(productId) > 0) {
                this.confirmedInputs[productId] = true;
            } else {
                this.confirmedInputs[productId] = false;
            }
        },

        // Increment quantity
        incrementQuantity(productId) {
            if (!this.stockInputs[productId]) {
                this.stockInputs[productId] = 0;
            }
            this.stockInputs[productId]++;
            // Auto confirm karena user klik tombol
            this.confirmedInputs[productId] = true;
        },

        // Decrement quantity
        decrementQuantity(productId) {
            if (this.stockInputs[productId] && this.stockInputs[productId] > 0) {
                this.stockInputs[productId]--;
            }
            // Update confirmed status
            if (this.stockInputs[productId] > 0) {
                this.confirmedInputs[productId] = true;
            } else {
                this.confirmedInputs[productId] = false;
            }
        },

        // Clear all inputs
        clearAll() {
            Object.keys(this.stockInputs).forEach(key => {
                this.stockInputs[key] = 0;
                this.confirmedInputs[key] = false;
            });
            this.notes = '';
        },

        // Filtered products based on search and category
        get filteredProducts() {
            return this.products.filter(product => {
                const matchSearch = this.search === '' ||
                    product.name.toLowerCase().includes(this.search.toLowerCase());
                const matchCategory = this.selectedCategory === '' ||
                    (product.category && product.categories_id == this.selectedCategory);
                return matchSearch && matchCategory;
            });
        },

        // Sorted products - hanya produk yang SUDAH DIKONFIRMASI yang pindah ke atas
        get sortedProducts() {
            return [...this.filteredProducts].sort((a, b) => {
                const confirmedA = this.isConfirmed(a.product_id);
                const confirmedB = this.isConfirmed(b.product_id);

                // Products yang sudah dikonfirmasi dengan quantity > 0 ke atas
                if (confirmedA && !confirmedB) return -1;
                if (!confirmedA && confirmedB) return 1;

                // Jika sama-sama confirmed atau tidak, sort by name
                return a.name.localeCompare(b.name);
            });
        },

        // Total products with stock to add
        get totalProductsWithStock() {
            return Object.values(this.stockInputs).filter(qty => qty > 0).length;
        },

        // Total quantity to add
        get totalQuantityToAdd() {
            return Object.values(this.stockInputs).reduce((sum, qty) => sum + (qty || 0), 0);
        },

        // Get products with stock to submit
        getProductsToSubmit() {
            return Object.entries(this.stockInputs)
                .filter(([_, qty]) => qty > 0)
                .map(([productId, qty]) => ({ productId, quantity: qty }));
        },

        // Submit stock additions
        async submitStock() {
            const productsToSubmit = this.getProductsToSubmit();
            if (productsToSubmit.length === 0) return;

            this.isSubmitting = true;

            // Submit each product one by one
            for (const item of productsToSubmit) {
                this.currentSubmitProductId = item.productId;
                this.currentSubmitQuantity = item.quantity;

                // Create form data
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('product_id', item.productId);
                formData.append('quantity', item.quantity);
                formData.append('notes', this.notes);

                try {
                    const response = await fetch('{{ route('stock-additions.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to submit');
                    }
                } catch (error) {
                    console.error('Error submitting stock:', error);
                    alert('Gagal menambah stok untuk beberapa produk. Silakan coba lagi.');
                    this.isSubmitting = false;
                    return;
                }
            }

            // Redirect to index after successful submission
            window.location.href = '{{ route('stock-additions.index') }}';
        }
    }
}
</script>
@endsection
