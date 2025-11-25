<div
    x-show="showModal"
    x-transition.opacity
    x-cloak
    x-ref="modalAddCategory"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
>
    <div
        x-show="showModal"
        x-transition.scale.origin.bottom.duration.300ms
        class="w-full max-w-lg overflow-hidden bg-white border border-gray-200 shadow-2xl rounded-2xl"
    >
        {{-- Header --}}
        <div class="relative px-6 py-4 text-white border-b bg-gradient-to-r from-purple-600 to-purple-500">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20">
                    <i class="text-lg text-white fa-solid fa-tags"></i>
                </div>
                <h2 class="text-xl font-semibold">Tambah Kategori Baru</h2>
            </div>

            <button 
                @click="showModal = false"
                class="absolute text-white transition right-4 top-4 hover:text-gray-200"
            >
                <i class="text-xl fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body --}}
        <form id="add-category-form">
            @csrf

            <div class="px-6 py-5 space-y-4">

                <div>
                    <label for="name" class="text-sm font-semibold text-gray-700">Nama Kategori</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        required
                        class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    >
                </div>

                <div>
                    <label for="description" class="text-sm font-semibold text-gray-700">Deskripsi (Opsional)</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3"
                        class="w-full px-4 py-2 mt-1 transition border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    ></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                
                <button 
                    type="button"
                    @click="showModal = false"
                    class="px-4 py-2 text-sm font-semibold text-gray-800 transition bg-white border border-gray-300 rounded-lg hover:bg-gray-100"
                >
                    Batal
                </button>

                <button 
                    type="submit"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition bg-purple-600 rounded-lg shadow-sm hover:bg-purple-700"
                >
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </div>
        </form>

    </div>
</div>
