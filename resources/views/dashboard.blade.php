@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center text-white mb-8 mt-2">
        <div>
            <h2 class="text-2xl font-bold">Selamat Datang, Owner!</h2>
            <p class="text-purple-100 text-sm opacity-90">Berikut adalah ringkasan toko Anda hari ini</p>
        </div>
        </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden">
            <div class="flex justify-between items-start z-10 relative">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Penjualan Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-800">Rp 4.250.000</h3>
                    <span class="inline-flex items-center gap-1 text-green-500 text-xs font-bold mt-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        +12.5%
                    </span>
                </div>
                <div class="p-3 bg-pink-100 rounded-lg">
                    <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Transaksi Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-800">45</h3>
                    <span class="inline-flex items-center gap-1 text-green-500 text-xs font-bold mt-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        +8.2%
                    </span>
                </div>
                <div class="p-3 bg-pink-100 rounded-lg">
                    <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Produk Terjual Bulan Ini</p>
                    <h3 class="text-xl font-bold text-gray-800">127</h3>
                    <span class="inline-flex items-center gap-1 text-green-500 text-xs font-bold mt-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        +15.3%
                    </span>
                </div>
                <div class="p-3 bg-pink-100 rounded-lg">
                    <svg class="w-6 h-6 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Transaksi Terbaru</h3>

            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg transition">
                    <div>
                        <p class="font-bold text-gray-700 text-sm">TRX001</p>
                        <p class="text-xs text-gray-500">14:30 - 5 Item</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800 text-sm">Rp 150.000</p>
                        <p class="text-xs text-gray-500">Tunai</p>
                    </div>
                </div>

                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div>
                        <p class="font-bold text-gray-700 text-sm">TRX002</p>
                        <p class="text-xs text-gray-500">14:15 - 3 Item</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800 text-sm">Rp 85.000</p>
                        <p class="text-xs text-gray-500">QRIS</p>
                    </div>
                </div>

                <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg transition">
                    <div>
                        <p class="font-bold text-gray-700 text-sm">TRX003</p>
                        <p class="text-xs text-gray-500">14:00 - 8 Item</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800 text-sm">Rp 320.000</p>
                        <p class="text-xs text-gray-500">Transfer</p>
                    </div>
                </div>

                 <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg transition">
                    <div>
                        <p class="font-bold text-gray-700 text-sm">TRX004</p>
                        <p class="text-xs text-gray-500">13:45 - 2 Item</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800 text-sm">Rp 45.000</p>
                        <p class="text-xs text-gray-500">Tunai</p>
                    </div>
                </div>

                 <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div>
                        <p class="font-bold text-gray-700 text-sm">TRX005</p>
                        <p class="text-xs text-gray-500">13:30 - 4 Item</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800 text-sm">Rp 110.000</p>
                        <p class="text-xs text-gray-500">E-Wallet</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Stok Menipis</h3>

            <div class="space-y-3">
                <div class="bg-yellow-50 border border-yellow-100 p-3 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-gray-800">Kopi Arabika 250g</p>
                            <p class="text-xs text-gray-500">Minuman</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-orange-600">Sisa: 5</p>
                            <p class="text-[10px] text-gray-400">Min: 10</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-100 p-3 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-gray-800">Susu UHT 1L</p>
                            <p class="text-xs text-gray-500">Minuman</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-orange-600">Sisa: 8</p>
                            <p class="text-[10px] text-gray-400">Min: 15</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-100 p-3 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-gray-800">Roti Tawar Gandum</p>
                            <p class="text-xs text-gray-500">Makanan</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-orange-600">Sisa: 3</p>
                            <p class="text-[10px] text-gray-400">Min: 10</p>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-100 p-3 rounded-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-bold text-gray-800">Mie Instan Goreng</p>
                            <p class="text-xs text-gray-500">Makanan</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-orange-600">Sisa: 9</p>
                            <p class="text-[10px] text-gray-400">Min: 20</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
