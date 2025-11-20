@extends('layouts.app')

@section('content')
<div class="mb-8">
	<h2 class="text-2xl font-bold text-gray-800">Manajemen User</h2>
	<p class="text-sm text-gray-500 mt-1">Kelola akun pengguna sistem kasir.</p>
</div>

<div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm">
	<form method="GET" action="{{ route('users.manage') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
		<div class="col-span-2">
			<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Pencarian</label>
			<input type="text" name="search" value="{{ $search }}" placeholder="Nama, email, username" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm" />
		</div>
		<div>
			<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Role</label>
			<select name="role_id" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm">
				<option value="">Semua Role</option>
				@foreach($roles as $role)
					<option value="{{ $role->role_id }}" @selected($roleFilter == $role->role_id)>{{ $role->name }}</option>
				@endforeach
			</select>
		</div>
		<div class="flex items-end">
			<button class="inline-flex items-center px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold shadow">Filter</button>
		</div>
	</form>
</div>

<div class="flex justify-between items-center mb-3">
	<h3 class="text-lg font-semibold text-gray-700">Daftar User</h3>
	<button id="btnOpenCreate" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold">
		<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
		Tambah User
	</button>
</div>

<div class="overflow-hidden border border-gray-200 rounded-xl bg-white shadow-sm">
	<table class="w-full text-sm">
		<thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
			<tr>
				<th class="px-4 py-2 text-left">#</th>
				<th class="px-4 py-2 text-left">Nama</th>
				<th class="px-4 py-2 text-left">Username</th>
				<th class="px-4 py-2 text-left">Email</th>
				<th class="px-4 py-2 text-left">Role</th>
				<th class="px-4 py-2 text-right">Aksi</th>
			</tr>
		</thead>
		<tbody class="divide-y divide-gray-100">
			@forelse($users as $u)
				<tr class="hover:bg-gray-50">
					<td class="px-4 py-2 text-gray-500">{{ $u->user_id }}</td>
					<td class="px-4 py-2 font-medium text-gray-800">{{ $u->name }}</td>
					<td class="px-4 py-2 text-gray-700">{{ $u->username }}</td>
					<td class="px-4 py-2 text-gray-600">{{ $u->email }}</td>
					<td class="px-4 py-2 text-gray-700">{{ optional($u->role)->name ?? '-' }}</td>
					<td class="px-4 py-2 text-right space-x-1">
						<button data-user="{{ json_encode([
                            'user_id' => $u->user_id,
                            'name' => $u->name,
                            'username' => $u->username,
                            'email' => $u->email,
                            'role_id' => $u->role_id
                        ]) }}" class="btnEdit inline-flex items-center px-2 py-1 rounded-md bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold">
							Edit
						</button>
						<button data-id="{{ $u->user_id }}" class="btnDelete inline-flex items-center px-2 py-1 rounded-md bg-red-500 hover:bg-red-600 text-white text-xs font-semibold">
							Hapus
						</button>
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Tidak ada user ditemukan.</td>
				</tr>
			@endforelse
		</tbody>
	</table>

	<div class="p-4 bg-gray-50 flex items-center justify-between text-xs text-gray-600">
		<div>
			Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} user
		</div>
		<div class="space-x-1">
			@if($users->onFirstPage())
				<span class="px-3 py-1 rounded bg-gray-200">Prev</span>
			@else
				<a href="{{ $users->previousPageUrl() }}" class="px-3 py-1 rounded bg-white border hover:bg-gray-100">Prev</a>
			@endif
			@if($users->hasMorePages())
				<a href="{{ $users->nextPageUrl() }}" class="px-3 py-1 rounded bg-white border hover:bg-gray-100">Next</a>
			@else
				<span class="px-3 py-1 rounded bg-gray-200">Next</span>
			@endif
		</div>
	</div>
</div>

