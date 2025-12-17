{{-- MODAL EDIT PRODUCT --}}
<div x-show="showEdit" x-transition.opacity x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div x-show="showEdit" x-transition.scale.origin.bottom.duration.300ms
        class="w-full max-w-lg bg-white border border-gray-200 shadow-2xl rounded-2xl max-h-[90vh] flex flex-col overflow-hidden">
        {{-- Header --}}
        <div class="relative flex-shrink-0 px-6 py-4 text-white border-b bg-gradient-to-r from-purple-600 to-purple-500 rounded-t-2xl">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20">
                    <i class="text-lg text-white fa-solid fa-box"></i>
                </div>
                <h2 class="text-xl font-semibold">Edit Produk</h2>
            </div>

            <button @click="showEdit = false" class="absolute text-white transition right-4 top-4 hover:text-gray-200">
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body (scrollable) --}}
        <form id="edit-product-form" @submit.prevent="editProduct" class="flex-1 px-6 py-5 space-y-4 overflow-y-auto">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_product_id" name="product_id">

            {{-- Kategori --}}
            <div>
                <label class="text-sm font-semibold text-gray-700">Kategori</label>
                <select id="edit_categoty_id" name="category_id"
                    class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">-- Pilih --</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->categories_id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Nama Produk --}}
            <div>
                <label class="text-sm font-semibold text-gray-700">Nama Produk</label>
                <input type="text" id="edit_name" name="name"
                    class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            {{-- Harga Modal --}}
            <div>
                <label class="text-sm font-semibold text-gray-700">Harga Modal</label>
                <input type="number" id="edit_cost_price" name="cost_price"
                    class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            {{-- Harga Jual --}}
            <div>
                <label class="text-sm font-semibold text-gray-700">Harga Jual</label>
                <input type="number" id="edit_selling_price" name="selling_price"
                    class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>


            {{-- Deskripsi --}}
            <div>
                <label class="text-sm font-semibold text-gray-700">Deskripsi</label>
                <textarea id="edit_description" name="description" rows="3"
                    class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>

            {{-- Gambar --}}
            <div>
                <label class="text-sm font-semibold text-gray-700">Gambar Produk</label>
                <input type="file" id="edit_product_images" name="product_images"
                    class="w-full px-2 py-1 mt-1 border border-gray-300 rounded-lg">
                <img id="preview_product_image" class="hidden w-20 h-20 mt-2 rounded-md">
            </div>
        </form>

        {{-- Footer --}}
        <div class="flex justify-end flex-shrink-0 gap-3 px-6 py-4 border-t bg-gray-50 rounded-b-2xl">
            <button type="button" @click="showEdit = false"
                class="px-4 py-2 text-sm font-semibold text-gray-800 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100">
                Batal
            </button>
            <button type="submit" form="edit-product-form"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-purple-600 rounded-lg hover:bg-purple-700">
                <i class="fa-solid fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>
