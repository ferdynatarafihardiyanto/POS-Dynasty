/**
 * ==============================================================================
 * 🖨️ MODUL PRINTER STRUK THERMAL - POS DYNASTY CAFE
 * ==============================================================================
 * Wadah integrasi printer struk kasir yang modular & fleksibel.
 * 
 * Pengembang / Kasir dapat dengan mudah menyesuaikan:
 * 1. Nama Printer (misal: 'POS-58', 'EPSON TM-T82', 'Panda POS', 'RP-58', dll.)
 * 2. Modul Cetak (Driver Direct Iframe, ESC/POS USB, Bluetooth/RawBT, atau Custom API)
 * 3. Lebar Kertas Thermal (58mm atau 80mm)
 * ==============================================================================
 */

window.ReceiptPrinter = {
    // Konfigurasi default (otomatis sinkron dengan localStorage)
    config: {
        printerName: localStorage.getItem('pos_printer_name') || 'POS-58 Thermal Printer',
        moduleType: localStorage.getItem('pos_printer_module') || 'iframe_direct',
        paperWidth: localStorage.getItem('pos_paper_width') || '58mm',
        autoPrint: localStorage.getItem('pos_auto_print') === 'true'
    },

    /**
     * Simpan konfigurasi ke localStorage
     */
    saveConfig(cfg) {
        if (cfg.printerName !== undefined) {
            this.config.printerName = cfg.printerName;
            localStorage.setItem('pos_printer_name', cfg.printerName);
        }
        if (cfg.moduleType !== undefined) {
            this.config.moduleType = cfg.moduleType;
            localStorage.setItem('pos_printer_module', cfg.moduleType);
        }
        if (cfg.paperWidth !== undefined) {
            this.config.paperWidth = cfg.paperWidth;
            localStorage.setItem('pos_paper_width', cfg.paperWidth);
        }
        if (cfg.autoPrint !== undefined) {
            this.config.autoPrint = !!cfg.autoPrint;
            localStorage.setItem('pos_auto_print', cfg.autoPrint ? 'true' : 'false');
        }
        console.log('[ReceiptPrinter] Konfigurasi tersimpan:', this.config);
    },

    /**
     * Fungsi utama untuk mencetak struk
     * @param {Object} receiptData Data transaksi lengkap
     * @param {Object} overrideConfig Opsi konfigurasi khusus (opsional)
     */
    async printReceipt(receiptData, overrideConfig = null) {
        const activeConfig = Object.assign({}, this.config, overrideConfig || {});
        console.log(`[ReceiptPrinter] Memulai pencetakan dengan printer: "${activeConfig.printerName}" (Modul: ${activeConfig.moduleType})`);

        switch (activeConfig.moduleType) {
            case 'iframe_direct':
                return this.printViaIframe(receiptData, activeConfig);
            
            case 'esc_pos':
                return this.printViaEscPos(receiptData, activeConfig);
            
            case 'rawbt':
                return this.printViaRawBT(receiptData, activeConfig);
            
            case 'custom_api':
                return this.printViaCustomApi(receiptData, activeConfig);
            
            default:
                return this.printViaIframe(receiptData, activeConfig);
        }
    },

    /**
     * MODUL 1: Direct Browser / Silent Print via Hidden Iframe
     * - Keunggulan: Pengguna TETAP berada di web POS tanpa berpindah tab atau membuka halaman kosong.
     * - Kompatibel dengan semua printer thermal yang terpasang driver Windows (POS-58/80, Epson, dsb).
     */
    printViaIframe(receiptData, config) {
        let iframe = document.getElementById('posReceiptPrintIframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'posReceiptPrintIframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
        }

        // Jika pesanan_id tersedia, muat template cetak dari server
        if (receiptData.pesanan_id && receiptData.pesanan_id > 0) {
            iframe.src = `/admin/transaksi/${receiptData.pesanan_id}/print?autoprint=0&t=` + new Date().getTime();
            iframe.onload = () => {
                setTimeout(() => {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (err) {
                        console.error('[ReceiptPrinter] Gagal memicu print iframe:', err);
                    }
                }, 350);
            };
        } else {
            // Untuk Test Print atau cetak mandiri dari data lokal:
            const htmlContent = this.generateReceiptHtml(receiptData, config);
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(htmlContent);
            doc.close();
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 350);
        }
    },

    /**
     * MODUL 2: Wadah ESC/POS USB Driver (Raw Printer Commands)
     * - Tempat menyambungkan printer thermal USB / Serial melalui WebUSB / WebSocket Daemon.
     * - Anda tinggal memasukkan nama printer atau endpoint driver Anda di sini.
     */
    async printViaEscPos(receiptData, config) {
        console.log(`[ReceiptPrinter ESC/POS] Mengirim perintah ESC/POS ke modul printer: "${config.printerName}"`);
        const bytes = this.generateEscPosBytes(receiptData, config);
        
        // HOOK / WADAH KONEKSI DRIVER PRINTER ANDA:
        // Contoh implementasi jika menggunakan daemon lokal / WebUSB:
        /*
        try {
            const response = await fetch('http://localhost:8080/printer/escpos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    printer_name: config.printerName,
                    paper_width: config.paperWidth,
                    raw_base64: btoa(String.fromCharCode.apply(null, bytes))
                })
            });
            console.log('[ReceiptPrinter ESC/POS] Respons modul:', await response.json());
        } catch (e) {
            console.warn('[ReceiptPrinter ESC/POS] Daemon driver lokal belum aktif, fallback ke iframe print.');
            this.printViaIframe(receiptData, config);
        }
        */
        
        // Default fallback jika driver hardware belum disambungkan:
        this.printViaIframe(receiptData, config);
    },

    /**
     * MODUL 3: Wadah Bluetooth / RawBT (Android POS)
     * - Menggunakan skema URL RawBT untuk mencetak langsung ke printer Bluetooth di tablet/smartphone.
     */
    printViaRawBT(receiptData, config) {
        const plainText = this.generatePlainTextReceipt(receiptData, config);
        const rawBtUri = "rawbt:base64," + btoa(unescape(encodeURIComponent(plainText)));
        window.location.href = rawBtUri;
    },

    /**
     * MODUL 4: Wadah Custom Printing API / QZ Tray / PrintNode
     * - Tempat jika Anda ingin mengirim data struk ke service cloud / service background khusus.
     */
    async printViaCustomApi(receiptData, config) {
        console.log(`[ReceiptPrinter Custom API] Wadah custom printer: "${config.printerName}" dipanggil.`);
        // Sesuaikan dengan API service printer Anda di sini:
        // e.g. qz.print(...) atau PrintNode API
        this.printViaIframe(receiptData, config);
    },

    /**
     * Helper: Generate HTML Struk untuk Thermal Printer
     */
    generateReceiptHtml(data, config) {
        const width = config.paperWidth === '80mm' ? '80mm' : '58mm';
        let itemsHtml = '';
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const subtotal = item.quantity * item.unitPrice;
                itemsHtml += `
                    <div style="margin-bottom: 4px;">
                        <div style="font-weight: bold;">${item.product.nama}</div>
                        ${item.selectedOptions && item.selectedOptions.length > 0 ? 
                            `<div style="font-size: 10px; color: #555; padding-left: 8px;">` +
                            item.selectedOptions.map(o => `+ ${o.nama}`).join('<br>') +
                            `</div>` : ''
                        }
                        <div style="display: flex; justify-content: space-between;">
                            <span>${item.quantity} x ${this.formatRupiah(item.unitPrice)}</span>
                            <span>${this.formatRupiah(subtotal)}</span>
                        </div>
                    </div>
                `;
            });
        }

        return `<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Struk #${data.nomor_pesanan || 'PREVIEW'}</title>
            <style>
                body {
                    font-family: 'Courier New', Courier, monospace;
                    width: ${width};
                    margin: 0 auto;
                    padding: 0;
                    font-size: 12px;
                    color: #000;
                }
                .text-center { text-align: center; }
                .fw-bold { font-weight: bold; }
                .divider { border-top: 1px dashed #000; margin: 6px 0; }
                .row-item { display: flex; justify-content: space-between; }
                @media print {
                    body { margin: 0; padding: 0; width: ${width}; }
                }
            </style>
        </head>
        <body>
            <div class="text-center">
                <div class="fw-bold" style="font-size: 15px;">KEDAI DYNASTY</div>
                <div style="font-size: 11px;">Jl. Contoh No. 123</div>
                <div style="font-size: 10px;">Telp: 08123456789</div>
            </div>
            <div class="divider"></div>
            <div style="font-size: 11px;">
                <div>No: ${data.nomor_pesanan || '-'}</div>
                <div>Waktu: ${data.time || new Date().toLocaleString('id-ID')}</div>
                <div>Kasir: ${data.cashierName || 'Kasir'}</div>
                <div>Pelanggan: ${data.customerName || 'Pelanggan Umum'}</div>
                <div>Meja: ${data.tableName || '-'}</div>
            </div>
            <div class="divider"></div>
            ${itemsHtml}
            <div class="divider"></div>
            <div class="row-item fw-bold">
                <span>Total:</span>
                <span>${this.formatRupiah(data.total || 0)}</span>
            </div>
            <div class="row-item">
                <span>Metode:</span>
                <span style="text-transform: uppercase;">${data.paymentMethod || 'TUNAI'}</span>
            </div>
            <div class="row-item">
                <span>Bayar:</span>
                <span>${this.formatRupiah(data.cashReceived || 0)}</span>
            </div>
            <div class="row-item">
                <span>Kembali:</span>
                <span>${this.formatRupiah(data.changeAmount || 0)}</span>
            </div>
            <div class="divider"></div>
            <div class="text-center" style="margin-top: 8px;">
                <div>Terima Kasih</div>
                <div>Silakan datang kembali</div>
            </div>
        </body>
        </html>`;
    },

    /**
     * Helper: Generate Plain Text Struk
     */
    generatePlainTextReceipt(data, config) {
        const lineLen = config.paperWidth === '80mm' ? 42 : 32;
        const line = "-".repeat(lineLen);
        let text = "       KEDAI DYNASTY\n";
        text += "     Jl. Contoh No. 123\n";
        text += "     Telp: 08123456789\n";
        text += line + "\n";
        text += `No: ${data.nomor_pesanan || '-'}\n`;
        text += `Waktu: ${data.time || '-'}\n`;
        text += `Pelanggan: ${data.customerName || 'Pelanggan Umum'}\n`;
        text += `Meja: ${data.tableName || '-'}\n`;
        text += line + "\n";
        
        if (data.items) {
            data.items.forEach(item => {
                text += `${item.product.nama}\n`;
                const qtyPrice = `${item.quantity} x ${this.formatRupiah(item.unitPrice)}`;
                const total = `${this.formatRupiah(item.quantity * item.unitPrice)}`;
                const spaces = Math.max(1, lineLen - qtyPrice.length - total.length);
                text += qtyPrice + " ".repeat(spaces) + total + "\n";
            });
        }
        
        text += line + "\n";
        text += `Total: ${this.formatRupiah(data.total || 0)}\n`;
        text += `Metode: ${(data.paymentMethod || 'TUNAI').toUpperCase()}\n`;
        text += `Bayar: ${this.formatRupiah(data.cashReceived || 0)}\n`;
        text += `Kembali: ${this.formatRupiah(data.changeAmount || 0)}\n`;
        text += line + "\n";
        text += "       Terima Kasih\n";
        text += "  Silakan datang kembali\n\n\n";
        return text;
    },

    /**
     * Helper: Generate ESC/POS Command Bytes
     */
    generateEscPosBytes(data, config) {
        // Standar byte sequence: Init (ESC @), Center (ESC a 1), Text, Cut (GS V 65 0)
        const ESC = 0x1B;
        const GS = 0x1D;
        let bytes = [ESC, 0x40]; // Initialize printer
        
        // Contoh byte generator - siap diintegrasikan dengan driver ESC/POS Anda
        const text = this.generatePlainTextReceipt(data, config);
        for (let i = 0; i < text.length; i++) {
            bytes.push(text.charCodeAt(i));
        }
        
        // Feed & Cut Paper (GS V 65 0)
        bytes.push(0x0A, 0x0A, 0x0A);
        bytes.push(GS, 0x56, 65, 0);
        return new Uint8Array(bytes);
    },

    formatRupiah(number) {
        return 'Rp ' + Math.round(number || 0).toLocaleString('id-ID');
    }
};
