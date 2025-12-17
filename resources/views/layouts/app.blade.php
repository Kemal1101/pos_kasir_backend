<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('Logo & APP.png') }}">
    <title>Dashboard - Toko Saya</title>

    <!-- CSS FONT AWESOME DI SINI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .bg-sidebar {
            background: linear-gradient(180deg, #7C3AED 0%, #6D28D9 100%);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Avatar - positioned absolute, scrolls with content */
        .avatar-fixed-wrapper {
            position: absolute !important;
            top: 1rem !important;
            right: 1.5rem !important;
            z-index: 9999999 !important;
            pointer-events: auto !important;
            display: none !important;
        }
        
        @media (min-width: 768px) {
            .avatar-fixed-wrapper {
                display: flex !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50" x-data="{ openSidebar: false, openDropdown: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- BACKDROP (mobile only) -->
        <div class="fixed inset-0 z-20 bg-black/40 md:hidden" x-show="openSidebar" x-transition.opacity
            @click="openSidebar = false">
        </div>

        <!-- SIDEBAR -->
        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 text-white transition-transform duration-300 transform bg-sidebar md:translate-x-0 md:relative"
            :class="openSidebar ? 'translate-x-0' : '-translate-x-full'">

            <!-- Branding -->
            <div class="flex items-center gap-3 p-6">
                <div class="p-2 bg-white rounded-lg">
                    <svg class="w-6 h-6 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight">Toko Saya</h1>
                    <p class="text-xs text-purple-200">Owner</p>
                </div>
            </div>

            <!-- NAV -->
            <nav class="flex-1 px-4 mt-4 space-y-2">

                @if (auth()->check() && in_array(auth()->user()->role_id, [1]))
                    <a href="/dashboard"
                        class="{{ request()->is('dashboard') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <a href="/category"
                        class="{{ request()->is('category') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                        <span class="font-medium">Kategori</span>
                    </a>

                    <a href="/products"
                        class="{{ request()->is('products') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span class="font-medium">Produk</span>
                    </a>

                    <a href="{{ route('users.manage') }}"
                        class="{{ request()->is('users') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="font-medium">User</span>
                    </a>

                    <a href="{{ route('sales.report') }}"
                        class="{{ request()->is('laporan-penjualan') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="font-medium">Laporan</span>
                    </a>
                @endif

                @if (auth()->check() && auth()->user()->role_id == 3)
                    <a href="/dashboard"
                        class="{{ request()->is('dashboard') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>


                    <a href="{{ route('stock-additions.index') }}"
                        class="{{ request()->is('stock-additions*') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="font-medium">Tambah Stok</span>
                    </a>
                @endif
            </nav>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="relative flex-1 h-full overflow-y-auto bg-gray-50" id="mainContent">

            <!-- Desktop Avatar - Scrolls with content -->
            @if (auth()->check())
                <div class="avatar-fixed-wrapper">
                    <div class="relative flex items-center gap-3">
                        <button @click="openDropdown = !openDropdown" @click.outside="openDropdown = false"
                            class="flex items-center gap-3 px-4 py-2 transition-all duration-200 bg-white rounded-full shadow-lg hover:shadow-xl">
                            <div class="hidden text-right sm:block">
                                <div class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ auth()->user()->role->name ?? 'User' }}</div>
                            </div>
                            <div class="flex items-center justify-center w-10 h-10 font-bold text-white rounded-full bg-gradient-to-br from-purple-600 to-blue-500">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <svg class="w-4 h-4 text-gray-600 transition-transform" :class="openDropdown && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="openDropdown" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 w-64 mt-2 bg-white rounded-lg shadow-xl top-full" style="z-index: 1000000;">
                            
                            <div class="p-4 border-b">
                                <div class="font-semibold text-gray-800">{{ auth()->user()->name }}</div>
                                <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                                <div class="inline-block px-2 py-1 mt-2 text-xs font-medium text-purple-700 bg-purple-100 rounded-full">
                                    {{ auth()->user()->role->name ?? 'User' }}
                                </div>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="p-2">
                                @csrf
                                <button type="submit" class="flex items-center w-full gap-2 px-4 py-2 text-sm text-red-600 transition-colors rounded-lg hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TOP BAR WITH TOGGLE (mobile only) -->
            <header class="flex items-center justify-between w-full h-16 px-4 bg-purple-600 md:hidden">
                <div class="flex items-center">
                    <button @click="openSidebar = true" class="text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h1 class="ml-4 text-xl font-semibold text-white">Menu</h1>
                </div>
                
                <!-- Avatar Dropdown Mobile -->
                @if (auth()->check())
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center focus:outline-none">
                            <div class="flex items-center justify-center w-10 h-10 text-sm font-semibold text-purple-700 bg-white rounded-full">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </button>
                        
                        <div x-show="open" 
                             x-cloak
                             @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 z-50 w-48 py-1 mt-2 bg-white rounded-lg shadow-lg">
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
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
            </header>

            <!-- Background Header Desktop -->
            <header class="absolute top-0 left-0 hidden w-full h-40 bg-purple-600 md:block"></header>

            <div class="relative p-6 mt-10 md:mt-0" style="z-index: 2;">
                @yield('content')
            </div>

        </main>

    </div>

</body>

</html>
