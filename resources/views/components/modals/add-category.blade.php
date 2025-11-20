<div
    x-show="showModal"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
>

    {{-- Panel Modal --}}
    <div
        x-show="showModal"
        x-transition.scale.origin.bottom.duration.300ms
        class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200"
    >

        {{-- Header --}}
        <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-600 to-purple-500 text-white relative">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-tags text-white text-lg"></i>
                </div>
                <h2 class="text-xl font-semibold">Tambah Kategori Baru</h2>
            </div>

            {{-- Tombol X --}}
            <button 
                @click="showModal = false"
                class="absolute right-4 top-4 text-white hover:text-gray-200 transition"
            >
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        {{-- Body --}}
        <form id="add-category-form" action="/categories/add_category" method="POST">
            @csrf

            <div class="px-6 py-5 space-y-4">

                {{-- Nama Kategori --}}
                <div>
                    <label for="name" class="text-sm font-semibold text-gray-700">Nama Kategori</label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name"
                        required
                        class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                    >
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="text-sm font-semibold text-gray-700">Deskripsi (Opsional)</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="3"
                        class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                    ></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                
                <button 
                    type="button"
                    @click="showModal = false"
                    class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-800 text-sm font-semibold hover:bg-gray-100 transition"
                >
                    Batal
                </button>

                <button 
                    type="submit"
                    form="add-category-form"
                    class="px-4 py-2 bg-purple-600 rounded-lg text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm flex items-center gap-2"
                >
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </div>
        </form>

    </div>
</div>
