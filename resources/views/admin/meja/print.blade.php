<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Meja {{ $meja->table_number }} - Dynasty Cafe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f3f4f6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .action-bar {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Printable Tent Card */
        .table-card {
            background: #ffffff;
            width: 360px;
            border-radius: 28px;
            padding: 35px 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 3px dashed #8b211e;
            text-align: center;
            position: relative;
        }

        .cafe-badge {
            display: inline-block;
            background: #fdf2f2;
            color: #8b211e;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 2px;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .table-title {
            font-size: 38px;
            font-weight: 900;
            color: #111827;
            margin-bottom: 4px;
            line-height: 1.1;
        }

        .table-desc {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 18px;
        }

        .qr-wrapper {
            background: #ffffff;
            padding: 12px;
            display: inline-block;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            margin-bottom: 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }

        .qr-img {
            width: 230px;
            height: 230px;
            display: block;
            border-radius: 8px;
        }

        .scan-instruction {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .scan-sub {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .url-box {
            font-size: 10px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #6b7280;
            background: #f9fafb;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
            word-break: break-all;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .no-print {
                display: none !important;
            }

            .table-card {
                box-shadow: none !important;
                border: 2px dashed #000000 !important;
                margin: auto;
            }
        }
    </style>
</head>
<body>

    <!-- Action Bar (Hidden on Print) -->
    <div class="action-bar no-print">
        <a href="{{ route('admin.meja.index') }}" class="btn btn-light shadow-sm rounded-3 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn text-white shadow-sm rounded-3 fw-bold px-4" style="background-color: #8b211e;" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
        <button type="button" class="btn btn-success shadow-sm rounded-3 fw-semibold" onclick="downloadQrImage()">
            <i class="bi bi-download me-1"></i> Download Gambar (PNG)
        </button>
        <button type="button" class="btn btn-dark shadow-sm rounded-3 fw-semibold" onclick="copyLink()">
            <i class="bi bi-clipboard me-1"></i> Salin Link
        </button>
        <a href="{{ $qrUrl }}" target="_blank" class="btn btn-outline-secondary shadow-sm rounded-3 fw-semibold">
            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Menu Meja
        </a>
    </div>

    <!-- Printable Card -->
    <div class="table-card">
        <div class="cafe-badge">☕ Dynasty Cafe</div>
        <div class="table-title">Meja {{ $meja->table_number }}</div>
        @if(!empty($meja->name))
            <div class="table-desc">{{ $meja->name }}</div>
        @endif

        <div class="qr-wrapper">
            <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=350x350&data={{ urlencode($qrUrl) }}" alt="QR Meja {{ $meja->table_number }}">
        </div>

        <div class="scan-instruction">Scan QR untuk Melihat Menu</div>
        <div class="scan-sub">Pesan dan bayar langsung dari meja Anda</div>
        
        <div class="url-box">{{ $qrUrl }}</div>
    </div>

    <script>
        function copyLink() {
            const link = "{{ $qrUrl }}";
            navigator.clipboard.writeText(link).then(() => {
                alert('Link pesanan Meja {{ $meja->table_number }} berhasil disalin:\n' + link);
            }).catch(() => {
                prompt('Salin link ini:', link);
            });
        }

        async function downloadQrImage() {
            const qrSrc = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data={{ urlencode($qrUrl) }}";
            try {
                const res = await fetch(qrSrc);
                const blob = await res.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = "QR_Meja_{{ $meja->table_number }}.png";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } catch (e) {
                window.open(qrSrc, '_blank');
            }
        }

        // Otomatis buka dialog cetak
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('noprint') !== '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>
</html>
