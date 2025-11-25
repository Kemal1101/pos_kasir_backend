<div
    x-show="showDeleteModal"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
>
    <div
        x-show="showDeleteModal"
        x-transition.scale.origin.center.duration.300ms
        class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200"
    >

        {{-- HEADER --}}
        <div class="px-6 py-4 border-b bg-gradient-to-r from-red-600 to-red-500 text-white relative">
            <h2 class="text-xl font-semibold">Hapus Kategori</h2>

            {{-- Close Button --}}
            <button 
                @click="showDeleteModal = false"
                class="absolute right-4 top-4 text-white hover:text-gray-200 transition"
            >
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="px-6 py-5 space-y-3">
            <p class="text-gray-700 text-sm">
                Apakah Anda yakin ingin menghapus kategori:
            </p>

            <p class="text-lg font-semibold text-red-600" id="delete_category_name"></p>

            <p class="text-xs text-gray-500 italic">
                *Menghapus kategori juga dapat berdampak pada produk yang terkait.
            </p>
        </div>

        {{-- FOOTER --}}
        <form id="delete-category-form" method="POST">
            @csrf
            @method('DELETE')

            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">

                <button 
                    type="button"
                    @click="showDeleteModal = false"
                    class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-800 text-sm font-semibold hover:bg-gray-100 transition"
                >
                    Batal
                </button>

                <button 
                    type="submit"
                    class="px-4 py-2 bg-red-600 rounded-lg text-white text-sm font-semibold hover:bg-red-700 transition shadow-sm flex items-center gap-2"
                >
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>

            </div>
        </form>

    </div>
</div>
