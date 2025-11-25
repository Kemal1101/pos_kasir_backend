@extends('layouts.app')

@section('content')
<div x-data="productData()"
     @close-add-modal.window="showAdd = false"
     @close-edit-modal.window="showEdit = false">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mt-2 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">
                Data Produk
            </h2>
            <p class="text-sm text-gray-700 opacity-90 md:text-purple-100">
                Kelola produk toko Anda di sini.
            </p>
        </div>
    </div>

     <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3">
    {{-- Total Kategori --}}
    <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Total Kategori</p>
                <h3 class="text-3xl font-bold text-gray-800">{{ $categories->count() }}</h3>
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
                <h3 class="text-3xl font-bold text-gray-800">{{ $products->total() }}</h3>
            </div>
            <div class="p-3 text-pink-600 rounded-xl bg-pink-100/70">
                <i class="fa-solid fa-box-open fa-xl"></i>
            </div>
        </div>
    </div>

    {{-- Produk Terbanyak Terjual --}}
    <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Produk Terbanyak Terjual</p>
                <h3 class="text-3xl font-bold text-gray-800">
                    {{ $mostSoldProduct ? $mostSoldProduct->name_product : 'N/A' }}
                </h3>
                @if($mostSoldProduct)
                    <p class="text-sm text-gray-500">{{ $mostSoldProduct->total_sold }} pcs terjual</p>
                @endif
            </div>
            <div class="p-3 text-pink-600 rounded-xl bg-pink-100/70">
                <i class="fa-solid fa-star fa-xl"></i>
            </div>
        </div>
    </div>

