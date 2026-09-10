<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Barang - Dynasty</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }

        .page-header {
            background: #8b211e;
            color: white;
            padding: 16px 24px;
            margin-bottom: 16px;
        }
        .brand { font-size: 18px; font-weight: 700; letter-spacing: 1px; }
        .report-title { font-size: 12px; opacity: 0.85; margin-top: 3px; }
        .meta { font-size: 9px; opacity: 0.7; margin-top: 2px; }

        .filters {
            margin: 0 24px 12px;
            display: flex; gap: 12px; flex-wrap: wrap;
        }
        .filter-badge {
            background: #f1f5f9; border: 1px solid #e2e8f0;
            border-radius: 20px; padding: 2px 10px;
            font-size: 9px; color: #64748b;
        }
        .filter-badge strong { color: #1a1a2e; }

        .summary-cards {
            display: flex; gap: 10px;
            margin: 0 24px 14px;
        }
        .card {
            flex: 1; border-radius: 8px; padding: 10px 14px;
            border: 1px solid #e5e7eb;
        }
        .card-total { background: #f0f4ff; border-color: #c7d2fe; }
        .card-aktif { background: #f0fdf4; border-color: #bbf7d0; }
        .card-nonaktif { background: #fff1f2; border-color: #fecdd3; }
        .card-label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 3px; }
        .card-value { font-size: 16px; font-weight: 700; }
        .card-total .card-value { color: #4f46e5; }
        .card-aktif .card-value { color: #16a34a; }
        .card-nonaktif .card-value { color: #dc2626; }

        .table-wrapper { padding: 0 24px; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #8b211e; color: white; }
        thead th {
            padding: 8px 10px; text-align: left;
            font-size: 8.5px; text-transform: uppercase;
            letter-spacing: 0.3px; font-weight: 600;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        .badge {
            display: inline-block; padding: 2px 7px;
            border-radius: 10px; font-size: 8px; font-weight: 700;
        }
        .badge-aktif { background: #dcfce7; color: #16a34a; }
        .badge-nonaktif { background: #fee2e2; color: #dc2626; }
        .badge-bundling { background: #e0f2fe; color: #0369a1; }
        .badge-standar { background: #f3f4f6; color: #374151; }
        .text-muted { color: #9ca3af; }
        .fw-bold { font-weight: 700; }
        .footer {
            text-align: center; padding: 14px 24px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af; font-size: 8px; margin-top: 12px;
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="brand">Dynasty</div>
    <div class="report-title">Laporan Daftar Barang</div>
    <div class="meta">Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total Data: {{ $produks->count() }} barang</div>
</div>

@php
    $activeFilters = array_filter(['Kategori' => $filters['kategori'], 'Pencarian' => $filters['search']]);
    $totalAktif = $produks->where('aktif', true)->count();
    $totalNonaktif = $produks->where('aktif', false)->count();
@endphp

@if($activeFilters)
<div class="filters">
    <span style="font-size:9px; color:#6b7280; font-weight:600;">Filter aktif:</span>
    @foreach($activeFilters as $key => $val)
        <span class="filter-badge"><strong>{{ $key }}:</strong> {{ $val }}</span>
    @endforeach
</div>
@endif

<div class="summary-cards">
    <div class="card card-total">
        <div class="card-label">Total Barang</div>
        <div class="card-value">{{ $produks->count() }}</div>
    </div>
    <div class="card card-aktif">
        <div class="card-label">Aktif</div>
        <div class="card-value">{{ $totalAktif }}</div>
    </div>
    <div class="card card-nonaktif">
        <div class="card-label">Nonaktif</div>
        <div class="card-value">{{ $totalNonaktif }}</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th>Harga Beli (HPP)</th>
                <th>Harga Jual</th>
                <th>Stok</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produks as $i => $p)
            <tr>
                <td class="text-muted">{{ $i + 1 }}</td>
                <td class="fw-bold" style="color:#8b211e;">BRG-{{ str_pad($p->id, 3, '0', STR_PAD_LEFT) }}</td>
                <td class="fw-bold">{{ $p->nama }}</td>
                <td class="text-muted">{{ $p->kategori->nama ?? '-' }}</td>
                <td>
                    <span class="badge {{ ($p->tipe_produk ?? 'standar') === 'bundling' ? 'badge-bundling' : 'badge-standar' }}">
                        {{ ($p->tipe_produk ?? 'standar') === 'bundling' ? 'Bundling' : 'Standar' }}
                    </span>
                </td>
                <td class="text-muted">Rp {{ number_format($p->hpp, 0, ',', '.') }}</td>
                <td class="fw-bold">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                <td class="fw-bold {{ $p->stok < 20 ? '' : '' }}" style="{{ $p->stok < 20 ? 'color:#dc2626;' : '' }}">
                    {{ $p->stok }}
                </td>
                <td>
                    <span class="badge {{ $p->aktif ? 'badge-aktif' : 'badge-nonaktif' }}">
                        {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding:24px; color:#9ca3af;">Tidak ada data barang.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="footer">
    Dynasty POS System &nbsp;|&nbsp; Laporan Daftar Barang digenerate pada {{ now()->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB
</div>

</body>
</html>
