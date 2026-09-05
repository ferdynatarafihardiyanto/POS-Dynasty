<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #{{ $pesanan->nomor_pesanan }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm; /* Thermal printer width */
            margin: 0 auto;
            padding: 0;
            font-size: 12px;
            color: #000;
        }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .item-row { display: flex; justify-content: space-between; }
        .item-name { width: 100%; text-align: left; }
        .item-qty-price { display: flex; justify-content: space-between; padding-left: 10px;}
        
        @media print {
            body { margin: 0; padding: 0; width: 58mm; }
            .no-print { display: none; }
        }
</head>
<body>
    <div class="text-center mb-2">
        <div class="fw-bold" style="font-size: 16px;">KEDAI DYNASTY</div>
        <div>Jl. Contoh No. 123</div>
        <div style="font-size: 10px;">Telp: 08123456789</div>
    </div>
    
    <div class="divider"></div>
    
    @php
        $rawCatatan = $pesanan->catatan ?? '';
        $custName = 'Pelanggan Umum';
        if (preg_match('/^(.*?)\s*\(Via/i', $rawCatatan, $cm)) {
            $custName = trim($cm[1]) ?: 'Pelanggan Umum';
        } elseif (!empty($rawCatatan)) {
            $custName = $rawCatatan;
        }
    @endphp
    <div class="mb-1">
        <div>No: {{ $pesanan->nomor_pesanan }}</div>
        <div>Waktu: {{ \Carbon\Carbon::parse($pesanan->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</div>
        <div>Kasir: {{ Auth::user()->name }}</div>
        <div>Pelanggan: {{ $custName }}</div>
        <div>Meja: {{ $pesanan->meja ? ($pesanan->meja->name ?? 'Meja ' . $pesanan->meja->table_number) : '-' }}</div>
    </div>
    
    <div class="divider"></div>
    
    <div class="mb-2">
        @foreach($pesanan->detailPesanan as $item)
        <div class="mb-1">
            <div class="item-name">{{ $item->nama_produk }}</div>
            <div class="item-qty-price">
                <span>{{ $item->jumlah }} x {{ number_format($item->harga, 0, ',', '.') }}</span>
                <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($item->modifiers_snapshot)
                @php $mods = json_decode($item->modifiers_snapshot, true) ?? []; @endphp
                @foreach($mods as $mod)
                <div style="font-size: 10px; padding-left: 10px; color: #555;">
                    + {{ $mod['option_nama'] }}
                </div>
                @endforeach
            @endif
        </div>
        @endforeach
    </div>
    
    <div class="divider"></div>
    
    <div class="item-row mb-1">
        <span>Total Harga</span>
        <span class="fw-bold">{{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
    </div>
    
    @php
        // Parse catatan for payment method & cash
        $catatan = $pesanan->catatan ?? '';
        $paymentMethod = 'TUNAI';
        $cash = $pesanan->total_harga;
        
        if (str_contains(strtoupper($catatan), 'QRIS')) $paymentMethod = 'QRIS';
        if (str_contains(strtoupper($catatan), 'DEBIT')) $paymentMethod = 'DEBIT';
        
        preg_match('/Rp (\d+)/', $catatan, $matches);
        if(count($matches) > 1) {
            $cash = (int) $matches[1];
        }
    @endphp
    
    <div class="item-row">
        <span>Metode Pemb.</span>
        <span>{{ $paymentMethod }}</span>
    </div>
    <div class="item-row">
        <span>Bayar</span>
        <span>{{ number_format($cash, 0, ',', '.') }}</span>
    </div>
    <div class="item-row">
        <span>Kembali</span>
        <span>{{ number_format($cash - $pesanan->total_harga, 0, ',', '.') }}</span>
    </div>
    
    <div class="divider mt-2"></div>
    
    <div class="text-center mt-2">
        <div>Terima Kasih</div>
        <div>Silakan datang kembali</div>
    </div>
    
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        // Cetak otomatis jika tidak diset autoprint=0
        if (urlParams.get('autoprint') !== '0') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>
</body>
</html>
