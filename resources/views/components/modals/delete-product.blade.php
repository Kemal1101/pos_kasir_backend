{{-- MODAL DELETE PRODUCT --}}
<div 
    x-show="showDeleteModal"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto"
>
    <div 
        x-show="showDeleteModal" 
        x-transition.scale.origin.center.duration.300ms
        class="w-full max-w-md bg-white border border-gray-200 shadow-2xl rounded-xl"
    >
        {{-- HEADER --}}
        <div class="relative px-6 py-4 text-white border-b bg-gradient-to-r from-red-600 to-red-500 rounded-t-xl">
            <h2 class="text-xl font-semibold">Hapus Produk</h2>
            <button 
                @click="showDeleteModal = false" 
                class="absolute text-white transition right-4 top-4 hover:text-gray-200"
            >
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 py-5 space-y-3">
            <p class="text-sm text-gray-700">
                Apakah Anda yakin ingin menghapus produk:
            </p>
            <p class="text-lg font-semibold text-red-600" id="delete_product_name" x-text="product?.name"></p>
            <p class="text-xs italic text-gray-500">
                *Menghapus produk ini tidak dapat dikembalikan.
            </p>
        </div>

        {{-- FOOTER --}}
        <form id="delete-product-form" @submit.prevent="deleteProduct">
            @csrf
            @method('DELETE')
            <div class="flex justify-end flex-shrink-0 gap-3 px-6 py-4 border-t bg-gray-50 rounded-b-xl">
                <button 
                    type="button" 
                    @click="showDeleteModal = false"
                    class="px-4 py-2 text-sm font-semibold text-gray-800 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100"
                >
                    Batal
                </button>
                <button 
                    type="submit"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-red-600 rounded-lg shadow-sm hover:bg-red-700"
                >
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
            </div>
        </form>
    </div>
</div>
