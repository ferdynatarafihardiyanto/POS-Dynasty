import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, ArrowRight, CheckCircle2, Clock, ChefHat, BellRing, Sparkles, ReceiptText, Plus, QrCode, Zap } from 'lucide-react';
import CustomerPayModal from './CustomerPayModal';
import CustomerReceiptModal from './CustomerReceiptModal';

export default function OrderStatusModal() {
    const {
        isOrderStatusOpen,
        setIsOrderStatusOpen,
        setIsOrderHistoryOpen,
        isCartOpen,
        setIsCartOpen,
        cartItems,
        setCartItems,
        menuList,
        selectedDetailItem,
        setSelectedDetailItem,
        activeOrder,
        orders,
        setActiveOrder,
        tableInfo,
        clearDeviceSession
    } = useCart();

    const [isPayModalOpen, setIsPayModalOpen] = useState(false);
    const [isReceiptModalOpen, setIsReceiptModalOpen] = useState(false);

    if (!isOrderStatusOpen) return null;

    const currentOrder = activeOrder || orders[0];

    const formatRupiah = (num) => {
        const val = parseFloat(num) || 0;
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(val).replace('IDR', 'Rp');
    };

    const formatSafeTime = (ts) => {
        try {
            if (!ts) return '';
            const cleanTs = typeof ts === 'string' ? ts.replace(' ', 'T') : ts;
            const d = new Date(cleanTs);
            if (isNaN(d.getTime())) return '';
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
        } catch {
            return '';
        }
    };

    const handleOpenPayModal = () => {
        setIsPayModalOpen(true);
    };

    const openItemDetail = (item) => {
        if (!item) return;
        const pId = item.menuItem?.backend_id || item.menuItem?.id || item.produk_id;
        const pName = (item.menuItem?.nama || item.nama_produk || '').toLowerCase();
        const fullItem = (menuList || []).find(m => 
            (pId && (m.id == pId || m.backend_id == pId)) || 
            (pName && m.nama?.toLowerCase() === pName)
        );

        const itemToOpen = {
            ...(fullItem || item.menuItem || {}),
            id: fullItem?.id || pId || 1,
            nama: fullItem?.nama || item.menuItem?.nama || item.nama_produk || 'Menu',
            harga: fullItem?.harga || item.unitPrice || item.harga || 0,
            gambar: fullItem?.gambar || item.menuItem?.gambar || item.menuItem?.gambar_url || '/images/produk/americano.jpg',
            initialQuantity: item.quantity || item.jumlah || 1,
            initialNotes: item.notes || item.catatan || '',
            initialModifiers: item.customizations?.modifiers || []
        };

        setIsOrderStatusOpen(false);
        setSelectedDetailItem(itemToOpen);
    };

    const handleBack = () => {
        if (!isPaid && currentOrder) {
            if (currentOrder.items && currentOrder.items.length > 0) {
                const itemsToRestore = currentOrder.items.map((it, idx) => ({
                    ...it,
                    cartItemId: it.cartItemId || `cart-restored-${Date.now()}-${idx}`,
                    menuItem: {
                        ...it.menuItem,
                        gambar: it.menuItem?.gambar || it.menuItem?.gambar_url || it.gambar_url || '/images/produk/americano.jpg',
                        nama: it.menuItem?.nama || it.nama_produk || 'Menu',
                        harga: it.menuItem?.harga || it.unitPrice || 0
                    },
                    customizations: it.customizations || { modifiers: [] }
                }));
                setCartItems(itemsToRestore);

                // Open Detail Menu (Gambar 2) of the item!
                openItemDetail(currentOrder.items[0]);
                return;
            }
        }
        setIsOrderStatusOpen(false);
    };

    const isPaid = (currentOrder?.status_pembayaran === 'dibayar') ||
                   (currentOrder?.status === 'dibayar') ||
                   (currentOrder?.status === 'disajikan') ||
                   (currentOrder?.status === 'selesai');

    const statusSteps = [
        { 
            key: 'menunggu_pembayaran', 
            label: '1. Pembayaran HP (QRIS / TF)', 
            desc: isPaid 
                ? 'Pembayaran lunas terverifikasi ✅' 
                : 'Silakan selesaikan pembayaran via QRIS atau Transfer Bank (TF) di HP', 
            icon: ReceiptText 
        },
        { 
            key: 'diproses', 
            label: '2. Sedang Dimasak di Dapur', 
            desc: isPaid 
                ? 'Koki sedang menyiapkan hidangan lezatmu' 
                : 'Dapur akan mulai memasak setelah pembayaran diverifikasi', 
            icon: ChefHat 
        },
        { 
            key: 'disajikan', 
            label: '3. Pesanan Sudah Diterima di Meja', 
            desc: (currentOrder?.status === 'disajikan' || currentOrder?.status === 'selesai')
                ? 'Pelayan telah mengantarkan pesanan ke mejamu. Makanan sudah diterima! 🍽️'
                : 'Pelayan sedang menyiapkan pengantaran makanan ke mejamu', 
            icon: BellRing 
        },
        { 
            key: 'selesai', 
            label: '4. Selesai / Menikmati Makanan', 
            desc: currentOrder?.status === 'selesai'
                ? 'Pesanan telah selesai dinikmati. Terima kasih telah berkunjung ke Kedai Dynasty! ✨'
                : 'Selamat menikmati hidangan lezat Kedai Dynasty!', 
            icon: Sparkles 
        }
    ];

    const getCurrentStepIndex = () => {
        if (!currentOrder) return 0;
        const status = (currentOrder.status || 'menunggu_pembayaran').toLowerCase();
        
        // Jika belum bayar, selalu di Step 0 (Pembayaran di Kasir)
        if (!isPaid && (status === 'menunggu_pembayaran' || status === 'menunggu_konfirmasi')) {
            return 0;
        }

        // Jika sudah bayar
        if (status === 'selesai') return 3;
        if (status === 'disajikan') return 2;
        if (status === 'diproses' || status === 'dibayar') return 1;

        return 1;
    };

    const activeStepIdx = getCurrentStepIndex();

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[92vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <button
                        onClick={handleBack}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95"
                        title="Kembali ke Keranjang Pesanan"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>
                    <div className="text-center">
                        <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                            Status Pesanan
                        </h2>
                        <p className="text-[11px] font-medium text-[#881B1E]">
                            Dine-in • Meja {currentOrder?.tableNumber || tableInfo.number}
                        </p>
                    </div>
                    <button
                        onClick={() => {
                            setIsOrderStatusOpen(false);
                            setIsOrderHistoryOpen(true);
                        }}
                        className="px-2 py-1 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold flex items-center gap-1 transition"
                        title="Lihat Semua Riwayat Pesanan Meja"
                    >
                        <ReceiptText className="w-3.5 h-3.5" />
                        <span className="text-[11px]">Riwayat</span>
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4 pb-28">
                    {!currentOrder ? (
                        <div className="py-16 text-center">
                            <Clock className="w-12 h-12 text-stone-300 mx-auto mb-2" />
                            <h3 className="font-display font-bold text-stone-700 text-sm">Belum Ada Pesanan Aktif</h3>
                            <p className="text-xs text-stone-400 mt-1">Silakan pilih hidangan di menu dan kirim pesanan.</p>
                        </div>
                    ) : (
                        <>
                            {/* Order Number Banner */}
                            <div className="bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] uppercase font-bold text-amber-300 block tracking-wider">
                                        Nomor Pesanan
                                    </span>
                                    <h3 className="font-display font-extrabold text-lg text-white">
                                        #{currentOrder.orderNumber}
                                    </h3>
                                    <span className="text-[11px] text-amber-200/80">
                                        {formatSafeTime(currentOrder.timestamp || Date.now())}
                                    </span>
                                </div>
                                <div className="text-right">
                                    <span className="text-[10px] font-bold text-amber-300 block">
                                        Meja
                                    </span>
                                    <span className="font-display font-black text-2xl text-white">
                                        {currentOrder.tableNumber || tableInfo?.number || '-'}
                                    </span>
                                </div>
                            </div>

                            {/* Payment Status Card (Khusus HP: QRIS / TF) */}
                            {!isPaid ? (
                                <div className="p-4 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50/60 border-2 border-amber-300 shadow-xs space-y-3">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <Clock className="w-5 h-5 text-amber-600 shrink-0 animate-spin" />
                                            <span className="font-display font-extrabold text-xs text-amber-950 uppercase tracking-wide">
                                                Langkah 1: Bayar via Midtrans Snap
                                            </span>
                                        </div>
                                        <span className="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-400 text-stone-950 shadow-2xs">
                                            Belum Bayar
                                        </span>
                                    </div>

                                    <p className="text-xs text-amber-900 leading-relaxed">
                                        Pesananmu telah dibuat! Silakan selesaikan pembayaran langsung lewat HP via <strong>Midtrans Snap (QRIS / VA Bank)</strong> agar pesanan segera dimasak oleh dapur.
                                    </p>

                                    {/* Tombol Utama Bayar dari HP */}
                                    <button
                                        type="button"
                                        onClick={handleOpenPayModal}
                                        className="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-700 to-emerald-800 hover:from-emerald-500 hover:to-emerald-600 text-white font-display font-extrabold text-xs shadow-lg shadow-emerald-800/20 active:scale-95 transition flex items-center justify-center gap-2 group cursor-pointer"
                                    >
                                        <Zap className="w-4 h-4 text-amber-300 fill-amber-300 group-hover:scale-110 transition-transform" />
                                        <span>Bayar via Midtrans Snap (QRIS / VA)</span>
                                        <ArrowRight className="w-4 h-4 text-emerald-200 group-hover:translate-x-0.5 transition-transform" />
                                    </button>

                                    <div className="text-[11px] text-amber-800/80 flex items-center gap-1.5 pt-1 border-t border-amber-200/60">
                                        <Sparkles className="w-3.5 h-3.5 text-amber-600 shrink-0" />
                                        <span>Pelacakan langsung proses dapur akan otomatis aktif setelah pembayaran.</span>
                                    </div>
                                </div>
                            ) : (
                                currentOrder.status === 'selesai' ? (
                                    <div className="p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-400 text-emerald-950 shadow-xs space-y-2">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <span className="text-2xl">🎉</span>
                                                <span className="font-display font-black text-xs text-emerald-950 uppercase tracking-wide">
                                                    Pesanan Telah Selesai!
                                                </span>
                                            </div>
                                            <span className="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase bg-emerald-600 text-white shadow-2xs">
                                                Selesai
                                            </span>
                                        </div>
                                        <p className="text-xs text-emerald-900 leading-relaxed">
                                            Terima kasih banyak telah berkunjung ke Kedai Dynasty. Transaksi pesanan Meja {currentOrder.tableNumber} telah selesai dinikmati. Sampai jumpa kembali! ✨
                                        </p>
                                    </div>
                                ) : currentOrder.status === 'disajikan' ? (
                                    <div className="p-4 rounded-2xl bg-gradient-to-br from-teal-50 to-emerald-50 border-2 border-teal-400 text-teal-950 shadow-xs space-y-2">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <span className="text-2xl">🍽️</span>
                                                <span className="font-display font-black text-xs text-teal-950 uppercase tracking-wide">
                                                    Pesanan Sudah Diterima di Meja!
                                                </span>
                                            </div>
                                            <span className="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase bg-teal-600 text-white shadow-2xs">
                                                Sudah Tiba
                                            </span>
                                        </div>
                                        <p className="text-xs text-teal-900 leading-relaxed">
                                            Pelayan telah mengantarkan pesanan ke <strong>Meja {currentOrder.tableNumber}</strong>. Makanan sudah diterima, selamat menikmati hidangan lezatmu!
                                        </p>
                                    </div>
                                ) : (
                                    <div className="p-3.5 rounded-2xl bg-emerald-50/95 border border-emerald-200 text-emerald-900 shadow-2xs space-y-1">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2.5">
                                                <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
                                                <div>
                                                    <span className="text-xs font-bold block">Status: Pembayaran Lunas ✅</span>
                                                    <span className="text-[10px] text-emerald-700">Pembayaran telah diterima. Koki sedang menyiapkan pesananmu.</span>
                                                </div>
                                            </div>
                                            <span className="px-2.5 py-1 rounded-xl text-[10px] font-extrabold uppercase bg-emerald-600 text-white shadow-2xs">
                                                Lunas
                                            </span>
                                        </div>
                                    </div>
                                )
                            )}

                            {/* Stepper Tracking Progress - Hanya muncul setelah pembayaran lunas */}
                            {isPaid && (
                                <div className="bg-white p-4 rounded-2xl border border-stone-200/80 space-y-4 animate-fade-in">
                                    <h4 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                        Pelacakan Langsung
                                    </h4>

                                    <div className="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-stone-200 before:z-0">
                                        {statusSteps.map((step, idx) => {
                                            const StepIcon = step.icon;
                                            const isCompleted = idx <= activeStepIdx;
                                            const isCurrent = idx === activeStepIdx;
                                            const isAllFinished = currentOrder.status === 'selesai';

                                            return (
                                                <div key={step.key} className="flex items-start gap-3 relative z-10">
                                                    <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs transition-colors shrink-0 ${
                                                        isCompleted
                                                            ? (isAllFinished && step.key === 'selesai'
                                                                ? 'bg-emerald-600 text-white font-black shadow-sm ring-4 ring-emerald-100'
                                                                : 'bg-amber-400 text-stone-950 font-black shadow-sm ring-4 ring-amber-100')
                                                            : 'bg-stone-100 text-stone-400'
                                                    }`}>
                                                        <StepIcon className="w-3.5 h-3.5" />
                                                    </div>

                                                    <div className="flex-1">
                                                        <div className="flex items-center justify-between">
                                                            <span className={`font-display text-xs ${
                                                                isCurrent
                                                                    ? (isAllFinished && step.key === 'selesai'
                                                                        ? 'font-extrabold text-emerald-800'
                                                                        : 'font-extrabold text-[#881B1E]')
                                                                    : isCompleted
                                                                        ? 'font-bold text-stone-800'
                                                                        : 'font-medium text-stone-400'
                                                            }`}>
                                                                {step.label}
                                                            </span>
                                                            {isCurrent && (
                                                                isAllFinished && step.key === 'selesai' ? (
                                                                    <span className="text-[9px] font-extrabold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full flex items-center gap-1">
                                                                        <CheckCircle2 className="w-2.5 h-2.5 text-emerald-600" />
                                                                        Selesai
                                                                    </span>
                                                                ) : (
                                                                    <span className="text-[9px] font-extrabold bg-amber-100 text-amber-900 px-1.5 py-0.5 rounded-full animate-pulse">
                                                                        Sedang Berlangsung
                                                                    </span>
                                                                )
                                                            )}
                                                        </div>
                                                        <p className="text-[10px] text-stone-500 mt-0.5">
                                                            {step.desc}
                                                        </p>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}

                            {/* Order Details Struk */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/80 space-y-3">
                                <div className="flex items-center justify-between border-b border-stone-100 pb-2">
                                    <div className="flex items-center gap-2">
                                        <ReceiptText className="w-4 h-4 text-[#881B1E]" />
                                        <h4 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                            Rincian Menu Dipesan
                                        </h4>
                                    </div>
                                    {isPaid && (
                                        <button
                                            type="button"
                                            onClick={() => setIsReceiptModalOpen(true)}
                                            className="text-[11px] font-bold text-[#881B1E] bg-red-50 hover:bg-red-100 border border-red-200/80 px-2.5 py-1 rounded-xl transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer"
                                            title="Buka Nota Struk Digital"
                                        >
                                            <ReceiptText className="w-3.5 h-3.5" />
                                            <span>Nota Struk</span>
                                        </button>
                                    )}
                                </div>

                                <div className="space-y-2.5 divide-y divide-stone-100 text-xs">
                                    {currentOrder.items?.map((item, idx) => (
                                        <div
                                            key={idx}
                                            onClick={() => !isPaid && openItemDetail(item)}
                                            className={`pt-2 first:pt-0 flex justify-between gap-2 ${!isPaid ? 'cursor-pointer hover:bg-stone-100/70 p-1.5 -mx-1.5 rounded-xl transition' : ''}`}
                                            title={!isPaid ? 'Klik untuk melihat / ubah detail menu' : undefined}
                                        >
                                            <div className="flex-1">
                                                <div className="font-bold text-stone-800">
                                                    {item.quantity}x {item.menuItem?.nama || item.nama_produk || 'Menu'}
                                                </div>

                                                {/* Modifiers / Variants */}
                                                {item.customizations?.modifiers && item.customizations.modifiers.length > 0 && (
                                                    <div className="flex flex-wrap gap-1 mt-0.5">
                                                        {item.customizations.modifiers.map(m => (
                                                            <span key={m.id} className="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.2 rounded font-medium">
                                                                + {m.nama}
                                                            </span>
                                                        ))}
                                                    </div>
                                                )}

                                                {/* Item Note */}
                                                {item.notes && (
                                                    <div className="text-[10px] text-amber-800 italic">
                                                        "{item.notes}"
                                                    </div>
                                                )}
                                            </div>
                                            <span className="font-bold text-stone-900 shrink-0">
                                                {formatRupiah(item.totalPrice ?? ((item.unitPrice || item.harga || 0) * (item.quantity || item.jumlah || 1)) ?? 0)}
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                <div className="pt-2 border-t border-dashed border-stone-300 space-y-1 text-xs text-stone-600">
                                    <div className="flex justify-between">
                                        <span>Subtotal</span>
                                        <span>{formatRupiah(currentOrder.subtotal || currentOrder.grandTotal)}</span>
                                    </div>
                                    {currentOrder.tax > 0 && (
                                        <div className="flex justify-between">
                                            <span>Pajak Restoran PB1 (10%)</span>
                                            <span>{formatRupiah(currentOrder.tax)}</span>
                                        </div>
                                    )}
                                    <div className="flex justify-between font-display font-black text-sm text-[#881B1E] pt-1">
                                        <span>Total Bayar</span>
                                        <span>{formatRupiah(currentOrder.grandTotal)}</span>
                                    </div>
                                    {(() => {
                                        let displayNote = currentOrder.notes || '';
                                        if (displayNote.startsWith('Pemesan:')) {
                                            const parts = displayNote.split('|');
                                            displayNote = parts.length > 1 ? parts.slice(1).join('|').trim() : '';
                                        }
                                        if (!displayNote) return null;
                                        return (
                                            <div className="mt-2 pt-2 border-t border-stone-100 bg-amber-50/70 p-2 rounded-xl text-[11px] text-amber-900">
                                                <span className="font-bold">📝 Catatan:</span> "{displayNote}"
                                            </div>
                                        );
                                    })()}
                                </div>
                            </div>
                        </>
                    )}
                </div>

                {/* Bottom Action Floating */}
                <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl flex items-center gap-2">
                    {currentOrder?.status === 'selesai' ? (
                        <div className="flex items-center gap-2 w-full">
                            <button
                                type="button"
                                onClick={() => setIsReceiptModalOpen(true)}
                                className="py-3 px-3.5 rounded-2xl bg-amber-100 hover:bg-amber-200 text-amber-950 font-display font-bold text-xs shadow-xs active:scale-95 transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
                                title="Lihat Nota Struk"
                            >
                                <ReceiptText className="w-4 h-4 text-[#881B1E]" />
                                <span>Nota Struk</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setIsOrderStatusOpen(false);
                                    setIsOrderHistoryOpen(true);
                                }}
                                className="py-3 px-3 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-800 font-display font-bold text-xs shadow-xs active:scale-95 transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
                            >
                                <span>Riwayat</span>
                            </button>

                            <button
                                type="button"
                                onClick={clearDeviceSession}
                                className="flex-1 py-3 px-3 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-700 to-emerald-800 hover:from-emerald-500 hover:to-emerald-600 text-white font-display font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5 cursor-pointer"
                                title="Selesai bersantap dan mulai sesi pesanan baru"
                            >
                                <Plus className="w-4 h-4" />
                                <span>Selesai & Pesan Baru</span>
                            </button>
                        </div>
                    ) : isPaid ? (
                        <div className="flex items-center gap-2 w-full">
                            <button
                                type="button"
                                onClick={() => setIsReceiptModalOpen(true)}
                                className="py-3 px-3.5 rounded-2xl bg-amber-100 hover:bg-amber-200 text-amber-950 font-display font-bold text-xs shadow-xs active:scale-95 transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
                                title="Lihat Nota Struk"
                            >
                                <ReceiptText className="w-4 h-4 text-[#881B1E]" />
                                <span>Nota Struk</span>
                            </button>

                            <button
                                type="button"
                                onClick={() => setIsOrderStatusOpen(false)}
                                className="flex-1 py-3.5 px-4 rounded-2xl bg-[#881B1E] hover:bg-[#731417] text-white font-display font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5"
                            >
                                <Plus className="w-4 h-4" />
                                <span>Pesan Menu Tambahan</span>
                            </button>
                        </div>
                    ) : (
                        <button
                            type="button"
                            onClick={() => setIsOrderStatusOpen(false)}
                            className="w-full py-3.5 px-4 rounded-2xl bg-[#881B1E] hover:bg-[#731417] text-white font-display font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5"
                        >
                            <Plus className="w-4 h-4" />
                            <span>Pesan Menu Tambahan</span>
                        </button>
                    )}
                </div>

            </div>

            {/* Customer Online QRIS & Transfer Payment Modal */}
            <CustomerPayModal
                isOpen={isPayModalOpen}
                onClose={() => setIsPayModalOpen(false)}
                order={currentOrder}
            />

            {/* Customer Digital Receipt / Nota Struk Modal */}
            <CustomerReceiptModal
                isOpen={isReceiptModalOpen}
                onClose={() => setIsReceiptModalOpen(false)}
                order={currentOrder}
            />
        </div>
    );
}
