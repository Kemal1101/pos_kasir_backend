{{-- MODAL ADD PRODUCT --}}
<div 
    x-show="showAdd"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto"
>
    <div
        x-transition.scale.origin.bottom.duration.300ms
        class="flex flex-col w-full max-w-lg bg-white border border-gray-200 shadow-2xl rounded-xl max-h-[90vh]"
    >
        {{-- Header --}}
        <div class="relative flex-shrink-0 px-6 py-4 text-white border-b bg-gradient-to-r from-purple-600 to-purple-500 rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20">
                    <i class="text-lg text-white fa-solid fa-box"></i>
                </div>
                <h2 class="text-xl font-semibold">Tambah Produk Baru</h2>
            </div>

            <button 
                @click="showAdd = false"
                class="absolute text-white transition right-4 top-4 hover:text-gray-200"
            >
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 px-6 py-5 overflow-y-auto">
            <form id="add-product-form" @submit.prevent="addProduct" class="space-y-4">
                @csrf
                <div>
                    <label for="add_category" class="text-sm font-semibold text-gray-700">Kategori</label>
                    <select id="add_category" name="categories_id" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @foreach ($categories as $c)
                            <option value="{{ $c->categories_id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="add_name" class="text-sm font-semibold text-gray-700">Nama Produk</label>
                    <input id="add_name" type="text" name="name" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label for="add_cost_price" class="text-sm font-semibold text-gray-700">Harga Modal</label>
                    <input id="add_cost_price" type="number" name="cost_price" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label for="add_selling_price" class="text-sm font-semibold text-gray-700">Harga Jual</label>
                    <input id="add_selling_price" type="number" name="selling_price" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label for="add_stock" class="text-sm font-semibold text-gray-700">Stok</label>
                    <input id="add_stock" type="number" name="stock" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label for="add_description" class="text-sm font-semibold text-gray-700">Deskripsi</label>
                    <textarea id="add_description" name="description" rows="3" class="w-full px-4 py-2 mt-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
                </div>

                <div>
                    <label for="add_image" class="text-sm font-semibold text-gray-700">Gambar Produk</label>
                    <input id="add_image" type="file" name="product_images" accept="image/*" class="w-full px-2 py-1 border border-gray-300 rounded-lg">
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end flex-shrink-0 gap-3 px-6 py-4 border-t bg-gray-50 rounded-b-xl">
            <button 
                type="button"
                @click="showAdd = false"
                class="px-4 py-2 text-sm font-semibold text-gray-800 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100"
            >
                Batal
            </button>
            <button 
                type="submit"
                form="add-product-form"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-purple-600 rounded-lg shadow-sm hover:bg-purple-700"
            >
                <i class="fa-solid fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
