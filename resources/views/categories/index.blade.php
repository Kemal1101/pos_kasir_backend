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

<div x-data="{ showModal:false, showEditModal:false, showDeleteModal: false }"
    @close-add-modal.window="showModal = false"
    @close-edit-modal.window="showEditModal = false"
    @close-delete-modal.window="showDeleteModal = false"
    >

    {{-- HEADER --}}
    <div class="flex items-center justify-between mt-2 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">
                Selamat Datang, Owner!
            </h2>
            <p class="text-sm text-gray-700 opacity-90 md:text-purple-100">
                Berikut adalah ringkasan toko Anda hari ini
            </p>
        </div>
    </div>

    {{-- SUMMARY STATISTICS --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">

        {{-- Total Kategori --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Kategori</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $categories->total() }}</h3>
                </div>
                <div class="p-3 text-pink-600 rounded-xl bg-pink-100/70">
                    <i class="fa-solid fa-tags fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Produk --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Produk</p>
                    <h3 class="text-3xl font-bold text-gray-800">{{ $produk }}</h3>
                </div>
                <div class="p-3 text-pink-600 rounded-xl bg-pink-100/70">
                    <i class="fa-solid fa-box-open fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Kategori Terbanyak --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Kategori Terbanyak</p>
                    <h3 class="text-3xl font-bold text-gray-800">
                        {{ $mostCategory->name ?? 'N/A' }}
                    </h3>
                </div>
                <div class="p-3 text-pink-600 rounded-xl bg-pink-100/70">
                    <i class="fa-solid fa-star fa-xl"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="p-6 bg-white border border-gray-100 shadow-md rounded-xl">

        <h3 class="mb-4 text-lg font-semibold text-gray-800">Daftar Kategori Produk</h3>

        {{-- Search + Add --}}
        <div class="flex flex-col items-start justify-between w-full gap-3 mb-6 sm:flex-row sm:items-center">

            <form method="GET" action="/category" class="flex w-full gap-3 sm:w-auto">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari Kategori..."
                    class="w-full px-4 py-2 transition border border-gray-300 rounded-lg sm:w-64 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">

                <button type="submit"
                    class="px-4 py-2 font-semibold text-white transition bg-purple-600 rounded-lg shadow-md hover:bg-purple-700 hover:shadow-lg">
                    <i class="mr-1 fa-solid fa-magnifying-glass"></i> Cari
                </button>
            </form>

            <button type="button"
                @click="showModal = true"
                class="px-4 py-2 font-semibold text-white transition bg-purple-600 rounded-lg shadow-md hover:bg-purple-700">
                <i class="mr-1 fa-solid fa-circle-plus"></i> Tambah Kategori
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
                    <tr class="text-xs tracking-wider text-gray-600 uppercase bg-gray-50">
                        <th class="px-4 py-3 font-bold text-left">ID</th>
                        <th class="px-4 py-3 font-bold text-left">Nama Kategori</th>
                        <th class="px-4 py-3 font-bold text-left">Deskripsi</th>
                        <th class="px-4 py-3 font-bold text-left">Jumlah Produk</th>
                        <th class="px-4 py-3 font-bold text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($categories as $cat)
                    <tr class="transition hover:bg-gray-50">

                        <td class="px-4 py-3 font-mono text-gray-700">
                            CAT{{ str_pad($cat->categories_id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="flex items-center gap-3 px-4 py-3 font-medium text-gray-900">
                            <span class="p-2 text-pink-600 bg-pink-100 rounded-full">
                                <i class="fa-solid fa-bookmark fa-xs"></i>
                            </span>
                            {{ $cat->name }}
                        </td>

                        <td class="px-4 py-3 italic text-gray-600">
                            {{ $cat->description ?? '-' }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-gray-700">
                            {{ $cat->products_count }} Produk
                        </td>

                        <td class="flex justify-center gap-3 px-4 py-3">

                            {{-- BUTTON EDIT --}}
                            <button
                                @click="
                                    showEditModal = true;
                                    $nextTick(() => {
                                        window.currentEditId = {{ $cat->categories_id }};
                                        document.getElementById('edit_name').value = @js($cat->name);
                                        document.getElementById('edit_description').value = @js($cat->description);
                                    });
                                "
                                class="text-blue-500 transition hover:text-blue-700"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>

                            {{-- BUTTON DELETE --}}
                            <button
                                @click="
                                    showDeleteModal = true;
                                    $nextTick(() => {
                                    document.getElementById('delete_category_name').innerText = '{{ $cat->name }}';
                                    document.getElementById('delete-category-form').action = '/api/categories/{{ $cat->categories_id }}';
                            })
                                "
                                class="text-red-500 transition hover:text-red-700"
                                >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="5" class="px-4 py-8 italic text-center text-gray-500">
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

<script>
document.addEventListener("DOMContentLoaded", () => {

    // ============================
    // Helper: Ambil Cookie JWT
    // ============================
    function getCookie(name) {
        let value = `; ${document.cookie}`;
        let parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null;
    }
    const jwtToken = getCookie("jwt_token");


    // ============================
    // 1. ADD CATEGORY
    // ============================
    const addForm = document.getElementById("add-category-form");

    if (addForm) {
        addForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            let formData = new FormData(addForm);

            try {
                const response = await fetch("/api/categories/add_category", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Authorization": `Bearer ${jwtToken}`,
                    },
                });

                const result = await response.json();

                if (!response.ok) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: result.meta?.message || "Gagal melakukan request",
                    });
                    return;
                }

                if (result.meta?.status === 200) {

                    // Tutup modal
                    window.dispatchEvent(new CustomEvent("close-add-modal"));

                    setTimeout(() => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: result.meta.message,
                            confirmButtonColor: "#7c3aed",
                        }).then(() => location.reload());
                    }, 200);

                    addForm.reset();
                }

            } catch (error) {
                console.log(error);
                Swal.fire({
                    icon: "error",
                    title: "Kesalahan",
                    text: "Tidak dapat menambah kategori!",
                });
            }
        });
    }



    // ============================
    // 2. EDIT CATEGORY
    // ============================
    const editForm = document.getElementById("edit-category-form");

    if (editForm) {
        editForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const id = window.currentEditId;
            const url = `/api/categories/${id}`;

            const data = {
                name: document.getElementById("edit_name").value,
                description: document.getElementById("edit_description").value,
            };

            try {
                const response = await fetch(url, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "Authorization": `Bearer ${jwtToken}`,
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (!response.ok) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal memperbarui!",
                        text: result.message || "Terjadi kesalahan.",
                    });
                    return;
                }

                if (result.meta?.status === 200) {

                    window.dispatchEvent(new CustomEvent("close-edit-modal"));

                    setTimeout(() => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: result.meta.message,
                            confirmButtonColor: "#7c3aed",
                        }).then(() => location.reload());
                    }, 200);

                    editForm.reset();
                }

            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan saat mengirim data.",
                });
                console.error("Fetch Error:", error);
            }
        });
    }



    // ============================
    // 3. DELETE CATEGORY
    // ============================
    const deleteForm = document.getElementById("delete-category-form");

    if (deleteForm) {
        deleteForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const actionUrl = deleteForm.action;

            try {
                const response = await fetch(actionUrl, {
                    method: "DELETE",
                    headers: {
                        "Accept": "application/json",
                        "Authorization": `Bearer ${jwtToken}`,
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    },
                });

                const result = await response.json();

                if (!response.ok) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal Menghapus!",
                        text: result.message || "Terjadi kesalahan.",
                    });
                    return;
                }

                if (result.meta?.status === 200) {

                    window.dispatchEvent(new CustomEvent("close-delete-modal"));

                    setTimeout(() => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: result.meta.message,
                            confirmButtonColor: "#7c3aed",
                        }).then(() => location.reload());
                    }, 200);

                    deleteForm.reset();
                }

            } catch (error) {
                console.error("DELETE ERROR:", error);

                Swal.fire({
                    icon: "error",
                    title: "Kesalahan!",
                    text: "Tidak dapat menghapus kategori.",
                });
            }
        });
    }

});
</script>
@endsection