<!-- Modal Create/Edit -->
<div id="modalUser" class="fixed inset-0 hidden items-center justify-center bg-black/40 backdrop-blur-sm z-40">
	<div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 relative">
		<button id="btnCloseModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
			<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
		</button>
		<h3 id="modalTitle" class="text-lg font-bold text-gray-800 mb-4">Tambah User</h3>
		<form id="formUser" class="space-y-4">
			<input type="hidden" name="user_id" id="user_id" />
			<div>
				<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Nama</label>
				<input type="text" name="name" id="name" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm" required />
			</div>
			<div class="grid grid-cols-2 gap-4">
				<div>
					<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Username</label>
					<input type="text" name="username" id="username" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm" required />
				</div>
				<div>
					<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Email</label>
					<input type="email" name="email" id="email" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm" required />
				</div>
			</div>
			<div class="grid grid-cols-2 gap-4">
				<div>
					<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Password <span class="text-gray-400">(minimal 8)</span></label>
					<input type="password" name="password" id="password" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm" />
				</div>
				<div>
					<label class="text-xs font-semibold text-gray-600 uppercase mb-1 block">Role</label>
					<select name="role_id" id="role_id" class="w-full rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 text-sm">
						<option value="">-- Pilih --</option>
						@foreach($roles as $role)
							<option value="{{ $role->role_id }}">{{ $role->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div id="formAlert" class="hidden text-xs font-medium"></div>
			<div class="flex justify-end gap-3 pt-2">
				<button type="button" id="btnCancel" class="px-4 py-2 rounded-lg border text-sm font-medium hover:bg-gray-50">Batal</button>
				<button type="submit" id="btnSubmit" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold">Simpan</button>
			</div>
		</form>
	</div>
</div>

<!-- Simple Toast -->
<div id="toast" class="hidden fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"></div>

<script>
const modal = document.getElementById('modalUser');
const btnOpenCreate = document.getElementById('btnOpenCreate');
const btnCloseModal = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancel');
const form = document.getElementById('formUser');
const modalTitle = document.getElementById('modalTitle');
const formAlert = document.getElementById('formAlert');
const toast = document.getElementById('toast');

function openModal(mode = 'create', data = null){
	modal.classList.remove('hidden');
	form.reset();
	formAlert.classList.add('hidden');
	document.getElementById('user_id').value='';
	if(mode==='edit' && data){
		modalTitle.textContent='Edit User';
		document.getElementById('user_id').value=data.user_id;
		document.getElementById('name').value=data.name;
		document.getElementById('username').value=data.username;
		document.getElementById('email').value=data.email;
		document.getElementById('role_id').value=data.role_id ?? '';
		document.getElementById('password').value='';
	} else {
		modalTitle.textContent='Tambah User';
	}
}
function closeModal(){ modal.classList.add('hidden'); }
function showToast(msg, type='success'){
	toast.textContent=msg;
	toast.className='fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm font-medium '+(type==='success'?'bg-green-600 text-white':'bg-red-600 text-white');
	toast.classList.remove('hidden');
	setTimeout(()=>toast.classList.add('hidden'),3000);
}

btnOpenCreate.addEventListener('click',()=>openModal('create'));
btnCloseModal.addEventListener('click',closeModal);
btnCancel.addEventListener('click',closeModal);
modal.addEventListener('click',e=>{ if(e.target===modal) closeModal(); });

document.querySelectorAll('.btnEdit').forEach(btn=>{
	btn.addEventListener('click',()=>{
		const data = JSON.parse(btn.getAttribute('data-user'));
		openModal('edit', data);
	});
});

document.querySelectorAll('.btnDelete').forEach(btn=>{
	btn.addEventListener('click',()=>{
		const id = btn.getAttribute('data-id');
		if(!confirm('Hapus user ini?')) return;
		fetch(`/api/users/${id}`, { method:'DELETE', headers:{ 'Accept':'application/json' } })
			.then(r=>r.json())
			.then(res=>{
				if(res.success){
					showToast('User dihapus');
					setTimeout(()=>window.location.reload(),700);
				} else {
					showToast(res.message || 'Gagal menghapus', 'error');
				}
			})
			.catch(()=>showToast('Error jaringan','error'));
	});
});

form.addEventListener('submit', e => {
	e.preventDefault();
	formAlert.classList.add('hidden');
	const formData = new FormData(form);
	const id = formData.get('user_id');
	const payload = {};
	formData.forEach((v,k)=>{ if(v!=='' && k!=='user_id') payload[k]=v; });
	const method = id ? 'PUT' : 'POST';
	const url = id ? `/api/users/${id}` : '/api/users/add_user';
	fetch(url, {
		method: method,
		headers: { 'Content-Type':'application/json', 'Accept':'application/json' },
		body: JSON.stringify(payload)
	})
	.then(r=>r.json())
	.then(res=>{
		if(res.success){
			showToast(id?'User diperbarui':'User dibuat');
			closeModal();
			setTimeout(()=>window.location.reload(),800);
		} else {
			formAlert.textContent = res.message || 'Gagal menyimpan';
			formAlert.className='text-xs font-medium text-red-600';
			formAlert.classList.remove('hidden');
		}
	})
	.catch(()=>{
		formAlert.textContent='Error jaringan';
		formAlert.className='text-xs font-medium text-red-600';
		formAlert.classList.remove('hidden');
	});
});
</script>
@endsection

