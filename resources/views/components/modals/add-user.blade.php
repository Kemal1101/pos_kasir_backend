<div
    x-show="showModal"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
>
    <div
        x-show="showModal"
        x-transition.scale.origin.bottom.duration.300ms
        class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200"
        role="dialog" aria-modal="true" aria-labelledby="addUserTitle"
    >
        <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-600 to-purple-500 text-white relative">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fa-solid fa-user-plus text-white text-lg"></i>
                </div>
                <h2 id="addUserTitle" class="text-xl font-semibold">Tambah User Baru</h2>
            </div>
            <button @click="showModal = false" class="absolute right-4 top-4 text-white hover:text-gray-200 transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <form id="add-user-form" class="" autocomplete="off">
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label for="add_name" class="text-sm font-semibold text-gray-700">Nama</label>
                    <input type="text" id="add_name" name="name" required class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add_username" class="text-sm font-semibold text-gray-700">Username</label>
                        <input type="text" id="add_username" name="username" required class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                    </div>
                    <div>
                        <label for="add_email" class="text-sm font-semibold text-gray-700">Email</label>
                        <input type="email" id="add_email" name="email" required class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add_password" class="text-sm font-semibold text-gray-700">Password</label>
                        <input type="password" id="add_password" name="password" minlength="8" required class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" />
                    </div>
                    <div>
                        <label for="add_role_id" class="text-sm font-semibold text-gray-700">Role</label>
                        <select id="add_role_id" name="role_id" class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            <option value="">-- Pilih --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="add_user_alert" class="hidden text-xs font-medium"></div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                <button type="button" @click="showModal = false" class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-800 text-sm font-semibold hover:bg-gray-100 transition">Batal</button>
                <button type="submit" form="add-user-form" class="px-4 py-2 bg-purple-600 rounded-lg text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