</div>


    {{-- TABLE --}}
    <div class="p-6 bg-white border border-gray-100 shadow-md rounded-xl">

    <h3 class="mb-4 text-lg font-semibold text-gray-800">Daftar Produk</h3>

    {{-- Search + Add --}}
    <div class="flex flex-col items-start justify-between w-full gap-3 mb-6 sm:flex-row sm:items-center">

        <form method="GET" action="/products" class="flex w-full gap-3 sm:w-auto">
            <input type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari Produk..."
                class="w-full px-4 py-2 transition border border-gray-300 rounded-lg sm:w-64 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">

            <button type="submit"
                class="px-4 py-2 font-semibold text-white transition bg-purple-600 rounded-lg shadow-md hover:bg-purple-700 hover:shadow-lg">
                <i class="mr-1 fa-solid fa-magnifying-glass"></i> Cari
            </button>
        </form>

        <button type="button"
            @click="showAdd = true"
            class="px-4 py-2 font-semibold text-white transition bg-purple-600 rounded-lg shadow-md hover:bg-purple-700">
            <i class="mr-1 fa-solid fa-circle-plus"></i> Tambah Produk
        </button>

    </div>

    {{-- Modal Tambah/Edit/Delete --}}
    @include('components.modals.add-product')
    @include('components.modals.edit-product')
    @include('components.modals.delete-product')

    {{-- TABLE --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full text-sm divide-y divide-gray-200">

            <thead>
                <tr class="text-xs tracking-wider text-gray-600 uppercase bg-gray-50">
                    <th class="px-4 py-3 font-bold text-left">ID</th>
                    <th class="px-4 py-3 font-bold text-left">Nama Produk</th>
                    <th class="px-4 py-3 font-bold text-left">Kategori</th>
                    <th class="px-4 py-3 font-bold text-left">Harga Jual</th>
                    <th class="px-4 py-3 font-bold text-left">Stok</th>
                    <th class="px-4 py-3 font-bold text-center">Gambar</th>
                    <th class="px-4 py-3 font-bold text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse ($products as $p)
                <tr class="transition hover:bg-gray-50">

                    <td class="px-4 py-3 font-mono text-gray-700">
                        PRD{{ str_pad($p->product_id, 3, '0', STR_PAD_LEFT) }}
                    </td>

                    <td class="flex items-center gap-3 px-4 py-3 font-medium text-gray-900">
                        <span class="p-2 text-pink-600 bg-pink-100 rounded-full">
                            <i class="fa-solid fa-box fa-xs"></i>
                        </span>
                        {{ $p->name }}
                    </td>

                    <td class="px-4 py-3 text-gray-700">
                        {{ $p->category->name ?? '-' }}
                    </td>

                    <td class="px-4 py-3 font-bold text-green-700">
                        Rp {{ number_format($p->selling_price,0,',','.') }}
                    </td>

                    <td class="px-4 py-3 font-semibold text-gray-700">
                        {{ $p->stock }} pcs
                    </td>

                    <td class="px-4 py-3 text-center">
                        @if ($p->product_images)
                            <img src="{{ $p->product_images }}" class="object-cover w-12 h-12 mx-auto rounded-md">
                        @else
                            <span class="italic text-gray-400">No Img</span>
                        @endif
                    </td>

                    <td class="flex justify-center gap-3 px-4 py-3">

                        {{-- BUTTON EDIT --}}
                        <button
    @click="
        showEdit = true;
        $nextTick(() => {
            document.getElementById('edit_product_id').value = '{{ $p->product_id }}';
            document.getElementById('edit_name').value = @js($p->name);
            document.getElementById('edit_cost_price').value = @js($p->cost_price);
            document.getElementById('edit_selling_price').value = @js($p->selling_price);
            document.getElementById('edit_stock').value = @js($p->stock);
            document.getElementById('edit_description').value = @js($p->description);
            document.getElementById('edit_categoty_id').value='{{ $p->categories_id }}';

            // Preview gambar
            if(@js($p->product_images)) {
                let img = document.getElementById('preview_product_image');
                img.src = @js($p->product_images);
                img.classList.remove('hidden');
            }
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
                                    document.getElementById('delete_product_name').innerText = '{{ $p->name }}';
                                    document.getElementById('delete-product-form').action = '/api/products/{{ $p->product_id }}';
                                });
                            "
                            class="text-red-500 transition hover:text-red-700"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 italic text-center text-gray-500">
                        Belum ada produk terdaftar.
                    </td>
                </tr>
                @endforelse

            </tbody>

        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-5">
        {{ $products->links('components.pagination.custom') }}
    </div>

</div>

</div>

<script>
function productData() {
    return {
        showAdd: false,
        showEdit: false,
        showDeleteModal: false,
        product: null,
        jwtToken: null,

        init() {
            this.jwtToken = (() => {
                let v = `; ${document.cookie}`;
                let parts = v.split(`; jwt_token=`);
                if(parts.length === 2) return parts.pop().split(";").shift();
                return null;
            })();
        },

        openEdit(prod) {
            this.product = prod;
            this.showEdit = true;
        },

        openDelete(prod) {
            this.product = prod;
            this.showDeleteModal = true;
        },

        async addProduct() {
            let form = document.getElementById("add-product-form");
            let formData = new FormData(form);
            try {
                const res = await fetch("/api/products/add_product", {
                    method:"POST",
                    body: formData,
                    headers:{
                        "Accept":"application/json",
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                        "Authorization": `Bearer ${this.jwtToken}`
                    }
                });
                const result = await res.json();
                if(!res.ok) throw new Error(result.meta?.message || 'Gagal menambah produk');
                this.showAdd = false;
                Swal.fire({icon:"success", title:"Berhasil!", text: result.meta.message, confirmButtonColor:"#7c3aed"})
                    .then(()=>location.reload());
            } catch(err) {
                console.error(err);
                Swal.fire({icon:"error", title:"Kesalahan", text: err.message});
            }
        },

        async editProduct() {
    try {
        const form = document.getElementById("edit-product-form");
        const formData = new FormData(form);
        const id = document.getElementById("edit_product_id").value;

        const res = await fetch(`/api/products/${id}`, {
            method: "POST", // tetap POST
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                "Authorization": `Bearer ${this.jwtToken}`,
                "X-HTTP-Method-Override": "PUT" // trick untuk Laravel
            },
            body: formData
        });

        const result = await res.json();

        if (!res.ok) throw new Error(result.meta?.message || "Gagal mengubah produk");

        this.showEdit = false;

        Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: result.meta.message,
            confirmButtonColor: "#7c3aed"
        }).then(() => location.reload());

    } catch(err) {
        console.error(err);
        Swal.fire({
            icon: "error",
            title: "Kesalahan",
            text: err.message
        });
    }
},

        async deleteProduct() {
    let form = document.getElementById('delete-product-form');
    const url = form.action; // ambil dari action form

    try {
        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept':'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Authorization': `Bearer ${this.jwtToken}`
            }
        });
        const result = await res.json();
        if (!res.ok) throw new Error(result.meta?.message || 'Gagal menghapus produk');
        this.showDeleteModal = false;
        Swal.fire({
            icon:'success',
            title:'Berhasil',
            text: result.meta?.message || 'Produk dihapus',
            confirmButtonColor:'#7c3aed'
        }).then(() => location.reload());
    } catch(err) {
        console.error(err);
        Swal.fire({icon:'error', title:'Kesalahan', text: err.message});
    }
}

    }
}
</script>
@endsection
