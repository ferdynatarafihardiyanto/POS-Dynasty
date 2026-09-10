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
        
        img.thermal-bw {
            filter: grayscale(100%) contrast(180%) brightness(85%);
            -webkit-filter: grayscale(100%) contrast(180%) brightness(85%);
            mix-blend-mode: multiply;
        }
        @media print {
            body { margin: 0; padding: 0; width: 58mm; }
            img.thermal-bw {
                filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                -webkit-filter: grayscale(100%) contrast(180%) brightness(85%) !important;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php
        $profilePath = storage_path('app/store_profile.json');
        $storeProfile = [
            'namaToko' => 'Kedai Kopi Dinasty',
            'slogan' => '',
            'alamat' => 'Jl. Jambangan Kebon Agung No. 12 B, Jambangan, Kec. Jambangan, Surabaya, Jawa Timur 60232',
            'telepon' => '',
            'sosmed' => 'kedaikopidinasty.tokoa.id',
            'pesanFooterStruk' => '',
            'cetakLogoStruk' => true,
            'logo_url' => null,
            'wifiList' => [
                ['ssid' => 'KEDAI DINASTY 5G', 'password' => 'wargadinasty'],
                ['ssid' => 'KEDAI DINASTY LT 2', 'password' => 'cobatanyabarista']
            ]
        ];
        if (file_exists($profilePath)) {
            $loaded = json_decode(file_get_contents($profilePath), true);
            if ($loaded && is_array($loaded)) {
                $storeProfile = array_merge($storeProfile, $loaded);
            }
        }
    @endphp

    <div class="text-center mb-2">
        @if(!empty($storeProfile['logo_url']) && ($storeProfile['cetakLogoStruk'] ?? true))
            <div style="margin-bottom: 4px;">
                <img src="{{ $storeProfile['logo_url'] }}" alt="Logo" class="thermal-bw" style="max-height: 50px; max-width: 65px; object-fit: contain;">
            </div>
        @elseif(!empty($storeProfile['logo_data']) && ($storeProfile['cetakLogoStruk'] ?? true))
            <div style="margin-bottom: 4px;">
                <img src="{{ $storeProfile['logo_data'] }}" alt="Logo" class="thermal-bw" style="max-height: 50px; max-width: 65px; object-fit: contain;">
            </div>
        @endif
        <div class="fw-bold" style="font-size: 15px; text-transform: uppercase;">{{ $storeProfile['namaToko'] ?? 'Kedai Kopi Dinasty' }}</div>
        @if(!empty($storeProfile['slogan']))
            <div style="font-size: 10px; font-weight: bold; text-transform: uppercase;">{{ $storeProfile['slogan'] }}</div>
        @endif
        <div style="font-size: 10px; line-height: 1.3; padding: 0 4px;">{{ $storeProfile['alamat'] ?? 'Jl. Jambangan Kebon Agung No. 12 B, Surabaya' }}</div>
        @if(!empty($storeProfile['telepon']))
            <div style="font-size: 9.5px;">Telp: {{ $storeProfile['telepon'] }}</div>
        @endif
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
            @if(!empty($item->catatan))
                <div style="font-size: 10px; font-style: italic; color: #444; padding-left: 10px;">
                    * Catatan: {{ $item->catatan }}
                </div>
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
    
    <div style="font-size: 10px; line-height: 1.4; margin-top: 6px; text-align: left;">
        @if(!empty($storeProfile['wifiList']) && count($storeProfile['wifiList']) > 0)
            @foreach($storeProfile['wifiList'] as $w)
                <div>Wifi : {{ $w['ssid'] ?? '' }}</div>
                <div>Pass : {{ $w['password'] ?? '' }}</div>
            @endforeach
        @else
            <div>Wifi : KEDAI DINASTY 5G</div>
            <div>Pass : wargadinasty</div>
            <div>Wifi : KEDAI DINASTY LT 2</div>
            <div>Pass : cobatanyabarista</div>
        @endif
    </div>

    @if(!empty($storeProfile['pesanFooterStruk']))
        <div class="text-center mt-2" style="font-size: 10px; white-space: pre-line;">
            {{ $storeProfile['pesanFooterStruk'] }}
        </div>
    @endif

    <div class="text-center mt-2" style="font-size: 11px; padding-top: 4px; font-weight: bold;">
        <div>{{ $storeProfile['sosmed'] ?? 'kedaikopidinasty.tokoa.id' }}</div>
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
