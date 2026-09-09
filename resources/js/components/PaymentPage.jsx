import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { 
    ArrowLeft, 
    QrCode, 
    CreditCard, 
    Building2, 
    CheckCircle2, 
    Copy, 
    ShieldCheck, 
    ReceiptText, 
    Sparkles, 
    ChevronDown, 
    ChevronUp,
    Smartphone,
    Check,
    Clock,
    ShoppingBag,
    Utensils,
    ArrowRight
} from 'lucide-react';

export default function PaymentPage({ isOpen, onClose, buyerName, orderNotes }) {
    const { 
        cartItems, 
        subtotal, 
        taxPB1, 
        grandTotal, 
        totalItemCount, 
        tableInfo, 
        submitOrder,
        showToast,
        setIsOrderStatusOpen
    } = useCart();

    const [paymentMethod, setPaymentMethod] = useState('qris'); // 'qris' | 'transfer' | 'other_bank'
    const [selectedBank, setSelectedBank] = useState('BCA');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isSummaryExpanded, setIsSummaryExpanded] = useState(false);
    const [copiedText, setCopiedText] = useState(null);
    const [successOrderData, setSuccessOrderData] = useState(null);

    if (!isOpen) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num || 0).replace('IDR', 'Rp');
    };

    const handleCopy = (text, label) => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
            setCopiedText(text);
            showToast(`${label} berhasil disalin ke clipboard! 📋`, 'info');
            setTimeout(() => setCopiedText(null), 2500);
        }
    };

    const handlePayNow = async () => {
        setIsSubmitting(true);
        try {
            // Combine buyer name and order notes safely into the existing order note parameter
            const combinedNote = buyerName?.trim() 
                ? `[Pemesan: ${buyerName.trim()}] ${orderNotes || ''}`.trim()
                : (orderNotes || '');

            const result = await submitOrder(combinedNote);
            if (result) {
                // Set success state to show FLOW 4 — Payment Success
                setSuccessOrderData(result);
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleViewOrderStatus = () => {
        setSuccessOrderData(null);
        onClose();
        setIsOrderStatusOpen(true);
    };

    const handleCloseSuccess = () => {
        setSuccessOrderData(null);
        onClose();
    };

    const vaNumbers = {
        BCA: '8271 0812 3456 7890',
        Mandiri: '8890 0812 3456 7890',
        BRI: '1289 0812 3456 7890',
        BNI: '9881 0812 3456 7890',
    };

    // =========================================================================
    // FLOW 4 — PAYMENT SUCCESS SCREEN
    // =========================================================================
    if (successOrderData) {
        return (
            <div className="fixed inset-0 z-50 bg-stone-950/75 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
                <div className="w-full max-w-md h-[92vh] sm:h-[86vh] bg-[#FFFDF9] rounded-t-[32px] sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative border-t sm:border border-amber-500/20">

                    {/* Top Header */}
                    <div className="bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] text-white px-4 py-3.5 flex items-center justify-between shadow-md">
                        <div className="flex items-center gap-2">
                            <span className="font-display font-extrabold text-sm text-white">
                                Kedai Dynasty
                            </span>
                            <span className="text-[11px] text-amber-200">
                                • Dine-In Meja {successOrderData.tableNumber || tableInfo?.number || '01'}
                            </span>
                        </div>
                        <button
                            onClick={handleCloseSuccess}
                            className="text-xs font-bold text-amber-200 hover:text-white px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>

                    {/* Body */}
                    <div className="flex-1 overflow-y-auto no-scrollbar p-5 space-y-5 text-center">
                        {/* Success Icon */}
                        <div className="pt-4">
                            <div className="w-20 h-20 mx-auto rounded-full bg-emerald-100 border-4 border-emerald-400/40 flex items-center justify-center text-emerald-600 shadow-lg shadow-emerald-500/10 animate-bounce" style={{ animationDuration: '2s' }}>
                                <CheckCircle2 className="w-12 h-12" />
                            </div>
                            <h3 className="font-display font-black text-xl text-stone-900 mt-4">
                                Pembayaran Berhasil!
                            </h3>
                            <p className="text-xs text-stone-500 mt-1 max-w-xs mx-auto">
                                Pesanan Anda telah diterima dan langsung masuk ke antrean dapur Kedai Dynasty.
                            </p>
                        </div>

                        {/* Order Highlight Card */}
                        <div className="bg-white rounded-2xl border border-stone-200/90 p-4 shadow-sm text-left space-y-3">
                            <div className="flex justify-between items-center pb-2.5 border-b border-stone-100">
                                <div>
                                    <span className="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">
                                        Nomor Pesanan
                                    </span>
                                    <span className="font-display font-black text-base text-[#82181A]">
                                        #{successOrderData.orderNumber}
                                    </span>
                                </div>
                                <div className="text-right">
                                    <span className="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">
                                        Meja
                                    </span>
                                    <span className="font-display font-extrabold text-sm text-stone-900">
                                        Meja {successOrderData.tableNumber || tableInfo?.number || '01'}
                                    </span>
                                </div>
                            </div>

                            <div className="flex justify-between items-center text-xs text-stone-600">
                                <span>Nama Pembeli:</span>
                                <span className="font-bold text-stone-800">{buyerName || 'Customer'}</span>
                            </div>

                            <div className="flex justify-between items-center text-xs text-stone-600">
                                <span>Waktu Transaksi:</span>
                                <span className="font-medium text-stone-700">
                                    {new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WIB
                                </span>
                            </div>

                            <div className="pt-2 border-t border-stone-100 flex justify-between items-baseline">
                                <span className="font-display font-bold text-xs text-stone-800">Total Pembayaran</span>
                                <span className="font-display font-black text-base text-[#82181A]">
                                    {formatRupiah(successOrderData.grandTotal)}
                                </span>
                            </div>
                        </div>

                        {/* Status Preview Card */}
                        <div className="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-3.5 text-left flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center shrink-0 shadow-xs">
                                <Utensils className="w-5 h-5" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <div className="font-display font-bold text-xs text-stone-900 flex items-center gap-1.5">
                                    <span>Status: Pesanan Diterima</span>
                                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                </div>
                                <p className="text-[10px] text-stone-500 mt-0.5">
                                    Koki & barista kami sedang menyiapkan sajian terbaik untuk Anda.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Sticky Bottom Actions */}
                    <div className="p-4 bg-white/95 backdrop-blur-md border-t border-stone-200/80 shadow-2xl space-y-2">
                        <button
                            onClick={handleViewOrderStatus}
                            className="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] hover:from-[#5B0F11] hover:to-[#82181A] text-white font-display font-extrabold text-xs tracking-wider shadow-lg shadow-red-950/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 uppercase cursor-pointer"
                        >
                            <span>Lihat Status Pesanan</span>
                            <ArrowRight className="w-4 h-4" />
                        </button>
                        <button
                            onClick={handleCloseSuccess}
                            className="w-full py-2.5 text-xs font-bold text-stone-600 hover:text-stone-900 transition cursor-pointer"
                        >
                            Kembali ke Menu Utama
                        </button>
                    </div>

                </div>
            </div>
        );
    }

    // =========================================================================
    // FLOW 3 — SINGLE UNIFIED PAYMENT PAGE
    // =========================================================================
    return (
        <div className="fixed inset-0 z-50 bg-stone-950/70 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[94vh] sm:h-[90vh] bg-[#FFFDF9] rounded-t-[32px] sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative border-t sm:border border-amber-500/20">

                {/* Sticky Header */}
                <div className="sticky top-0 z-20 bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] text-white px-4 py-3.5 flex items-center justify-between shadow-md">
                    <div className="flex items-center gap-3">
                        <button
                            onClick={onClose}
                            className="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition active:scale-95 cursor-pointer"
                            aria-label="Kembali ke keranjang"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </button>
                        <div>
                            <h2 className="font-display font-extrabold text-sm text-white leading-tight">
                                Pembayaran Pesanan
                            </h2>
                            <p className="text-[11px] font-medium text-amber-200">
                                Kedai Dynasty • {tableInfo?.name || `Meja ${tableInfo?.number || '01'}`}
                            </p>
                        </div>
                    </div>

                    <div className="bg-amber-400 text-stone-950 px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider flex items-center gap-1 shadow-sm">
                        <ShieldCheck className="w-3.5 h-3.5" />
                        <span>Aman</span>
                    </div>
                </div>

                {/* Scrollable Body Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4 pb-36">

                    {/* Ringkasan Pesanan & Pembeli */}
                    <div className="bg-white rounded-2xl border border-stone-200/90 p-4 shadow-xs space-y-3">
                        <div className="flex items-center justify-between pb-2.5 border-b border-stone-100">
                            <div>
                                <span className="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">
                                    Nama Pembeli
                                </span>
                                <span className="font-display font-extrabold text-sm text-stone-900">
                                    {buyerName?.trim() || 'Customer Kedai Dynasty'}
                                </span>
                            </div>
                            <div className="text-right">
                                <span className="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">
                                    Meja Dine-In
                                </span>
                                <span className="font-display font-extrabold text-sm text-[#82181A]">
                                    Meja {tableInfo?.number || '01'}
                                </span>
                            </div>
                        </div>

                        {/* Total Nominal Highlight (Otomatis dari Sistem) */}
                        <div className="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-3.5 rounded-xl border border-amber-200/70 flex items-center justify-between">
                            <div>
                                <span className="text-[10px] font-extrabold uppercase tracking-wider text-amber-900 block">
                                    Total Pembayaran
                                </span>
                                <span className="font-display font-black text-xl text-[#82181A]">
                                    {formatRupiah(grandTotal)}
                                </span>
                            </div>
                            <span className="text-[10px] font-bold bg-amber-100 text-amber-900 px-2.5 py-1 rounded-full">
                                {totalItemCount} Porsi
                            </span>
                        </div>

                        {/* Collapsible Order Items Accordion */}
                        <div>
                            <button
                                onClick={() => setIsSummaryExpanded(!isSummaryExpanded)}
                                className="w-full flex items-center justify-between text-xs font-bold text-stone-600 hover:text-stone-900 pt-1 cursor-pointer"
                            >
                                <span className="flex items-center gap-1.5">
                                    <ReceiptText className="w-3.5 h-3.5 text-stone-400" />
                                    <span>Rincian Pesanan ({cartItems.length} menu)</span>
                                </span>
                                {isSummaryExpanded ? (
                                    <ChevronUp className="w-4 h-4 text-stone-400" />
                                ) : (
                                    <ChevronDown className="w-4 h-4 text-stone-400" />
                                )}
                            </button>

                            {isSummaryExpanded && (
                                <div className="mt-2.5 pt-2.5 border-t border-stone-100 space-y-2 text-xs divide-y divide-stone-50 animate-fade-in">
                                    {cartItems.map((item) => (
                                        <div key={item.cartItemId} className="pt-1.5 first:pt-0 flex justify-between gap-2">
                                            <div className="flex-1">
                                                <span className="font-semibold text-stone-800">
                                                    {item.quantity}x {item.menuItem?.nama}
                                                </span>
                                                {item.notes && (
                                                    <span className="block text-[10px] text-amber-900 italic">
                                                        "{item.notes}"
                                                    </span>
                                                )}
                                            </div>
                                            <span className="font-bold text-stone-900 shrink-0">
                                                {formatRupiah(item.totalPrice)}
                                            </span>
                                        </div>
                                    ))}

                                    <div className="pt-2 text-stone-500 text-[11px] space-y-1">
                                        <div className="flex justify-between">
                                            <span>Subtotal</span>
                                            <span>{formatRupiah(subtotal)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span>PB1 Restoran (10%)</span>
                                            <span>{formatRupiah(taxPB1)}</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Section: Pilih Metode Pembayaran */}
                    <div className="space-y-2.5">
                        <div className="flex items-center justify-between px-1">
                            <h3 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                Metode Pembayaran
                            </h3>
                            <span className="text-[10px] font-semibold text-stone-400">
                                Pilih salah satu
                            </span>
                        </div>

                        <div className="grid grid-cols-3 gap-2">
                            {/* Option 1: QRIS */}
                            <button
                                onClick={() => setPaymentMethod('qris')}
                                className={`p-3 rounded-2xl border text-center flex flex-col items-center justify-center gap-1.5 transition-all cursor-pointer ${
                                    paymentMethod === 'qris'
                                        ? 'border-amber-500 bg-amber-50/80 shadow-xs ring-2 ring-amber-400/30 font-bold text-stone-900'
                                        : 'border-stone-200/90 bg-white hover:border-stone-300 text-stone-600'
                                }`}
                            >
                                <div className={`w-8 h-8 rounded-xl flex items-center justify-center ${
                                    paymentMethod === 'qris' ? 'bg-amber-400 text-stone-950' : 'bg-stone-100 text-stone-600'
                                }`}>
                                    <QrCode className="w-4 h-4" />
                                </div>
                                <span className="text-xs font-display font-bold">QRIS</span>
                                <span className="text-[9px] text-amber-800 font-bold bg-amber-100/90 px-1.5 py-0.2 rounded-full">
                                    Instan
                                </span>
                            </button>

                            {/* Option 2: Transfer Bank */}
                            <button
                                onClick={() => setPaymentMethod('transfer')}
                                className={`p-3 rounded-2xl border text-center flex flex-col items-center justify-center gap-1.5 transition-all cursor-pointer ${
                                    paymentMethod === 'transfer'
                                        ? 'border-amber-500 bg-amber-50/80 shadow-xs ring-2 ring-amber-400/30 font-bold text-stone-900'
                                        : 'border-stone-200/90 bg-white hover:border-stone-300 text-stone-600'
                                }`}
                            >
                                <div className={`w-8 h-8 rounded-xl flex items-center justify-center ${
                                    paymentMethod === 'transfer' ? 'bg-amber-400 text-stone-950' : 'bg-stone-100 text-stone-600'
                                }`}>
                                    <CreditCard className="w-4 h-4" />
                                </div>
                                <span className="text-xs font-display font-bold">Transfer</span>
                                <span className="text-[9px] text-stone-500 font-medium">
                                    Virtual Acc
                                </span>
                            </button>

                            {/* Option 3: Bank Lain */}
                            <button
                                onClick={() => setPaymentMethod('other_bank')}
                                className={`p-3 rounded-2xl border text-center flex flex-col items-center justify-center gap-1.5 transition-all cursor-pointer ${
                                    paymentMethod === 'other_bank'
                                        ? 'border-amber-500 bg-amber-50/80 shadow-xs ring-2 ring-amber-400/30 font-bold text-stone-900'
                                        : 'border-stone-200/90 bg-white hover:border-stone-300 text-stone-600'
                                }`}
                            >
                                <div className={`w-8 h-8 rounded-xl flex items-center justify-center ${
                                    paymentMethod === 'other_bank' ? 'bg-amber-400 text-stone-950' : 'bg-stone-100 text-stone-600'
                                }`}>
                                    <Building2 className="w-4 h-4" />
                                </div>
                                <span className="text-xs font-display font-bold">Bank Lain</span>
                                <span className="text-[9px] text-stone-500 font-medium">
                                    ATM / Antar Bank
                                </span>
                            </button>
                        </div>
                    </div>

                    {/* Method 1: QRIS Display */}
                    {paymentMethod === 'qris' && (
                        <div className="bg-white rounded-2xl border border-stone-200/90 p-4 shadow-xs text-center space-y-3 animate-fade-in">
                            <div className="flex items-center justify-center gap-2">
                                <span className="font-display font-black text-xs text-stone-800 tracking-wider uppercase">
                                    QRIS Standar Pembayaran Nasional
                                </span>
                            </div>

                            {/* QR National Standard Mockup */}
                            <div className="w-52 mx-auto p-3.5 bg-white rounded-2xl border-2 border-dashed border-amber-400 flex flex-col items-center justify-center shadow-inner relative space-y-2">
                                <div className="text-[10px] font-black text-stone-900 tracking-widest uppercase">
                                    KEDAI DYNASTY
                                </div>
                                <div className="p-2 bg-stone-900 rounded-xl">
                                    <QrCode className="w-28 h-28 text-white" />
                                </div>
                                <div className="text-[11px] font-black text-[#82181A] bg-amber-100 px-3 py-1 rounded-full">
                                    {formatRupiah(grandTotal)}
                                </div>
                                <span className="text-[9px] text-stone-400">NMID: ID102003948271</span>
                            </div>

                            <p className="text-xs font-medium text-stone-600 max-w-xs mx-auto">
                                Buka aplikasi e-Wallet atau m-Banking Anda, lalu scan kode QRIS di atas untuk menyelesaikan pesanan.
                            </p>

                            <div className="pt-2 border-t border-stone-100 flex items-center justify-center gap-1.5 text-[10px] font-bold text-stone-600 flex-wrap">
                                <span className="px-2 py-1 bg-stone-100 rounded-lg">BCA Mobile</span>
                                <span className="px-2 py-1 bg-stone-100 rounded-lg">GoPay</span>
                                <span className="px-2 py-1 bg-stone-100 rounded-lg">OVO</span>
                                <span className="px-2 py-1 bg-stone-100 rounded-lg">DANA</span>
                                <span className="px-2 py-1 bg-stone-100 rounded-lg">ShopeePay</span>
                                <span className="px-2 py-1 bg-stone-100 rounded-lg">Livin'</span>
                            </div>
                        </div>
                    )}

                    {/* Method 2: Transfer Bank */}
                    {paymentMethod === 'transfer' && (
                        <div className="bg-white rounded-2xl border border-stone-200/90 p-4 shadow-xs space-y-3 animate-fade-in">
                            <h4 className="font-display font-bold text-xs text-stone-900 uppercase tracking-wider">
                                Pilih Bank Virtual Account:
                            </h4>

                            <div className="grid grid-cols-2 gap-2">
                                {['BCA', 'Mandiri', 'BRI', 'BNI'].map((bank) => (
                                    <button
                                        key={bank}
                                        onClick={() => setSelectedBank(bank)}
                                        className={`p-2.5 rounded-xl border text-xs font-bold transition-all cursor-pointer flex items-center justify-between ${
                                            selectedBank === bank
                                                ? 'border-[#82181A] bg-red-50/70 text-[#82181A] ring-1 ring-[#82181A]'
                                                : 'border-stone-200 bg-white text-stone-700 hover:border-stone-300'
                                        }`}
                                    >
                                        <span>Bank {bank}</span>
                                        {selectedBank === bank && (
                                            <CheckCircle2 className="w-3.5 h-3.5 text-[#82181A]" />
                                        )}
                                    </button>
                                ))}
                            </div>

                            {/* VA Card */}
                            <div className="bg-stone-50 p-3.5 rounded-xl border border-stone-200 space-y-2">
                                <span className="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">
                                    Nomor Virtual Account {selectedBank}
                                </span>
                                <div className="flex items-center justify-between bg-white p-2.5 rounded-xl border border-stone-200">
                                    <span className="font-mono font-bold text-sm text-stone-900 tracking-wider">
                                        {vaNumbers[selectedBank] || vaNumbers.BCA}
                                    </span>
                                    <button
                                        onClick={() => handleCopy(vaNumbers[selectedBank] || vaNumbers.BCA, 'Nomor Virtual Account')}
                                        className="text-xs font-bold text-[#82181A] hover:text-[#661012] flex items-center gap-1 cursor-pointer bg-red-50 hover:bg-red-100 px-2 py-1 rounded-lg transition"
                                    >
                                        {copiedText === (vaNumbers[selectedBank] || vaNumbers.BCA) ? (
                                            <>
                                                <Check className="w-3.5 h-3.5 text-emerald-600" />
                                                <span className="text-emerald-700">Tersalin</span>
                                            </>
                                        ) : (
                                            <>
                                                <Copy className="w-3.5 h-3.5" />
                                                <span>Salin</span>
                                            </>
                                        )}
                                    </button>
                                </div>

                                <div className="flex justify-between items-center text-xs text-stone-600 pt-1">
                                    <span>Nominal Transfer Tepat:</span>
                                    <span className="font-bold text-stone-900">{formatRupiah(grandTotal)}</span>
                                </div>
                            </div>

                            <p className="text-[11px] text-stone-500 leading-relaxed">
                                💡 Pembayaran melalui Virtual Account akan diverifikasi otomatis setelah transfer selesai.
                            </p>
                        </div>
                    )}

                    {/* Method 3: Bank Lain / Antar Bank */}
                    {paymentMethod === 'other_bank' && (
                        <div className="bg-white rounded-2xl border border-stone-200/90 p-4 shadow-xs space-y-3 animate-fade-in">
                            <h4 className="font-display font-bold text-xs text-stone-900 uppercase tracking-wider">
                                Panduan Transfer Antar Bank:
                            </h4>

                            <div className="bg-stone-50 p-3 rounded-xl border border-stone-200 text-xs space-y-2 text-stone-700">
                                <div className="flex items-start gap-2">
                                    <span className="w-4 h-4 rounded-full bg-amber-400 text-stone-950 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">1</span>
                                    <span>Pilih menu <strong>Transfer Antar Bank</strong> pada ATM / m-Banking Anda.</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <span className="w-4 h-4 rounded-full bg-amber-400 text-stone-950 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">2</span>
                                    <span>Pilih Bank BCA (Kode: 014) dan masukkan nomor rekening Virtual Account: <strong>8271 0812 3456 7890</strong>.</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <span className="w-4 h-4 rounded-full bg-amber-400 text-stone-950 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">3</span>
                                    <span>Masukkan nominal transfer tepat sebesar <strong>{formatRupiah(grandTotal)}</strong>.</span>
                                </div>
                            </div>

                            <p className="text-[11px] text-stone-500">
                                Simpan bukti transaksi Anda untuk konfirmasi jika diperlukan oleh kasir.
                            </p>
                        </div>
                    )}

                </div>

                {/* Sticky Bottom Action Bar with Real Submit */}
                <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl">
                    <button
                        onClick={handlePayNow}
                        disabled={isSubmitting}
                        className="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] hover:from-[#5B0F11] hover:to-[#82181A] text-white font-display font-extrabold text-xs tracking-wider shadow-lg shadow-red-950/20 active:scale-[0.98] disabled:opacity-50 transition-all flex items-center justify-between uppercase cursor-pointer"
                    >
                        <span>{isSubmitting ? 'Memproses Pesanan...' : 'Bayar Sekarang 💳'}</span>
                        <span className="bg-white/20 px-3 py-1 rounded-xl text-amber-200 text-xs font-black">
                            {formatRupiah(grandTotal)}
                        </span>
                    </button>
                </div>

            </div>
        </div>
    );
}

