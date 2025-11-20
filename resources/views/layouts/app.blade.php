<!DOCTYPE html>
<html lang="id" x-data="{ openSidebar: false }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Toko Saya</title>

    <!-- CSS FONT AWESOME DI SINI -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-sidebar { background: linear-gradient(180deg, #7C3AED 0%, #6D28D9 100%); }
    </style>
</head>

<body class="bg-gray-50">

    <div class="flex h-screen overflow-hidden">

        <!-- BACKDROP (mobile only) -->
        <div 
            class="fixed inset-0 bg-black/40 z-20 md:hidden"
            x-show="openSidebar"
            x-transition.opacity
            @click="openSidebar = false">
        </div>

        <!-- SIDEBAR -->
        <aside 
            class="w-64 bg-sidebar text-white flex flex-col fixed inset-y-0 left-0 z-30 transition-transform duration-300 transform md:translate-x-0 md:relative"
            :class="openSidebar ? 'translate-x-0' : '-translate-x-full'">

            <!-- Branding -->
            <div class="p-6 flex items-center gap-3">
                <div class="bg-white p-2 rounded-lg">
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
            <nav class="flex-1 px-4 space-y-2 mt-4">

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

                <a href="#" class="text-purple-100 hover:bg-purple-600 flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span class="font-medium">Produk</span>
                </a>

                <a href="{{ route('users.manage') }}"
                class="{{ request()->is('users.manage') ? 'bg-white text-purple-700' : 'text-purple-100 hover:bg-purple-600' }} flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="font-medium">User</span>
                </a>

                <a href="#" class="text-purple-100 hover:bg-purple-600 flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <span class="font-medium">Laporan</span>
                </a>

                <a href="#" class="text-purple-100 hover:bg-purple-600 flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">Pengaturan</span>
                </a>

            </nav>

            <div class="p-4 mt-auto border-t border-purple-500">
                <button class="w-full flex items-center justify-center gap-2 bg-white text-purple-700 py-2 rounded hover:bg-gray-100 font-medium text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Keluar
                </button>
            </div>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto bg-gray-50 h-full relative">

            <!-- TOP BAR WITH TOGGLE (mobile only) -->
            <header class="bg-purple-600 h-16 w-full flex items-center px-4 md:hidden">
                <button @click="openSidebar = true" class="text-white">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-white text-xl font-semibold ml-4">Menu</h1>
            </header>

            <!-- Background Header -->
            <header class="bg-purple-600 h-40 absolute top-0 left-0 w-full z-0 hidden md:block"></header>

            <div class="relative z-10 p-6 mt-10 md:mt-0">
                @yield('content')
            </div>

        </main>

    </div>

</body>
</html>
