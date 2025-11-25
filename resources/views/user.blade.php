@extends('layouts.app')

@section('content')
<div x-data="{ showModal:false, showEditModal:false, showDeleteModal:false }" class="space-y-8">
    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-2 mt-2">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">Manajemen User</h2>
            <p class="text-sm opacity-90 text-gray-700 md:text-purple-100">Kelola akun pengguna sistem kasir</p>
        </div>
    </div>

    {{-- TABLE + MODAL WRAPPER --}}
    <div class="bg-white shadow-md border border-gray-100 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar User</h3>

        {{-- Search + Add Button --}}
        <div class="mb-6" x-data="{}">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full">
                <form method="GET" action="{{ route('users.manage') }}" class="flex gap-3 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari User..." class="w-full sm:w-64 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                    <select name="role_id" class="px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->role_id }}" @selected($roleFilter == $role->role_id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold shadow-md hover:bg-purple-700 hover:shadow-lg transition">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Filter
                    </button>
                </form>
                <button type="button" @click="showModal = true" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition">
                    <i class="fa-solid fa-circle-plus mr-1"></i> Tambah User
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3 text-left font-bold">ID</th>
                        <th class="px-4 py-3 text-left font-bold">Nama</th>
                        <th class="px-4 py-3 text-left font-bold">Username</th>
                        <th class="px-4 py-3 text-left font-bold">Email</th>
                        <th class="px-4 py-3 text-left font-bold">Role</th>
                        <th class="px-4 py-3 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-mono text-gray-700">USR{{ str_pad($u->user_id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3 text-gray-900 font-medium flex items-center gap-3">
                                <span class="p-2 rounded-full bg-purple-100 text-purple-600"><i class="fa-solid fa-user fa-xs"></i></span>
                                {{ $u->name }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $u->username }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ optional($u->role)->name ?? '-' }}</td>
                            <td class="px-4 py-3 flex gap-3 justify-center">
                                <button type="button"
                                    title="Edit"
                                    @click="
                                        showEditModal = true;
                                        $nextTick(() => {
                                            document.getElementById('edit_user_id').value='{{ $u->user_id }}';
                                            document.getElementById('edit_name').value=@js($u->name);
                                            document.getElementById('edit_username').value=@js($u->username);
                                            document.getElementById('edit_email').value=@js($u->email);
                                            document.getElementById('edit_role_id').value='{{ $u->role_id }}';
                                        });
                                    "
                                    class="text-blue-500 hover:text-blue-700 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" title="Hapus" data-id="{{ $u->user_id }}" class="btnDelete text-red-500 hover:text-red-700 transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">Belum ada data user yang tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-5">
            {{ $users->links('pagination::tailwind') }}
        </div>
    </div>

    {{-- MODALS --}}
    @include('components.modals.add-user')
    @include('components.modals.edit-user')

    <script>
        // 1. Fungsi Ambil Token
        function getJwtToken() {
            let token = localStorage.getItem('jwt_token') || localStorage.getItem('token');
            if (!token) {
                const m = document.cookie.split('; ').find(x => x.startsWith('jwt_token='));
                token = m ? decodeURIComponent(m.split('=')[1]) : null;
            }
            return token;
        }

        // Helper: Tampilkan Alert Sukses lalu Reload
        function showSuccessAndReload(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                confirmButtonColor: '#7c3aed',
                confirmButtonText: 'OK'
            }).then((result) => {
                // Halaman hanya direload setelah user klik OK
                if (result.isConfirmed || result.isDismissed) {
                    location.reload();
                }
            });
        }

        // 2. Logic DELETE User
        document.querySelectorAll('.btnDelete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (!id) return;

                // Gunakan SweetAlert Confirm
                Swal.fire({
                    title: 'Hapus user ini?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const token = getJwtToken();
                        fetch(`/api/users/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                ...(token ? { 'Authorization': 'Bearer ' + token } : {})
                            }
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.meta && res.meta.status === 200) {
                                showSuccessAndReload(res.meta.message);
                            } else {
                                Swal.fire('Gagal!', (res.meta && res.meta.message) ? res.meta.message : 'Gagal menghapus', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error!', 'Terjadi kesalahan jaringan', 'error'));
                    }
                });
            });
        });

        // 3. Logic ADD User
        const addForm = document.getElementById('add-user-form');
        if (addForm) {
            addForm.addEventListener('submit', e => {
                e.preventDefault();
                const token = getJwtToken();

                const payload = {
                    name: document.getElementById('add_name').value,
                    username: document.getElementById('add_username').value,
                    email: document.getElementById('add_email').value,
                    password: document.getElementById('add_password').value,
                    role_id: document.getElementById('add_role_id').value || null,
                };

                const alertBox = document.getElementById('add_user_alert');
                alertBox?.classList.add('hidden');

                fetch('/api/users/add_user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(token ? { 'Authorization': 'Bearer ' + token } : {})
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.meta && res.meta.status === 200) {
                        // Sembunyikan modal (opsional, via AlpineJS data jika perlu)
                        // document.querySelector('[x-data]').__x.$data.showModal = false;

                        // PANGGIL FUNGSI SUKSES DI SINI
                        showSuccessAndReload(res.meta.message);

                    } else {
                        const errorMsg = (res.meta && res.meta.message) ? res.meta.message : 'Gagal menyimpan';
                        if (alertBox) {
                            alertBox.textContent = errorMsg;
                            alertBox.className = 'text-xs font-medium text-red-600';
                            alertBox.classList.remove('hidden');
                        }
                    }
                })
                .catch((err) => {
                    if (alertBox) {
                        alertBox.textContent = 'Error jaringan';
                        alertBox.className = 'text-xs font-medium text-red-600';
                        alertBox.classList.remove('hidden');
                    }
                });
            });
        }

        // 4. Logic EDIT User
        const editForm = document.getElementById('edit-user-form');
        if (editForm) {
            editForm.addEventListener('submit', e => {
                e.preventDefault();
                const token = getJwtToken();
                const id = document.getElementById('edit_user_id').value;

                const payload = {
                    name: document.getElementById('edit_name').value,
                    username: document.getElementById('edit_username').value,
                    email: document.getElementById('edit_email').value,
                    role_id: document.getElementById('edit_role_id').value || null,
                };

                const pwd = document.getElementById('edit_password').value;
                if (pwd) payload.password = pwd;

                const alertBox = document.getElementById('edit_user_alert');
                alertBox?.classList.add('hidden');

                fetch(`/api/users/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        ...(token ? { 'Authorization': 'Bearer ' + token } : {})
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.meta && res.meta.status === 200) {
                        // PANGGIL FUNGSI SUKSES DI SINI
                        showSuccessAndReload(res.meta.message);
                    } else {
                        const errorMsg = (res.meta && res.meta.message) ? res.meta.message : 'Gagal menyimpan';
                        if (alertBox) {
                            alertBox.textContent = errorMsg;
                            alertBox.className = 'text-xs font-medium text-red-600';
                            alertBox.classList.remove('hidden');
                        }
                    }
                })
                .catch(() => {
                    if (alertBox) {
                        alertBox.textContent = 'Error jaringan';
                        alertBox.className = 'text-xs font-medium text-red-600';
                        alertBox.classList.remove('hidden');
                    }
                });
            });
        }
    </script>
</div>
@endsection
