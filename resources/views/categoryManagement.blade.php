@extends('layouts.app')

{{-- SweetAlert Success --}}
@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            confirmButtonColor: '#7c3aed',
        });
    });
</script>
@endif

@section('content')

<div x-data="{ showModal:false, showEditModal:false, showDeleteModal: false }">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-8 mt-2">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">
                Selamat Datang, Owner!
            </h2>
            <p class="text-sm opacity-90 text-gray-700 md:text-purple-100">
                Berikut adalah ringkasan toko Anda hari ini
            </p>
        </div>
    </div>

    {{-- SUMMARY STATISTICS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

        {{-- Total Kategori --}}
        <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Kategori</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $categories->total() }}</h3>
                </div>
                <div class="p-3 rounded-xl bg-pink-100/70 text-pink-600">
                    <i class="fa-solid fa-tags fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Produk --}}
        <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Total Produk</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $produk }}</h3>
                </div>
                <div class="p-3 rounded-xl bg-pink-100/70 text-pink-600">
                    <i class="fa-solid fa-box-open fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Kategori Terbanyak --}}
        <div class="bg-white p-5 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Kategori Terbanyak</p>
                    <h3 class="text-3xl font-bold text-gray-800">
                        {{ $mostCategory->name ?? 'N/A' }}
                    </h3>
                </div>
                <div class="p-3 rounded-xl bg-pink-100/70 text-pink-600">
                    <i class="fa-solid fa-star fa-xl"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white shadow-md border border-gray-100 rounded-xl p-6">

        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Kategori Produk</h3>

        {{-- Search + Add --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full mb-6">

            <form method="GET" action="/category" class="flex gap-3 w-full sm:w-auto">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari Kategori..."
                    class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-300
                           focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">

                <button type="submit"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold shadow-md
                           hover:bg-purple-700 hover:shadow-lg transition">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
                </button>
            </form>

            <button type="button"
                @click="showModal = true"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold shadow-md hover:bg-purple-700 transition">
                <i class="fa-solid fa-circle-plus mr-1"></i> Tambah Kategori
            </button>

        </div>

        {{-- Modal Tambah --}}
        @include('components.modals.add-category')

        {{-- Modal Edit --}}
        @include('components.modals.edit-category')

        {{-- Modal Delete --}}
        @include('components.modals.delete-category')

        {{-- TABLE --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full text-sm divide-y divide-gray-200">

                <thead>
                    <tr class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3 text-left font-bold">ID</th>
                        <th class="px-4 py-3 text-left font-bold">Nama Kategori</th>
                        <th class="px-4 py-3 text-left font-bold">Deskripsi</th>
                        <th class="px-4 py-3 text-left font-bold">Jumlah Produk</th>
                        <th class="px-4 py-3 text-center font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($categories as $cat)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-4 py-3 font-mono text-gray-700">
                            CAT{{ str_pad($cat->categories_id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-4 py-3 text-gray-900 font-medium flex items-center gap-3">
                            <span class="p-2 rounded-full bg-pink-100 text-pink-600">
                                <i class="fa-solid fa-bookmark fa-xs"></i>
                            </span>
                            {{ $cat->name }}
                        </td>

                        <td class="px-4 py-3 text-gray-600 italic">
                            {{ $cat->description ?? '-' }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-gray-700">
                            {{ $cat->products_count }} Produk
                        </td>

                        <td class="px-4 py-3 flex gap-3 justify-center">

                            {{-- BUTTON EDIT --}}
                            <button
                                @click="
                                    showEditModal = true;
                                    $nextTick(() => {
                                        document.getElementById('edit_name').value = @js($cat->name);
                                        document.getElementById('edit_description').value = @js($cat->description);
                                        document.getElementById('edit-category-form').action =
                                            '/categories/{{ $cat->categories_id }}';
                                    });
                                "
                                class="text-blue-500 hover:text-blue-700 transition"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            {{-- BUTTON DELETE --}}
                            <button
                                @click="
                                    showDeleteModal = true;
                                    $nextTick(() => {
                                    document.getElementById('delete_category_name').innerText = '{{ $cat->name }}';
                                    document.getElementById('delete-category-form').action = '/categories/{{ $cat->categories_id }}';
                            })
                                "
                                class="text-red-500 hover:text-red-700 transition"
                                >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic">
                            Belum ada data kategori yang tersedia.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-5">
            {{ $categories->links('components.pagination.custom') }}
        </div>

    </div>

</div>

@endsection
