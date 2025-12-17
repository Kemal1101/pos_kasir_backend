<!-- Desktop Avatar Component - Fixed Position -->
@if (auth()->check())
    <div id="desktopAvatarFixed" 
         x-data="{ openDropdown: false }" 
         class="items-center gap-3">
        
        <button @click="openDropdown = !openDropdown" class="flex items-center gap-3 focus:outline-none">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-medium text-white drop-shadow-lg">{{ auth()->user()->name }}</p>
                <p class="text-xs text-purple-200 drop-shadow">{{ auth()->user()->role->name ?? 'User' }}</p>
            </div>
            <div class="flex items-center justify-center w-10 h-10 text-sm font-semibold text-purple-700 bg-white rounded-full ring-2 ring-purple-300 shadow-lg">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div x-show="openDropdown" 
             x-cloak
             @click.outside="openDropdown = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute top-full right-0 w-48 py-1 mt-2 bg-white rounded-lg shadow-xl border border-gray-200">
            <div class="px-4 py-3 border-b">
                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout.perform') }}">
                @csrf
                <button type="submit" class="flex items-center w-full gap-2 px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
@endif
