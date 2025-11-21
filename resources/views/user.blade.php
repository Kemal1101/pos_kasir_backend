@extends('layouts.app')

@section('content')
<div x-data="{ showAddUser:false, showEditUser:false }" class="space-y-8">
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
                <button id="btnOpenCreate" type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition">
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
                                <button type="button" title="Edit" data-user="{{ json_encode(['user_id'=>$u->user_id,'name'=>$u->name,'username'=>$u->username,'email'=>$u->email,'role_id'=>$u->role_id]) }}" class="btnEdit text-blue-500 hover:text-blue-700 transition">
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

    {{-- PAGE SCRIPT --}}
    <script>
    function getJwtToken(){
        const m = document.cookie.split('; ').find(x=>x.startsWith('jwt_token='));
        return m ? decodeURIComponent(m.split('=')[1]) : null;
    }
    const alpineRoot = document.querySelector('[x-data]');
    const openCreateBtn = document.getElementById('btnOpenCreate');
    if(openCreateBtn){ openCreateBtn.addEventListener('click',()=>{ if(alpineRoot?.__x?.$data){ alpineRoot.__x.$data.showAddUser = true; } }); }

    document.querySelectorAll('.btnEdit').forEach(btn=>{
        btn.addEventListener('click',()=>{
            const raw = btn.getAttribute('data-user'); if(!raw) return;
            let data; try { data = JSON.parse(raw); } catch(e){ return; }
            const idEl = document.getElementById('edit_user_id'); if(!idEl) return;
            idEl.value = data.user_id || '';
            document.getElementById('edit_name').value = data.name || '';
            document.getElementById('edit_username').value = data.username || '';
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_role_id').value = (data.role_id ?? '');
            if(alpineRoot?.__x?.$data){ alpineRoot.__x.$data.showEditUser = true; }
        });
    });

    document.querySelectorAll('.btnDelete').forEach(btn=>{
        btn.addEventListener('click',()=>{
            const id = btn.getAttribute('data-id'); if(!id) return;
            if(!confirm('Hapus user ini?')) return;
            const token = getJwtToken();
            fetch(`/api/users/${id}`, { method:'DELETE', headers:{ 'Accept':'application/json', ...(token?{ 'Authorization': 'Bearer '+token }: {}) } })
                .then(r=>r.json())
                .then(res=>{ if(res.success){ location.reload(); } else { alert(res.message || 'Gagal menghapus'); } })
                .catch(()=>alert('Error jaringan'));
        });
    });

    const addForm = document.getElementById('add-user-form');
    if(addForm){ addForm.addEventListener('submit', e => {
        e.preventDefault();
        const token = getJwtToken();
        const payload = {
            name: document.getElementById('add_name').value,
            username: document.getElementById('add_username').value,
            email: document.getElementById('add_email').value,
            password: document.getElementById('add_password').value,
            role_id: document.getElementById('add_role_id').value || null,
        };
        const alertBox = document.getElementById('add_user_alert'); alertBox?.classList.add('hidden');
        fetch('/api/users/add_user', { method:'POST', headers:{ 'Content-Type':'application/json', 'Accept':'application/json', ...(token?{ 'Authorization': 'Bearer '+token }: {}) }, body: JSON.stringify(payload) })
            .then(r=>r.json())
            .then(res=>{ if(res.success){ location.reload(); } else if(alertBox){ alertBox.textContent = res.message || 'Gagal menyimpan'; alertBox.className='text-xs font-medium text-red-600'; alertBox.classList.remove('hidden'); } })
            .catch(()=>{ if(alertBox){ alertBox.textContent='Error jaringan'; alertBox.className='text-xs font-medium text-red-600'; alertBox.classList.remove('hidden'); } });
    }); }

    const editForm = document.getElementById('edit-user-form');
    if(editForm){ editForm.addEventListener('submit', e => {
        e.preventDefault();
        const token = getJwtToken();
        const id = document.getElementById('edit_user_id').value;
        const payload = {
            name: document.getElementById('edit_name').value,
            username: document.getElementById('edit_username').value,
            email: document.getElementById('edit_email').value,
            role_id: document.getElementById('edit_role_id').value || null,
        };
        const pwd = document.getElementById('edit_password').value; if(pwd) payload.password = pwd;
        const alertBox = document.getElementById('edit_user_alert'); alertBox?.classList.add('hidden');
        fetch(`/api/users/${id}`, { method:'PUT', headers:{ 'Content-Type':'application/json', 'Accept':'application/json', ...(token?{ 'Authorization': 'Bearer '+token }: {}) }, body: JSON.stringify(payload) })
            .then(r=>r.json())
            .then(res=>{ if(res.success){ location.reload(); } else if(alertBox){ alertBox.textContent = res.message || 'Gagal menyimpan'; alertBox.className='text-xs font-medium text-red-600'; alertBox.classList.remove('hidden'); } })
            .catch(()=>{ if(alertBox){ alertBox.textContent='Error jaringan'; alertBox.className='text-xs font-medium text-red-600'; alertBox.classList.remove('hidden'); } });
    }); }
    </script>
</div>
@endsection
