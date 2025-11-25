@extends('layouts.app')

@section('content')
<div x-data="salesReportData()">

    {{-- HEADER --}}
    <div class="flex flex-col items-start justify-between gap-4 mt-2 mb-8 md:flex-row md:items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 md:text-white">
                Laporan Penjualan
            </h2>
            <p class="text-sm text-gray-700 opacity-90 md:text-purple-100">
                Analisis penjualan bulanan toko Anda
            </p>
        </div>

        <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
            {{-- Month Filter --}}
            <div class="flex items-center gap-3">
                <label class="text-sm font-semibold text-gray-900 md:text-white">Periode:</label>
                <form method="GET" action="{{ route('sales.report') }}" id="monthFilterForm">
                    <select name="month"
                            onchange="document.getElementById('monthFilterForm').submit()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @foreach($monthOptions as $option)
                            <option value="{{ $option['value'] }}" {{ $selectedMonth == $option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-2">
                <button @click="showExportModal = true" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-purple-700 transition bg-white border border-purple-600 rounded-lg shadow-md hover:bg-purple-50">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Export PDF</span>
                </button>
            </div>
        </div>
    </div>    {{-- STATISTICS CARDS --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">

        {{-- Total Penjualan --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Penjualan</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $salesChange >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }}">
                            @if($salesChange > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($salesChange < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @endif
                            {{ $salesChange > 0 ? '+' : '' }}{{ number_format($salesChange, 1) }}%
                        </span>
                        <span class="text-[10px] text-gray-500">vs bulan lalu</span>
                    </div>
                </div>
                <div class="p-3 text-purple-600 rounded-xl bg-purple-100/70">
                    <i class="fa-solid fa-chart-line fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Transaksi --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Total Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalTransactions) }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $transactionsChange >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }}">
                            @if($transactionsChange > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($transactionsChange < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @endif
                            {{ $transactionsChange > 0 ? '+' : '' }}{{ number_format($transactionsChange, 1) }}%
                        </span>
                        <span class="text-[10px] text-gray-500">vs bulan lalu</span>
                    </div>
                </div>
                <div class="p-3 text-indigo-600 rounded-xl bg-indigo-100/70">
                    <i class="fa-solid fa-receipt fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Produk Terjual --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Produk Terjual</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ number_format($totalProductsSold) }} pcs</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $productsChange >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }}">
                            @if($productsChange > 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @elseif($productsChange < 0)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @endif
                            {{ $productsChange > 0 ? '+' : '' }}{{ number_format($productsChange, 1) }}%
                        </span>
                        <span class="text-[10px] text-gray-500">vs bulan lalu</span>
                    </div>
                </div>
                <div class="p-3 text-teal-600 rounded-xl bg-teal-100/70">
                    <i class="fa-solid fa-boxes-stacked fa-xl"></i>
                </div>
            </div>
        </div>

        {{-- Rata-rata Transaksi --}}
        <div class="p-5 transition bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase">Rata-rata Transaksi</p>
                    <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[11px] text-gray-500">per transaksi</span>
                    </div>
                </div>
                <div class="p-3 text-amber-600 rounded-xl bg-amber-100/70">
                    <i class="fa-solid fa-calculator fa-xl"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- SALES CHART --}}
    <div class="p-6 mb-8 bg-white border border-gray-100 shadow-md rounded-xl">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Grafik Penjualan Harian</h3>
            <p class="text-sm text-gray-500">Penjualan per hari dalam periode terpilih</p>
        </div>
        <div class="relative h-80">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- TWO COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- TOP PRODUCTS --}}
        <div class="p-6 bg-white border border-gray-100 shadow-md rounded-xl">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Semua Produk Terlaris</h3>

            <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                @forelse($topProducts as $index => $item)
                    <div class="flex items-center justify-between p-3 transition border border-gray-100 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-8 h-8 font-bold text-white rounded-full {{ $index < 3 ? 'bg-gradient-to-r from-purple-600 to-pink-600' : 'bg-gray-400' }}">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $item->product->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->product->category->name ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-purple-600">{{ number_format($item->total_quantity) }} pcs</p>
                            <p class="text-xs text-gray-500">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-sm italic text-center text-gray-500">Belum ada data penjualan</p>
                @endforelse
            </div>
        </div>

        {{-- SALES BY CATEGORY --}}
        <div class="p-6 bg-white border border-gray-100 shadow-md rounded-xl">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Penjualan per Kategori</h3>

            <div class="space-y-3">
                @forelse($salesByCategory as $category)
                    @php
                        $categoryName = $category->category_name ?? 'Uncategorized';
                        $percentage = $totalProductsSold > 0 ? ($category->total_quantity / $totalProductsSold) * 100 : 0;
                    @endphp
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="text-purple-600 fa-solid fa-tag"></i>
                                <span class="text-sm font-semibold text-gray-800">{{ $categoryName }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-800">{{ number_format($category->total_quantity) }} pcs</span>
                                <span class="ml-2 text-xs text-gray-500">({{ number_format($percentage, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full h-2 overflow-hidden bg-gray-200 rounded-full">
                            <div class="h-full transition-all bg-gradient-to-r from-purple-600 to-pink-600" style="width: {{ $percentage }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500">Revenue: Rp {{ number_format($category->total_revenue, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="py-8 text-sm italic text-center text-gray-500">Belum ada data kategori</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Export PDF Modal --}}
    <div
        x-show="showExportModal"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
    >
        <div
            x-show="showExportModal"
            x-transition.scale.origin.bottom.duration.300ms
            class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-200"
            @click.away="showExportModal = false"
        >
            <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-600 to-purple-500 text-white relative">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-file-pdf text-white text-lg"></i>
                    </div>
                    <h2 class="text-xl font-semibold">Export Laporan PDF</h2>
                </div>
                <button @click="showExportModal = false" class="absolute right-4 top-4 text-white hover:text-gray-200 transition">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-gray-600 mb-4">Pilih periode bulan yang ingin diekspor:</p>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Bulan</label>
                        <select x-model="exportMonth" class="mt-1 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                            @foreach($monthOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-purple-600 mt-0.5"></i>
                            <p class="text-xs text-purple-800">
                                Laporan akan berisi statistik penjualan, produk terlaris, dan ringkasan harian untuk periode yang dipilih.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                <button @click="showExportModal = false" class="px-4 py-2 bg-white rounded-lg border border-gray-300 text-gray-800 text-sm font-semibold hover:bg-gray-100 transition">
                    Batal
                </button>
                <button @click="exportPdf()" class="px-4 py-2 bg-purple-600 rounded-lg text-white text-sm font-semibold hover:bg-purple-700 transition shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Print Styles --}}
<style>
@media print {
    /* Hide sidebar and navigation */
    aside, header, .no-print {
        display: none !important;
    }

    /* Full width for print */
    main {
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
    }

    /* Adjust card colors for print */
    .bg-gradient-to-r {
        background: #7c3aed !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Keep colors */
    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Page breaks */
    .page-break {
        page-break-before: always;
    }

    /* Adjust chart for print */
    canvas {
        max-height: 400px;
    }
}
</style>

<script>
function salesReportData() {
    return {
        showExportModal: false,
        exportMonth: '{{ $selectedMonth }}',

        init() {
            this.initChart();
        },

        initChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');

            const labels = @json($chartLabels);
            const data = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: data,
                        borderColor: 'rgb(124, 58, 237)',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgb(124, 58, 237)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Tanggal'
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        },

        exportPdf() {
            const url = `{{ route('sales.report.pdf') }}?month=${this.exportMonth}`;
            window.location.href = url;
            this.showExportModal = false;

            Swal.fire({
                title: 'Mengunduh PDF',
                text: 'Laporan sedang diproses...',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
}
</script>
@endsection
