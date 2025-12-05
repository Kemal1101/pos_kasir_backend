<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('Logo & APP.png') }}">
    <title>POSKasir | Sistem POS Modern untuk Bisnis yang Berkembang</title>

    {{-- Tailwind CSS CDN (untuk memastikan CSS terload tanpa proses build) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Inter (Memuat weight 400 hingga 900 untuk tipografi yang kuat) --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    {{-- Font Awesome (Untuk ikon sosial media dan fitur) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Tetap pertahankan font family Inter */
        body {
            font-family: 'Inter', sans-serif;
            /* Tambahkan background ringan pada body */
            background-color: #f9fafb;
        }

        /* Definisi warna custom (Dibuat lebih kontras) */
        .bg-gradient-purple {
            background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%);
        }

        /* New: Gradasi untuk teks utama yang lebih berani */
        .text-gradient-hero {
            background-image: linear-gradient(to right, #7C3AED, #6D28D9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Custom class untuk tombol outline di Hero */
        .btn-outline-custom {
            border: 1px solid #6d28d9;
            color: #6d28d9;
            transition: all 0.2s;
        }

        .btn-outline-custom:hover {
            background-color: #f3e8ff;
            /* Tambahkan efek transform kecil saat hover */
            transform: translateY(-2px);
        }

        /* Custom CSS untuk Sidebar Mobile */
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        /* Menambahkan animasi pulse-slow */
        @keyframes pulse-slow {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="font-sans text-gray-800">

    {{-- 1. Sidebar Menu Mobile (Off-Canvas) --}}
    <div id="mobile-sidebar"
        class="fixed inset-y-0 left-0 z-50 flex flex-col justify-between w-64 p-6 bg-white shadow-xl sidebar lg:hidden">
        <div>
            {{-- Header Sidebar --}}
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <div class="flex items-center space-x-2">
                    <div class="flex items-center justify-center w-8 h-8 font-bold text-white rounded-lg bg-violet-600">
                        P
                    </div>
                    <span class="text-xl font-bold text-gray-900">POSKasir</span>
                </div>
                <button id="close-sidebar" class="text-2xl text-gray-800" aria-label="Close Menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Navigasi Sidebar (Disesuaikan dengan section ID) --}}
            <ul class="flex flex-col mt-6 space-y-3">
                <li><a href="#fitur"
                        class="block py-2 text-lg font-medium text-gray-700 transition hover:text-violet-600">Fitur</a>
                </li>
                <li><a href="/login"
                        class="block py-2 text-lg font-medium text-gray-700 transition hover:text-violet-600">Login</a>
                </li>
            </ul>
        </div>

        {{-- CTA Sidebar --}}
        <div class="pb-4">
            <a href="/login"
                class="block w-full px-4 py-3 font-semibold text-center text-white transition rounded-lg bg-violet-600 hover:bg-violet-700">
                Mulai Sekarang
            </a>
        </div>
    </div>

    {{-- Overlay untuk Mobile --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black bg-opacity-50 lg:hidden"></div>

    {{-- Navigation Header (Disesuaikan dengan section ID dan CTA) --}}
    <header class="sticky top-0 z-10 bg-white shadow-lg">
        <nav class="container flex items-center justify-between px-4 py-4 mx-auto lg:px-20">
            <div class="flex items-center space-x-2 group">
                <div
                    class="flex items-center justify-center w-8 h-8 font-bold text-white transition duration-300 rounded-lg bg-violet-600 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-violet-400/50">
                    P
                </div>
                <span class="text-xl font-bold text-gray-900">POSKasir</span>
            </div>

            {{-- Tautan Navigasi (Desktop) --}}
            <ul class="items-center hidden space-x-6 lg:flex">
                <li><a href="#fitur" class="text-gray-600 transition hover:text-violet-600">Fitur</a></li>
                {{-- Mengganti tautan Masuk menjadi tombol biasa atau link untuk konsistensi --}}
                <li><a href="{{ route('login.form') }}" class="text-gray-600 transition hover:text-violet-600">Login</a></li>
                <li>
                    {{-- Tombol utama CTA --}}
                    <a href="/login"
                        class="px-5 py-2 font-semibold text-white transition duration-300 rounded-full shadow-lg bg-violet-600 hover:bg-violet-700 hover:shadow-violet-600/50">
                        Mulai
                    </a>
                </li>
            </ul>

            {{-- Mobile Menu Button (Tampilkan di Mobile) --}}
            <button id="open-sidebar" class="text-xl text-gray-800 lg:hidden" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>

    <main>
        {{-- Hero Section (Diubah: Teks Kiri, Gambar Kanan di desktop) --}}
        <section id="hero" class="pt-20 pb-16 bg-white">
            {{-- Menggunakan lg:px-20 untuk jarak tepi yang luas di desktop --}}
            <div class="container flex flex-col items-center justify-between px-4 mx-auto lg:flex-row lg:px-20">

                {{-- Konten Kiri (Teks) --}}
                <div class="mb-10 text-center lg:w-1/2 lg:mb-0 lg:text-left lg:pr-12">
                    {{-- Tagline dengan ring & shadow --}}
                    <span
                        class="inline-block px-3 py-1 mb-4 text-sm font-semibold rounded-full shadow-md ring-1 ring-violet-300 bg-violet-50 text-violet-700">
                        Sistem POS Terpercaya #1
                    </span>
                    {{-- Judul dengan gradasi yang lebih kuat --}}
                    <h1 class="mb-6 text-4xl font-black leading-tight md:text-5xl lg:text-6xl">
                        Kelola Bisnis Anda Lebih <br><span class="text-gradient-hero">Mudah & Efisien</span>
                    </h1>
                    {{-- Deskripsi dipertebal --}}
                    <p class="max-w-lg mx-auto mb-8 text-lg font-medium text-gray-600 lg:mx-0 md:text-xl">
                        Sistem Point of Sale modern yang membantu ribuan bisnis mengelola penjualan, inventori, dan
                        laporan dengan lebih cepat dan akurat.
                    </p>
                    {{-- Tombol (Menambahkan Animasi Pulse lambat pada tombol utama) --}}
                    <div
                        class="flex flex-col items-center justify-center space-y-4 lg:flex-row lg:space-x-4 lg:space-y-0 lg:justify-start">
                        {{-- Tombol utama dengan shadow yang kuat dan animasi pulse --}}
                        <a href="/login"
                            class="w-full px-8 py-3 font-bold text-white transition duration-300 rounded-xl lg:w-auto bg-violet-600 shadow-xl shadow-violet-500/50 hover:bg-violet-700 hover:scale-[1.03] animate-pulse-slow">
                            Mulai Sekarang <i class="ml-2 fas fa-arrow-right"></i>
                        </a>
                        <a href="#lihat-demo"
                            class="w-full px-8 py-3 font-semibold transition lg:w-auto btn-outline-custom rounded-xl hover:bg-violet-50">
                            Lihat Demo
                        </a>
                    </div>
                </div>

                {{-- Konten Kanan (Gambar) --}}
                <div class="w-full mb-10 lg:w-1/2 lg:mb-0 lg:pl-12">
                    <div class="w-full max-w-lg mx-auto lg:max-w-md">
                        {{-- Gambar dengan bayangan yang lebih nyata --}}
                        <div class="overflow-hidden shadow-[0_20px_50px_rgba(109,40,217,0.3)] rounded-2xl">
                            <img src="{{ asset('images/hero-pos.jpg') }}" alt="POS System Hardware"
                                class="object-cover w-full h-auto">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <hr class="my-10">

        {{-- Fitur Unggulan Section (Animasi pada Cards) --}}
        <section id="fitur" class="py-20 bg-gray-50">
            <div class="container px-4 mx-auto lg:px-20">
                {{-- Header --}}
                <div class="mb-12 text-center">
                    <span
                        class="inline-block px-4 py-1 mb-3 text-sm font-semibold rounded-full bg-violet-100 text-violet-600">
                        Fitur Unggulan
                    </span>
                    {{-- Judul dengan gradasi --}}
                    <h2 class="mb-4 text-3xl font-extrabold md:text-4xl lg:text-5xl">
                        Semua yang Anda Butuhkan dalam <span class="text-gradient-hero">Satu Platform</span>
                    </h2>
                    <p class="text-lg text-gray-600">Fitur lengkap untuk membantu bisnis Anda berkembang lebih pesat</p>
                </div>

                {{-- Grid Fitur (Efek hover lebih dramatis) --}}
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

                    {{-- Card Penjualan Cepat (Modifikasi Transisi & Hover) --}}
                    <div
                        class="p-8 transition duration-300 bg-white shadow-lg border-t-4 border-violet-600 rounded-xl hover:shadow-2xl hover:scale-[1.03] hover:ring-2 hover:ring-violet-600">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-4 text-2xl text-pink-500 bg-pink-100 rounded-full shadow-md">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Penjualan Cepat</h3>
                        <p class="text-gray-600">Interface intuitif untuk transaksi cepat dengan sistem shortcut
                            keyboard dan barcode scanner</p>
                    </div>

                    {{-- Card Manajemen Inventori (Modifikasi Transisi & Hover) --}}
                    <div
                        class="p-8 transition duration-300 bg-white shadow-lg border-t-4 border-teal-600 rounded-xl hover:shadow-2xl hover:scale-[1.03] hover:ring-2 hover:ring-teal-600">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-4 text-2xl text-teal-500 bg-teal-100 rounded-full shadow-md">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Manajemen Inventori</h3>
                        <p class="text-gray-600">Kelola stok produk secara real-time dengan notifikasi stok rendah
                            otomatis</p>
                    </div>

                    {{-- Card Laporan & Analytics (Modifikasi Transisi & Hover) --}}
                    <div
                        class="p-8 transition duration-300 bg-white shadow-lg border-t-4 border-blue-600 rounded-xl hover:shadow-2xl hover:scale-[1.03] hover:ring-2 hover:ring-blue-600">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-4 text-2xl text-blue-500 bg-blue-100 rounded-full shadow-md">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Laporan & Analytics</h3>
                        <p class="text-gray-600">Dashboard komprehensif dengan analisis penjualan, profit, dan tren
                            bisnis</p>
                    </div>

                    {{-- Card Manajemen Karyawan --}}
                    <div
                        class="p-8 transition duration-300 bg-white shadow-lg border-t-4 border-amber-600 rounded-xl hover:shadow-2xl hover:scale-[1.03] hover:ring-2 hover:ring-amber-600">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-4 text-2xl rounded-full shadow-md bg-amber-100 text-amber-500">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Manajemen Karyawan</h3>
                        <p class="text-gray-600">Atur akses, shift kerja, dan monitor kinerja karyawan dengan mudah</p>
                    </div>

                    {{-- Card Multi Payment --}}
                    <div
                        class="p-8 transition duration-300 bg-white shadow-lg border-t-4 border-pink-600 rounded-xl hover:shadow-2xl hover:scale-[1.03] hover:ring-2 hover:ring-pink-600">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-4 text-2xl text-pink-500 bg-pink-100 rounded-full shadow-md">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Multi Payment</h3>
                        <p class="text-gray-600">Terima berbagai metode pembayaran: cash, kartu, e-wallet, dan QRIS</p>
                    </div>

                    {{-- Card Akses 24/7 --}}
                    <div
                        class="p-8 transition duration-300 bg-white shadow-lg border-t-4 border-cyan-600 rounded-xl hover:shadow-2xl hover:scale-[1.03] hover:ring-2 hover:ring-cyan-600">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-4 text-2xl rounded-full shadow-md bg-cyan-100 text-cyan-500">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <h3 class="mb-2 text-xl font-bold">Akses 24/7</h3>
                        <p class="text-gray-600">Cloud-based system yang bisa diakses kapan saja dari mana saja</p>
                    </div>
                </div>
            </div>
        </section>

        <hr class="my-10">

        {{-- Call to Action / Registration Section (Menambahkan animasi subtle pada container CTA) --}}
        <section id="cta" class="py-16">
            <div class="container px-4 mx-auto lg:px-20">
                <div
                    class="bg-gradient-purple p-10 md:p-16 text-white rounded-3xl text-center shadow-[0_20px_50px_rgba(124,58,237,0.7)] transition duration-500 hover:shadow-[0_25px_60px_rgba(124,58,237,0.9)]">

                    {{-- Ukuran Judul disesuaikan --}}
                    <h2 class="mb-4 text-3xl font-extrabold md:text-4xl lg:text-5xl">Siap Mengubah Cara Anda Berbisnis?
                    </h2>
                    <p class="max-w-3xl mx-auto mb-10 text-lg font-medium opacity-95 md:text-xl">POSKasir adalah sistem
                        Point of Sale berbasis Website untuk Manajemen admin dan Mobile untuk kasir, dirancang untuk
                        mengintegrasikan penjualan, stok
                        real-time, dan laporan keuangan di satu dashboard yang akurat. Raih efisiensi maksimal dan
                        kontrol penuh atas bisnis Anda.</p>

                    {{-- Tombol menggunakan flex-col di mobile --}}
                    <div
                        class="flex flex-col items-center justify-center mb-10 space-y-4 md:flex-row md:space-x-6 md:space-y-0">
                        <a href="/login"
                            class="w-full px-10 py-4 text-lg font-bold transition duration-300 rounded-xl md:w-auto bg-white shadow-xl shadow-gray-900/10 text-violet-600 hover:bg-gray-100 hover:scale-[1.03] animate-pulse">
                            Mulai Gratis Sekarang <i class="ml-2 fas fa-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Benefit (Menggunakan flex-col dan space-y di mobile) --}}
                    <div
                        class="flex flex-col justify-center pt-4 mt-6 space-y-2 text-sm font-semibold border-t md:flex-row md:space-x-8 md:space-y-0 md:text-lg border-white/30">
                        <p class="flex items-center justify-center"><i
                                class="mr-2 fas fa-check-circle text-lime-300"></i> Mudah Digunakan</p>
                        <p class="flex items-center justify-center"><i
                                class="mr-2 fas fa-check-circle text-lime-300"></i> Fitur Lengkap</p>
                        <p class="flex items-center justify-center"><i
                                class="mr-2 fas fa-check-circle text-lime-300"></i> POS Sistem Mobile</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <hr class="my-10">

    {{-- Footer (Padding disesuaikan) --}}
    <footer class="pt-16 pb-8 bg-gray-100">
        <div class="container px-4 mx-auto lg:px-20">
            {{-- Mengubah grid menjadi default 2 kolom, md 4 kolom --}}
            <div class="grid grid-cols-2 gap-10 pb-12 border-b border-gray-300 md:grid-cols-4">

                {{-- Kolom Kiri (POSKasir Info) --}}
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center mb-4 space-x-2">
                        <div
                            class="flex items-center justify-center w-10 h-10 text-xl font-bold text-white rounded-lg bg-violet-600">
                            P</div>
                        <span class="text-2xl font-bold text-gray-900">POSKasir</span>
                    </div>
                    <p class="mb-6 text-gray-600">Sistem POS modern untuk bisnis yang berkembang.</p>
                    <div class="flex space-x-4">
                        {{-- Social Media Icons --}}
                        <a href="#" class="text-gray-400 transition hover:text-violet-600 hover:scale-125"><i
                                class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 transition hover:text-violet-600 hover:scale-125"><i
                                class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 transition hover:text-violet-600 hover:scale-125"><i
                                class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 transition hover:text-violet-600 hover:scale-125"><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                {{-- Kolom Tautan (Produk) --}}
                <div>
                    <h4 class="mb-4 text-lg font-bold">Produk</h4>
                    <ul class="space-y-2">
                        <li><a href="#fitur" class="text-gray-600 transition hover:text-violet-600">Fitur</a></li>
                        <li><a href="#harga" class="text-gray-600 transition hover:text-violet-600">Harga</a></li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Demo</a></li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Updates</a></li>
                    </ul>
                </div>

                {{-- Kolom Tautan (Perusahaan) --}}
                <div>
                    <h4 class="mb-4 text-lg font-bold">Perusahaan</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Tentang Kami</a>
                        </li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Blog</a></li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Karir</a></li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Kontak</a></li>
                    </ul>
                </div>

                {{-- Kolom Tautan (Support) --}}
                <div>
                    <h4 class="mb-4 text-lg font-bold">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Help Center</a>
                        </li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Dokumentasi</a>
                        </li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">API</a></li>
                        <li><a href="#" class="text-gray-600 transition hover:text-violet-600">Status</a></li>
                    </ul>
                </div>
            </div>

            {{-- Bagian Bawah Footer (Menggunakan flex-col di mobile) --}}
            <div
                class="flex flex-col items-center pt-8 space-y-4 text-sm text-gray-500 md:flex-row md:justify-between md:space-y-0">
                <p>© 2025 POSKasir. All rights reserved.</p>
                <div class="flex space-x-4">
                    <a href="#" class="transition hover:text-violet-600">Privacy Policy</a>
                    <a href="#" class="transition hover:text-violet-600">Terms of Service</a>
                    <a href="#" class="transition hover:text-violet-600">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('mobile-sidebar');
            const openBtn = document.getElementById('open-sidebar');
            const closeBtn = document.getElementById('close-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            // Fungsi untuk membuka sidebar
            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Mencegah scrolling pada body
            }

            // Fungsi untuk menutup sidebar
            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.add('hidden');
                document.body.style.overflow = ''; // Mengizinkan scrolling kembali
            }

            // Event Listeners
            openBtn.addEventListener('click', openSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            // Menutup sidebar ketika link navigasi diklik (jika navigasi adalah anchor)
            const navLinks = sidebar.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', closeSidebar);
            });
        });
    </script>
</body>

</html>
