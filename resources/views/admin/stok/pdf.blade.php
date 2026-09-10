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
            background-color: #8b211e;
            background: linear-gradient(135deg, #8b211e 0%, #6b1915 100%);
            color: white;
            padding: 16px 24px;
            margin-bottom: 16px;
        }
        .brand { font-size: 20px; font-weight: 700; letter-spacing: 1px; }
        .report-title { font-size: 13px; opacity: 0.85; margin-top: 3px; }
        .meta { font-size: 9.5px; opacity: 0.7; margin-top: 2px; }
        .filters {
            margin: 0 24px 12px;
        }
        .filter-badge {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 3px 10px;
            font-size: 9.5px;
            color: #64748b;
            margin-right: 8px;
            margin-bottom: 6px;
        }
        .filter-badge strong { color: #1a1a2e; }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 14px;
        }
        .card {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
        }
        .card-masuk { background-color: #f0fdf4; border-color: #bbf7d0; }
        .card-keluar { background-color: #fff1f2; border-color: #fecdd3; }
        .card-penyesuaian { background-color: #fffbeb; border-color: #fde68a; }
        .card-total { background-color: #f0f4ff; border-color: #c7d2fe; }
        .card-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 3px; }
        .card-value { font-size: 16px; font-weight: 700; }
        .card-masuk .card-value { color: #16a34a; }
        .card-keluar .card-value { color: #dc2626; }
        .card-penyesuaian .card-value { color: #d97706; }
        .card-total .card-value { color: #4f46e5; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 20px;
        }
        thead tr {
            background-color: #8b211e;
            color: white;
        }
        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 600;
        }
        tbody tr:nth-child(even) { background-color: #f9fafb; }
        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-masuk { background: #dcfce7; color: #16a34a; }
        .badge-keluar { background: #fee2e2; color: #dc2626; }
        .badge-penyesuaian { background: #fef9c3; color: #854d0e; }
        .qty-masuk { color: #16a34a; font-weight: 700; }
        .qty-keluar { color: #dc2626; font-weight: 700; }
        .qty-penyesuaian { color: #d97706; font-weight: 700; }
        .text-muted { color: #9ca3af; font-size: 9px; }
        .footer {
            text-align: center;
            padding: 14px 24px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 8.5px;
        }
        .table-wrapper { padding: 0 24px; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        .print-btn-area {
            padding: 12px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
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
        .btn-back { background: #e2e8f0; color: #334155; text-decoration: none; }
    </style>
</head>
<body>

@if(empty($isPdf))
<div class="print-btn-area no-print">
    <button class="btn btn-print" onclick="window.print()">🖨️ Print / Simpan PDF</button>
    <a href="{{ route('admin.stok.index', array_filter(['tipe' => $filters['tipe'], 'tanggal' => $filters['tanggal'], 'search' => $filters['search']])) }}" class="btn btn-back">← Kembali</a>
    @if(!empty($fallback))
        <span style="font-size: 11px; color: #92400e; background: #fef3c7; border: 1px solid #fde68a; padding: 5px 12px; border-radius: 6px; margin-left: auto;">
            ℹ️ Mode Cetak Langsung Browser (Gunakan tombol <strong>Print / Simpan PDF</strong> untuk menyimpan sebagai PDF)
        </span>
    @endif
</div>
@endif

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
    <span style="font-size:9.5px; color:#6b7280; font-weight:600; margin-right: 6px;">Filter aktif:</span>
    @foreach($activeFilters as $key => $val)
        <span class="filter-badge"><strong>{{ $key }}:</strong> {{ ucfirst($val) }}</span>
    @endforeach
</div>
@endif

<div class="table-wrapper">
    <table class="summary-table">
        <tr>
            <td style="width: 25%; vertical-align: top; padding: 0 4px 0 0;">
                <div class="card card-masuk">
                    <div class="card-label">Stok Masuk</div>
                    <div class="card-value">{{ $totalMasuk }}</div>
                </div>
            </td>
            <td style="width: 25%; vertical-align: top; padding: 0 4px;">
                <div class="card card-keluar">
                    <div class="card-label">Stok Keluar</div>
                    <div class="card-value">{{ $totalKeluar }}</div>
                </div>
            </td>
            <td style="width: 25%; vertical-align: top; padding: 0 4px;">
                <div class="card card-penyesuaian">
                    <div class="card-label">Penyesuaian</div>
                    <div class="card-value">{{ $totalPenyesuaian }}</div>
                </div>
            </td>
            <td style="width: 25%; vertical-align: top; padding: 0 0 0 4px;">
                <div class="card card-total">
                    <div class="card-label">Total Entri</div>
                    <div class="card-value">{{ $riwayats->count() }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="table-wrapper">
    <table class="data-table">
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
