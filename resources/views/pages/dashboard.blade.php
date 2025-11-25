@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center text-white mb-8 mt-2">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">Selamat Datang, Owner!</h2>
            <p class="text-sm opacity-90 text-gray-700 md:text-purple-100">Berikut adalah ringkasan toko Anda hari ini</p>
        </div>
        </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Penjualan Hari Ini -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] tracking-wide text-gray-500 font-semibold uppercase">Penjualan Hari Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 leading-tight">Rp {{ number_format($totalSalesToday ?? 0, 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $salesTodayChangeClass }} bg-gray-50">
                            @php $pct = $percentSalesToday ?? 0; @endphp
                            @if($pct > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($pct < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/></svg>
                            @endif
                            {{ $pct > 0 ? '+' : '' }}{{ number_format($pct, 1) }}%
                        </span>
                    </div>
                </div>
                <div class="shrink-0 p-3 rounded-lg bg-gradient-to-tr from-pink-100 to-pink-200">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] tracking-wide text-gray-500 font-semibold uppercase">Transaksi Hari Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 leading-tight">{{ $transactionCountToday ?? 0 }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $transactionsTodayChangeClass }} bg-gray-50">
                            @php $pct = $percentTransactionsTodayVsLastMonth ?? 0; @endphp
                            @if($pct > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($pct < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/></svg>
                            @endif
                            {{ $pct > 0 ? '+' : '' }}{{ number_format($pct, 1) }}%
                        </span>
                    </div>
                </div>
                <div class="shrink-0 p-3 rounded-lg bg-gradient-to-tr from-indigo-100 to-indigo-200">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Produk Terjual Bulan Ini -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] tracking-wide text-gray-500 font-semibold uppercase">Produk Terjual Bulan Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 leading-tight">{{ $productsSoldThisMonth ?? 0 }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $productsSoldMonthChangeClass }} bg-gray-50">
                            @php $pct = $percentProductsSoldMonthVsLastMonth ?? 0; @endphp
                            @if($pct > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($pct < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/></svg>
                            @endif
                            {{ $pct > 0 ? '+' : '' }}{{ number_format($pct, 1) }}%
                        </span>
                    </div>
                </div>
                <div class="shrink-0 p-3 rounded-lg bg-gradient-to-tr from-teal-100 to-teal-200">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>

        <!-- Penjualan Bulan Ini -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-[11px] tracking-wide text-gray-500 font-semibold uppercase">Penjualan Bulan Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 leading-tight">Rp {{ number_format($salesThisMonthTotal ?? 0, 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $salesMonthChangeClass }} bg-gray-50">
                            @php $pct = $percentSalesThisMonthVsLastMonth ?? 0; @endphp
                            @if($pct > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($pct < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-width="2"/></svg>
                            @endif
                            {{ $pct > 0 ? '+' : '' }}{{ number_format($pct, 1) }}%
                        </span>
                    </div>
                </div>
                <div class="shrink-0 p-3 rounded-lg bg-gradient-to-tr from-amber-100 to-amber-200">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Transaksi Terbaru</h3>

            <div class="space-y-4">
                @forelse(($recentTransactions ?? []) as $trx)
                    <div class="flex justify-between items-center p-3 {{ $loop->odd ? 'hover:bg-gray-50' : 'bg-gray-50 border border-gray-100' }} rounded-lg transition">
                        <div>
                            <p class="font-bold text-gray-700 text-sm">{{ $trx['code'] }}</p>
                            <p class="text-xs text-gray-500">{{ $trx['time'] }} - {{ $trx['items'] }} Item</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800 text-sm">Rp {{ number_format($trx['amount'] ?? 0, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500">{{ $trx['method'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Stok Menipis</h3>

            <div class="space-y-3">
                @forelse(($lowStockProducts ?? []) as $p)
                    <div class="bg-yellow-50 border border-yellow-100 p-3 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ data_get($p, 'name', '-') }}</p>
                                <p class="text-xs text-gray-500">{{ data_get($p, 'category.name', '-') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-orange-600">Sisa: {{ data_get($p, 'stock', 0) }}</p>
                                <p class="text-[10px] text-gray-400">Min: {{ $lowStockThreshold ?? 10 }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada produk menipis.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
