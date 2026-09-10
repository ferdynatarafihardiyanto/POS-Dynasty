<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mutasi Stok - Dynasty</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #fff;
        }
        .page-header {
            background: linear-gradient(135deg, #8b211e 0%, #6b1915 100%);
            color: white;
            padding: 20px 30px;
            margin-bottom: 20px;
        }
        .brand { font-size: 22px; font-weight: 700; letter-spacing: 1px; }
        .report-title { font-size: 14px; opacity: 0.85; margin-top: 4px; }
        .meta { font-size: 10px; opacity: 0.7; margin-top: 2px; }
        .filters {
            margin: 0 30px 16px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .filter-badge {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 10px;
            color: #64748b;
        }
        .filter-badge strong { color: #1a1a2e; }
        .summary-cards {
            display: flex;
            gap: 12px;
            margin: 0 30px 16px;
        }
        .card {
            flex: 1;
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
        }
        .card-masuk { background: #f0fdf4; border-color: #bbf7d0; }
        .card-keluar { background: #fff1f2; border-color: #fecdd3; }
        .card-penyesuaian { background: #fffbeb; border-color: #fde68a; }
        .card-total { background: #f0f4ff; border-color: #c7d2fe; }
        .card-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 4px; }
        .card-value { font-size: 18px; font-weight: 700; }
        .card-masuk .card-value { color: #16a34a; }
        .card-keluar .card-value { color: #dc2626; }
        .card-penyesuaian .card-value { color: #d97706; }
        .card-total .card-value { color: #4f46e5; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 20px;
        }
        thead tr {
            background: #8b211e;
            color: white;
        }
        thead th {
            padding: 9px 10px;
            text-align: left;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:hover { background: #f1f5f9; }
        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-masuk { background: #dcfce7; color: #16a34a; }
        .badge-keluar { background: #fee2e2; color: #dc2626; }
        .badge-penyesuaian { background: #fef9c3; color: #854d0e; }
        .qty-masuk { color: #16a34a; font-weight: 700; }
        .qty-keluar { color: #dc2626; font-weight: 700; }
        .qty-penyesuaian { color: #d97706; font-weight: 700; }
        .text-muted { color: #9ca3af; font-size: 9.5px; }
        .footer {
            text-align: center;
            padding: 16px 30px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 9px;
        }
        .table-wrapper { padding: 0 30px; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        .print-btn-area {
            padding: 12px 30px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 7px 18px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #8b211e; color: white; }
        .btn-back { background: #e5e7eb; color: #374151; text-decoration: none; }
    </style>
</head>
<body>

<div class="print-btn-area no-print">
    <button class="btn btn-print" onclick="window.print()">🖨️ Print / Simpan PDF</button>
    <a href="{{ route('admin.stok.index', array_filter(['tipe' => $filters['tipe'], 'tanggal' => $filters['tanggal'], 'search' => $filters['search']])) }}" class="btn btn-back">← Kembali</a>
</div>

<div class="page-header">
    <div class="brand">☕ Dynasty</div>
    <div class="report-title">Laporan Mutasi Stok</div>
    <div class="meta">Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total Data: {{ $riwayats->count() }} entri</div>
</div>

@php
    $activeFilters = array_filter(['Tipe' => $filters['tipe'], 'Tanggal' => $filters['tanggal'], 'Pencarian' => $filters['search']]);
    $totalMasuk = $riwayats->where('jenis','masuk')->count();
    $totalKeluar = $riwayats->where('jenis','keluar')->count();
    $totalPenyesuaian = $riwayats->where('jenis','penyesuaian')->count();
@endphp

@if($activeFilters)
<div class="filters">
    <span style="font-size:10px; color:#6b7280; font-weight:600;">Filter aktif:</span>
    @foreach($activeFilters as $key => $val)
        <span class="filter-badge"><strong>{{ $key }}:</strong> {{ ucfirst($val) }}</span>
    @endforeach
</div>
@endif

<div class="summary-cards">
    <div class="card card-masuk">
        <div class="card-label">Stok Masuk</div>
        <div class="card-value">{{ $totalMasuk }}</div>
    </div>
    <div class="card card-keluar">
        <div class="card-label">Stok Keluar</div>
        <div class="card-value">{{ $totalKeluar }}</div>
    </div>
    <div class="card card-penyesuaian">
        <div class="card-label">Penyesuaian</div>
        <div class="card-value">{{ $totalPenyesuaian }}</div>
    </div>
    <div class="card card-total">
        <div class="card-label">Total Entri</div>
        <div class="card-value">{{ $riwayats->count() }}</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>No. Referensi</th>
                <th>Tipe</th>
                <th>Item</th>
                <th>Jenis Item</th>
                <th>Qty Mutasi</th>
                <th>Stok Sebelum</th>
                <th>Stok Sesudah</th>
                <th>Keterangan</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayats as $i => $r)
            @php
                $item = $r->produk ? $r->produk->nama : ($r->bahanBaku ? $r->bahanBaku->nama : '-');
                $jenisItem = $r->produk ? 'Produk' : ($r->bahanBaku ? 'Bahan Baku' : '-');
                $satuan = $r->produk ? ($r->produk->satuan ?? 'Pcs') : ($r->bahanBaku ? $r->bahanBaku->satuan : '');
                $qtyDisplay = ($r->jenis == 'keluar' ? '-' : '+') . abs($r->jumlah) . ' ' . $satuan;
                $qtyClass = match($r->jenis) { 'masuk' => 'qty-masuk', 'keluar' => 'qty-keluar', default => 'qty-penyesuaian' };
                $badgeClass = match($r->jenis) { 'masuk' => 'badge-masuk', 'keluar' => 'badge-keluar', default => 'badge-penyesuaian' };
            @endphp
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td>{{ $r->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}<br><span class="text-muted">{{ $r->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}</span></td>
                <td style="font-weight:600;">{{ $r->referensi ?? '-' }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($r->jenis) }}</span></td>
                <td style="font-weight:600;">{{ $item }}</td>
                <td class="text-muted">{{ $jenisItem }}</td>
                <td class="{{ $qtyClass }}">{{ $qtyDisplay }}</td>
                <td class="text-muted">{{ (float)$r->stok_sebelum }}</td>
                <td style="font-weight:600;">{{ (float)$r->stok_sesudah }}</td>
                <td class="text-muted">{{ $r->keterangan ?? '-' }}</td>
                <td>{{ $r->user ? $r->user->name : 'Sistem/POS' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align:center; padding: 30px; color:#9ca3af;">Tidak ada data mutasi stok.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    Dynasty POS System &nbsp;|&nbsp; Laporan ini digenerate secara otomatis pada {{ now()->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB
</div>

</body>
</html>
