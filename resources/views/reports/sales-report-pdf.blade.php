<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - {{ $periodLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #7c3aed;
        }

        .header h1 {
            font-size: 24px;
            color: #7c3aed;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }

        .header p {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .stat-card .change {
            font-size: 8px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .change.positive {
            background: #d1fae5;
            color: #065f46;
        }

        .change.negative {
            background: #fee2e2;
            color: #991b1b;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th {
            background: #7c3aed;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }

        table tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .badge-gold {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-silver {
            background: #f3f4f6;
            color: #4b5563;
        }

        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #999;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <h2>Toko Saya</h2>
        <p>Periode: {{ $periodLabel }}</p>
        <p style="margin-top: 10px;">Dicetak pada: {{ now()->locale('id')->translatedFormat('d F Y H:i') }}</p>
    </div>

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Penjualan</h3>
            <div class="value">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
            <span class="change {{ $salesChange >= 0 ? 'positive' : 'negative' }}">
                {{ $salesChange > 0 ? '+' : '' }}{{ number_format($salesChange, 1) }}%
            </span>
        </div>
        <div class="stat-card">
            <h3>Total Modal (HPP)</h3>
            <div class="value">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
            <span style="font-size: 8px; color: #666;">Harga Pokok Penjualan</span>
        </div>
        <div class="stat-card" style="background: #d1fae5;">
            <h3 style="color: #065f46;">🎯 Keuntungan</h3>
            <div class="value" style="color: #065f46;">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
            <span class="change {{ $profitChange >= 0 ? 'positive' : 'negative' }}">
                {{ $profitChange > 0 ? '+' : '' }}{{ number_format($profitChange, 1) }}%
            </span>
        </div>
        <div class="stat-card">
            <h3>Margin Keuntungan</h3>
            <div class="value" style="color: {{ $profitMargin >= 20 ? '#065f46' : ($profitMargin >= 10 ? '#92400e' : '#991b1b') }};">{{ number_format($profitMargin, 1) }}%</div>
            <span style="font-size: 8px; color: #666;">
                @if($profitMargin >= 20) Sangat Baik
                @elseif($profitMargin >= 10) Cukup Baik
                @else Perlu Evaluasi
                @endif
            </span>
        </div>
    </div>

    {{-- Additional Stats Row --}}
    <div class="stats-grid" style="margin-top: -15px; margin-bottom: 25px;">
        <div class="stat-card">
            <h3>Total Transaksi</h3>
            <div class="value">{{ number_format($totalTransactions) }}</div>
            <span class="change {{ $transactionsChange >= 0 ? 'positive' : 'negative' }}">
                {{ $transactionsChange > 0 ? '+' : '' }}{{ number_format($transactionsChange, 1) }}%
            </span>
        </div>
        <div class="stat-card">
            <h3>Produk Terjual</h3>
            <div class="value">{{ number_format($totalProductsSold) }} pcs</div>
            <span class="change {{ $productsChange >= 0 ? 'positive' : 'negative' }}">
                {{ $productsChange > 0 ? '+' : '' }}{{ number_format($productsChange, 1) }}%
            </span>
        </div>
        <div class="stat-card">
            <h3>Rata-rata Transaksi</h3>
            <div class="value">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h3>Produk Tidak Laku</h3>
            <div class="value" style="color: {{ $notSellingProducts->count() > 0 ? '#991b1b' : '#065f46' }};">{{ $notSellingProducts->count() }}</div>
            <span style="font-size: 8px; color: #666;">dalam periode ini</span>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="section">
        <div class="section-title">Produk Terlaris</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">Rank</th>
                    <th style="width: 35%;">Nama Produk</th>
                    <th style="width: 20%;">Kategori</th>
                    <th style="width: 15%;" class="text-right">Qty Terjual</th>
                    <th style="width: 25%;" class="text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProducts as $index => $item)
                    <tr>
                        <td class="text-center">
                            @if($index < 3)
                                <span class="badge {{ $index === 0 ? 'badge-gold' : ($index === 1 ? 'badge-silver' : 'badge-purple') }}">
                                    #{{ $index + 1 }}
                                </span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->product->category->name ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->total_quantity) }} pcs</td>
                        <td class="text-right">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data penjualan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Sales by Category --}}
    <div class="section">
        <div class="section-title">Penjualan per Kategori</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Kategori</th>
                    <th style="width: 20%;" class="text-right">Qty Terjual</th>
                    <th style="width: 15%;" class="text-right">Persentase</th>
                    <th style="width: 25%;" class="text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesByCategory as $category)
                    @php
                        $percentage = $totalProductsSold > 0 ? ($category->total_quantity / $totalProductsSold) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $category->category_name ?? 'Uncategorized' }}</td>
                        <td class="text-right">{{ number_format($category->total_quantity) }} pcs</td>
                        <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                        <td class="text-right">Rp {{ number_format($category->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data kategori</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Top Profit Products --}}
    <div class="section">
        <div class="section-title" style="color: #065f46; border-color: #10b981;">🎯 Produk dengan Profit Tertinggi</div>
        <table>
            <thead>
                <tr style="background: #10b981;">
                    <th style="width: 5%;">Rank</th>
                    <th style="width: 25%;">Nama Produk</th>
                    <th style="width: 15%;">Kategori</th>
                    <th style="width: 10%;" class="text-right">Qty</th>
                    <th style="width: 15%;" class="text-right">Revenue</th>
                    <th style="width: 15%;" class="text-right">Modal</th>
                    <th style="width: 15%;" class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProfitProducts as $index => $item)
                    <tr>
                        <td class="text-center">
                            @if($index < 3)
                                <span class="badge" style="background: {{ $index === 0 ? '#d1fae5' : '#f0fdf4' }}; color: #065f46;">
                                    #{{ $index + 1 }}
                                </span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->category_name ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->total_quantity) }}</td>
                        <td class="text-right">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: #065f46; font-weight: bold;">Rp {{ number_format($item->total_profit, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data profit</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Not Selling Products --}}
    @if($notSellingProducts->count() > 0)
    <div class="section">
        <div class="section-title" style="color: #991b1b; border-color: #ef4444;">⚠️ Produk Tidak Laku ({{ $notSellingProducts->count() }} produk)</div>
        <table>
            <thead>
                <tr style="background: #ef4444;">
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Nama Produk</th>
                    <th style="width: 25%;">Kategori</th>
                    <th style="width: 15%;" class="text-right">Stok</th>
                    <th style="width: 20%;" class="text-right">Harga Jual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notSellingProducts->take(15) as $index => $product)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? '-' }}</td>
                        <td class="text-right">{{ number_format($product->stock) }} pcs</td>
                        <td class="text-right">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if($notSellingProducts->count() > 15)
                    <tr>
                        <td colspan="5" class="text-center" style="font-style: italic; color: #666;">
                            ... dan {{ $notSellingProducts->count() - 15 }} produk lainnya
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div style="background: #fef3c7; padding: 8px; border-radius: 4px; margin-top: 10px;">
            <p style="font-size: 9px; color: #92400e;">
                <strong>💡 Rekomendasi:</strong> Pertimbangkan untuk memberikan promo atau diskon pada produk-produk ini untuk meningkatkan penjualan.
            </p>
        </div>
    </div>
    @endif

    {{-- Daily Sales Summary --}}
    <div class="section" style="page-break-before: always;">
        <div class="section-title">Ringkasan Penjualan Harian</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Tanggal</th>
                    <th style="width: 20%;" class="text-center">Jumlah Transaksi</th>
                    <th style="width: 50%;" class="text-right">Total Penjualan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailySales as $day)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day->date)->locale('id')->translatedFormat('d F Y') }}</td>
                        <td class="text-center">{{ number_format($day->transaction_count) }}</td>
                        <td class="text-right">Rp {{ number_format($day->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Belum ada data penjualan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem POS Toko Saya</p>
        <p>&copy; {{ date('Y') }} Toko Saya. All rights reserved.</p>
    </div>
</body>
</html>
