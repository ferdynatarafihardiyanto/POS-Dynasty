import React, { useRef, useState, useEffect } from 'react';
import { X, Printer, Download, CheckCircle2, Loader2, Share2 } from 'lucide-react';
import { toPng } from 'html-to-image';

export default function CustomerReceiptModal({ isOpen, onClose, order }) {
    const receiptRef = useRef(null);
    const receiptCaptureRef = useRef(null);
    const [isDownloading, setIsDownloading] = useState(false);
    const [downloadSuccess, setDownloadSuccess] = useState(false);

    // Profile & Logo Toko (Sinkron dengan Admin Profil & API)
    const [storeProfile, setStoreProfile] = useState(() => {
        try {
            const saved = localStorage.getItem('dynasty_store_profile');
            if (saved) return JSON.parse(saved);
        } catch (e) {}
        return {
            namaToko: 'Kedai Kopi Dinasty',
            slogan: 'Authentic Coffee & Eatery',
            telepon: '0812-3456-7890',
            sosmed: 'kedaikopidinasty.tokoa.id',
            alamat: 'Jl. Jambangan Kebon Agung No. 12 B, Surabaya',
            pesanFooterStruk: "Terima kasih atas kunjungan Anda!\nSilakan datang kembali.",
            cetakLogoStruk: true,
            wifiList: [
                { ssid: 'KEDAI DINASTY 5G', password: 'wargadinasty' },
                { ssid: 'KEDAI DINASTY LT 2', password: 'cobatanyabarista' }
            ]
        };
    });

    const [storeLogo, setStoreLogo] = useState(() => {
        return localStorage.getItem('dynasty_logo_data') || null;
    });

    useEffect(() => {
        // Ambil profil toko terbaru dari backend API
        fetch('/api/profil-toko')
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    setStoreProfile(res.data);
                    if (res.data.logo_url) {
                        setStoreLogo(res.data.logo_url);
                    } else if (res.data.logo_data) {
                        setStoreLogo(res.data.logo_data);
                    }
                }
            })
            .catch(() => {});
    }, [isOpen]);

    if (!isOpen || !order) return null;

    // Format plain number with thousand comma, e.g. 12,000 (Exact match to real thermal receipt scan)
    const formatNumber = (num) => {
        return new Intl.NumberFormat('en-US', {
            maximumFractionDigits: 0
        }).format(num || 0);
    };

    // Format date as: 08/09/2026 11:05
    const formatDateReceipt = (dateInput) => {
        const d = new Date(dateInput || Date.now());
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const minutes = String(d.getMinutes()).padStart(2, '0');
        return `${day}/${month}/${year} ${hours}:${minutes}`;
    };

    const cleanOrderNumber = (order.orderNumber || '').replace(/^#/, '');
    let receiptNumber = order.nomor_struk;
    if (!receiptNumber) {
        if (cleanOrderNumber.startsWith('ORD-')) {
            receiptNumber = 'SR' + cleanOrderNumber.slice(-5);
        } else if (cleanOrderNumber) {
            receiptNumber = cleanOrderNumber.startsWith('SR') ? cleanOrderNumber : 'SR' + cleanOrderNumber;
        } else {
            receiptNumber = 'SR44646';
        }
    }

    const customerName = order.customerName || order.nama_pelanggan || localStorage.getItem('pos_customer_name') || (order.tableNumber ? `Meja ${order.tableNumber}` : 'natan');
    const cashierName = order.kasir || order.cashierName || 'Masdarul';
    
    // Normalisasi metode pembayaran: Cash / QRIS / Transfer
    const rawPayment = (order.metode_pembayaran || order.metode || 'Cash').toLowerCase();
    const paymentMethod = rawPayment === 'cash' || rawPayment === 'tunai' 
        ? 'Cash' 
        : (rawPayment.includes('qris') ? 'QRIS' : (rawPayment.includes('tf') || rawPayment.includes('transfer') ? 'Transfer' : 'Cash'));

    const items = order.items || [];
    const totalQty = items.reduce((acc, item) => acc + (parseInt(item.quantity || item.jumlah, 10) || 1), 0) || 1;
    const grandTotal = order.grandTotal || items.reduce((acc, item) => acc + (item.totalPrice || ((item.unitPrice || item.harga || 0) * (item.quantity || 1))), 0) || 0;

    const handlePrint = () => {
        window.print();
    };

    // Helper: generate PNG dataUrl with html-to-image and fallback to html2canvas
    const generateReceiptImage = async () => {
        const targetNode = receiptCaptureRef.current || receiptRef.current;
        if (!targetNode) return null;

        try {
            return await toPng(targetNode, {
                quality: 1,
                pixelRatio: 2.5,
                cacheBust: true,
            });
        } catch (err1) {
            console.warn('html-to-image toPng failed, falling back to html2canvas:', err1);
            const html2canvas = (await import('html2canvas')).default;
            const canvas = await html2canvas(targetNode, {
                scale: 2.5,
                useCORS: true,
                backgroundColor: null,
                logging: false,
            });
            return canvas.toDataURL('image/png');
        }
    };

    // Download Struk Langsung ke Galeri / Penyimpanan HP
    const handleDownloadReceipt = async () => {
        if (isDownloading) return;
        setIsDownloading(true);
        setDownloadSuccess(false);

        try {
            const dataUrl = await generateReceiptImage();
            if (!dataUrl) throw new Error('Gagal menghasilkan gambar');

            const cleanNum = (receiptNumber || 'receipt').replace(/[^a-zA-Z0-9-_]/g, '');
            const fileName = `Struk-${cleanNum}.png`;

            const res = await fetch(dataUrl);
            const blob = await res.blob();
            const file = new File([blob], fileName, { type: 'image/png' });

            // Deteksi perangkat iOS (iPhone / iPad)
            // Di iOS Safari, Web Share API langsung menyediakan opsi "Simpan Gambar" (Save Image) ke Foto / Galeri
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

            if (isIOS && navigator.canShare && navigator.canShare({ files: [file] })) {
                try {
                    await navigator.share({
                        files: [file],
                        title: `Struk Pembayaran - ${receiptNumber}`,
                        text: `Struk pembayaran ${storeProfile.namaToko || 'Kedai Kopi Dinasty'}`
                    });
                    setDownloadSuccess(true);
                    setTimeout(() => setDownloadSuccess(false), 3500);
                    setIsDownloading(false);
                    return;
                } catch (shareErr) {
                    if (shareErr.name === 'AbortError') {
                        setIsDownloading(false);
                        return;
                    }
                }
            }

            // Android & Desktop: Download langsung ke Penyimpanan Device (Folder Download -> Galeri HP)
            const blobUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            setTimeout(() => URL.revokeObjectURL(blobUrl), 4000);

            setDownloadSuccess(true);
            setTimeout(() => setDownloadSuccess(false), 3500);
        } catch (error) {
            console.error('Error saat mendownload struk:', error);
            alert('Gagal mendownload struk. Silakan coba screenshot struk ini.');
        } finally {
            setIsDownloading(false);
        }
    };

    // Opsi Berbagi Struk (WhatsApp / Share Sheet HP)
    const handleShareReceipt = async () => {
        if (isDownloading) return;
        setIsDownloading(true);

        try {
            const dataUrl = await generateReceiptImage();
            if (!dataUrl) throw new Error('Gagal menghasilkan gambar');

            const cleanNum = (receiptNumber || 'receipt').replace(/[^a-zA-Z0-9-_]/g, '');
            const fileName = `Struk-${cleanNum}.png`;

            const res = await fetch(dataUrl);
            const blob = await res.blob();
            const file = new File([blob], fileName, { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({
                    files: [file],
                    title: `Struk Pembayaran - ${receiptNumber}`,
                    text: `Struk pembayaran ${storeProfile.namaToko || 'Kedai Kopi Dinasty'}`
                });
            } else {
                // Fallback ke download biasa jika share API tidak didukung
                handleDownloadReceipt();
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Error sharing receipt:', error);
            }
        } finally {
            setIsDownloading(false);
        }
    };

    return (
        <div className="fixed inset-0 z-70 bg-black/75 backdrop-blur-xs flex justify-center items-center p-3 sm:p-4 animate-fade-in print:p-0 print:bg-white print:static print:z-0">
            {/* Scoped Print CSS for Thermal 58mm Output */}
            <style>{`
                @media print {
                    body * {
                        visibility: hidden !important;
                    }
                    #thermalReceiptModalPrintArea, #thermalReceiptModalPrintArea * {
                        visibility: visible !important;
                    }
                    #thermalReceiptModalPrintArea {
                        position: fixed !important;
                        left: 0 !important;
                        top: 0 !important;
                        width: 58mm !important;
                        margin: 0 auto !important;
                        padding: 2mm 3mm !important;
                        background: #ffffff !important;
                        color: #000000 !important;
                        box-shadow: none !important;
                        border: none !important;
                    }
                    #thermalReceiptModalPrintArea img {
                        filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                        -webkit-filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                    }
                    @page {
                        size: 58mm auto;
                        margin: 0;
                    }
                }
            `}</style>

            <div className="w-full max-w-[340px] bg-stone-900 rounded-3xl shadow-2xl flex flex-col max-h-[94vh] overflow-hidden animate-slide-up relative border border-stone-800 print:shadow-none print:border-none print:max-h-none print:rounded-none print:w-full print:bg-white">
                
                {/* Header Actions - Hidden on Print */}
                <div className="px-4 py-3 bg-stone-900 border-b border-stone-800 flex items-center justify-between shrink-0 print:hidden">
                    <div className="flex items-center gap-2 text-white">
                        <Printer className="w-4 h-4 text-amber-400" />
                        <span className="font-mono font-bold text-xs uppercase tracking-wider text-stone-200">Struk Pembayaran (Hitam Putih)</span>
                    </div>
                    <button
                        onClick={onClose}
                        className="w-7 h-7 rounded-full bg-stone-800 hover:bg-stone-700 text-stone-300 hover:text-white flex items-center justify-center transition active:scale-95 cursor-pointer"
                        title="Tutup"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Scrollable Receipt Preview Area */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-3.5 sm:p-5 bg-stone-950 flex flex-col items-center print:p-0 print:bg-white">
                    
                    {/* Receipt Capture Container (Includes Sawtooth Borders for Realistic Receipt Tear) */}
                    <div 
                        ref={receiptCaptureRef}
                        className="w-full max-w-[320px] flex flex-col items-center bg-transparent print:w-full print:max-w-none"
                    >
                        {/* Top Paper Tear Sawtooth Edge (Gerigi Kertas Thermal) */}
                        <div className="w-full overflow-hidden leading-none print:hidden -mb-px">
                            <svg className="w-full h-2.5 text-white fill-current" viewBox="0 0 240 10" preserveAspectRatio="none">
                                <path d="M0,10 L5,0 L10,10 L15,0 L20,10 L25,0 L30,10 L35,0 L40,10 L45,0 L50,10 L55,0 L60,10 L65,0 L70,10 L75,0 L80,10 L85,0 L90,10 L95,0 L100,10 L105,0 L110,10 L115,0 L120,10 L125,0 L130,10 L135,0 L140,10 L145,0 L150,10 L155,0 L160,10 L165,0 L170,10 L175,0 L180,10 L185,0 L190,10 L195,0 L200,10 L205,0 L210,10 L215,0 L220,10 L225,0 L230,10 L235,0 L240,10 Z" />
                            </svg>
                        </div>

                        {/* Printable Pure Black & White Thermal Receipt Paper */}
                        <div 
                            id="thermalReceiptModalPrintArea"
                            ref={receiptRef}
                            className="w-full bg-white px-5 py-4 text-black font-mono text-[11px] leading-tight selection:bg-stone-300 shadow-md print:p-0 print:shadow-none print:max-w-none print:w-full"
                            style={{ fontFamily: "'Courier New', Courier, Consolas, Monaco, monospace" }}
                        >
                            
                            {/* 0. Logo Toko (Strict Hitam Putih / Thermal Grayscale Monokrom) */}
                            {storeLogo && storeProfile.cetakLogoStruk !== false && (
                                <div className="flex justify-center pb-2.5">
                                    <img 
                                        src={storeLogo} 
                                        alt="Logo Struk (B&W)" 
                                        crossOrigin="anonymous"
                                        className="max-h-14 max-w-20 object-contain mx-auto"
                                        style={{
                                            filter: 'grayscale(100%) contrast(180%) brightness(85%)',
                                            WebkitFilter: 'grayscale(100%) contrast(180%) brightness(85%)',
                                            mixBlendMode: 'multiply'
                                        }}
                                    />
                                </div>
                            )}

                            {/* 1. Header Toko (Center) */}
                            <div className="text-center space-y-0.5 pb-2">
                                <div className="font-bold text-[14px] tracking-wide text-black uppercase">
                                    {storeProfile.namaToko || 'Kedai Kopi Dinasty'}
                                </div>
                                {storeProfile.slogan && (
                                    <div className="text-[10px] text-black tracking-wider uppercase font-semibold">
                                        {storeProfile.slogan}
                                    </div>
                                )}
                                <div className="text-[10px] text-black leading-snug px-1">
                                    {storeProfile.alamat || 'Jl. Jambangan Kebon Agung No. 12 B, Jambangan, Kec. Jambangan, Surabaya, Jawa Timur 60232'}
                                </div>
                                {storeProfile.telepon && (
                                    <div className="text-[9.5px] text-black">
                                        Telp/WA: {storeProfile.telepon}
                                    </div>
                                )}
                            </div>

                            {/* Dashed Line */}
                            <div className="border-t border-dashed border-black my-2"></div>

                            {/* 2. Metadata Transaksi (Sesuai Foto Asli) */}
                            <div className="space-y-0.5 text-[11px]">
                                <div className="flex justify-between items-center">
                                    <span>Pembeli</span>
                                    <span className="font-bold">{customerName}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span>Pembayaran</span>
                                    <span>{paymentMethod}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span>Tanggal</span>
                                    <span>{formatDateReceipt(order.timestamp || order.created_at)}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span>No Struk</span>
                                    <span className="font-bold">{receiptNumber}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span>Kasir</span>
                                    <span>{cashierName}</span>
                                </div>
                            </div>

                            {/* Dashed Line */}
                            <div className="border-t border-dashed border-black my-2"></div>

                            {/* 3. Daftar Item Pesanan (Sesuai Format Foto Asli) */}
                            <div className="space-y-2 py-0.5">
                                {items.length === 0 ? (
                                    <div className="space-y-0.5">
                                        <div className="text-black font-bold">Pesanan Menu</div>
                                        <div className="flex justify-between">
                                            <span>{formatNumber(grandTotal)} x 1</span>
                                            <span>{formatNumber(grandTotal)}</span>
                                        </div>
                                    </div>
                                ) : (
                                    items.map((item, idx) => {
                                        const itemQty = parseInt(item.quantity || item.jumlah, 10) || 1;
                                        const itemPrice = item.unitPrice || item.harga || (item.totalPrice ? Math.round(item.totalPrice / itemQty) : 0);
                                        const itemSubtotal = item.totalPrice || (itemPrice * itemQty);
                                        const itemName = item.menuItem?.nama || item.nama_produk || item.nama || 'Menu';

                                        return (
                                            <div key={idx} className="space-y-0.5">
                                                <div className="text-black font-bold">
                                                    {itemName}
                                                </div>
                                                <div className="flex justify-between">
                                                    <span>{formatNumber(itemPrice)} x {itemQty}</span>
                                                    <span className="font-bold">{formatNumber(itemSubtotal)}</span>
                                                </div>

                                                {/* Toppings / Modifiers */}
                                                {item.customizations?.modifiers && item.customizations.modifiers.length > 0 && (
                                                    <div className="pl-2 text-[10px] text-stone-800">
                                                        {item.customizations.modifiers.map((m, mIdx) => (
                                                            <div key={mIdx}>+ {m.nama || m.name}</div>
                                                        ))}
                                                    </div>
                                                )}

                                                {/* Special Notes */}
                                                {item.notes && (
                                                    <div className="pl-2 text-[10px] text-stone-800 italic">
                                                        * Catatan: {item.notes}
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })
                                )}
                            </div>

                            {/* Dashed Line */}
                            <div className="border-t border-dashed border-black my-2"></div>

                            {/* 4. Total Bayar & Kembalian (Sesuai Foto Asli) */}
                            <div className="space-y-0.5 text-[11px]">
                                <div className="flex justify-between font-bold text-[11.5px]">
                                    <span>TOTAL {totalQty} QTY</span>
                                    <span>{formatNumber(grandTotal)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Bayar</span>
                                    <span>{formatNumber(grandTotal)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span>Kembali</span>
                                    <span>0</span>
                                </div>
                            </div>

                            {/* Dashed Line */}
                            <div className="border-t border-dashed border-black my-2"></div>

                            {/* 5. Informasi WiFi Dinamis (Sesuai Profil Toko) */}
                            <div className="space-y-0.5 text-[10px] leading-tight pt-0.5">
                                {storeProfile.wifiList && storeProfile.wifiList.length > 0 ? (
                                    storeProfile.wifiList.map((w, idx) => (
                                        <div key={idx}>
                                            <div>Wifi : {w.ssid}</div>
                                            <div>Pass : {w.password}</div>
                                        </div>
                                    ))
                                ) : (
                                    <>
                                        <div>Wifi : KEDAI DINASTY 5G</div>
                                        <div>Pass : wargadinasty</div>
                                        <div>Wifi : KEDAI DINASTY LT 2</div>
                                        <div>Pass : cobatanyabarista</div>
                                    </>
                                )}
                            </div>

                            {/* 6. Pesan Footer Struk (Penutup / Ucapan Terima Kasih) */}
                            {storeProfile.pesanFooterStruk && (
                                <div className="text-center pt-2.5 text-[10px] text-black leading-snug whitespace-pre-line">
                                    {storeProfile.pesanFooterStruk}
                                </div>
                            )}

                            {/* 7. Footer Link / Sosmed Toko */}
                            <div className="text-center pt-2 pb-1 text-[11px] tracking-wide text-black font-semibold">
                                {storeProfile.sosmed || 'kedaikopidinasty.tokoa.id'}
                            </div>

                        </div>

                        {/* Bottom Paper Tear Sawtooth Edge (Gerigi Kertas Thermal) */}
                        <div className="w-full overflow-hidden leading-none print:hidden -mt-px rotate-180">
                            <svg className="w-full h-2.5 text-white fill-current" viewBox="0 0 240 10" preserveAspectRatio="none">
                                <path d="M0,10 L5,0 L10,10 L15,0 L20,10 L25,0 L30,10 L35,0 L40,10 L45,0 L50,10 L55,0 L60,10 L65,0 L70,10 L75,0 L80,10 L85,0 L90,10 L95,0 L100,10 L105,0 L110,10 L115,0 L120,10 L125,0 L130,10 L135,0 L140,10 L145,0 L150,10 L155,0 L160,10 L165,0 L170,10 L175,0 L180,10 L185,0 L190,10 L195,0 L200,10 L205,0 L210,10 L215,0 L220,10 L225,0 L230,10 L235,0 L240,10 Z" />
                            </svg>
                        </div>

                    </div>

                </div>

                {/* Success Notification Alert */}
                {downloadSuccess && (
                    <div className="mx-3.5 mt-2 p-2.5 rounded-xl bg-emerald-950/90 border border-emerald-500/40 flex items-center gap-2 text-emerald-300 text-xs font-mono animate-fade-in shrink-0">
                        <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                        <span>Struk berhasil didownload ke galeri / penyimpanan!</span>
                    </div>
                )}

                {/* Modal Footer Controls - Hidden on Print */}
                <div className="p-3.5 bg-stone-900 border-t border-stone-800 flex items-center gap-2 shrink-0 print:hidden">
                    <button
                        type="button"
                        disabled={isDownloading}
                        onClick={handleDownloadReceipt}
                        className="flex-1 py-2.5 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 active:scale-95 text-stone-950 font-mono font-bold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-md disabled:opacity-75 disabled:cursor-not-allowed"
                    >
                        {isDownloading ? (
                            <>
                                <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                <span>Menyimpan ke Galeri...</span>
                            </>
                        ) : downloadSuccess ? (
                            <>
                                <CheckCircle2 className="w-3.5 h-3.5 text-stone-950" />
                                <span>Tersimpan di Galeri!</span>
                            </>
                        ) : (
                            <>
                                <Download className="w-3.5 h-3.5 text-stone-950" />
                                <span>Download Struk (PNG)</span>
                            </>
                        )}
                    </button>
                    
                    {/* Share Button (WhatsApp / Galeri) if supported */}
                    {typeof navigator !== 'undefined' && typeof navigator.share === 'function' && (
                        <button
                            type="button"
                            disabled={isDownloading}
                            onClick={handleShareReceipt}
                            className="p-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 hover:text-white transition active:scale-95 cursor-pointer disabled:opacity-50"
                            title="Bagikan Struk (WhatsApp / Lainnya)"
                        >
                            <Share2 className="w-4 h-4" />
                        </button>
                    )}

                    {/* Thermal Physical Print Fallback */}
                    <button
                        type="button"
                        onClick={handlePrint}
                        className="p-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-300 hover:text-white transition active:scale-95 cursor-pointer"
                        title="Cetak ke Mesin Printer Thermal"
                    >
                        <Printer className="w-4 h-4" />
                    </button>
                    
                    <button
                        type="button"
                        onClick={onClose}
                        className="py-2.5 px-3.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 font-mono font-bold text-xs active:scale-95 transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    );
}
