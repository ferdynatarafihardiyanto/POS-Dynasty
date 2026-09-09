import React, { useState, useRef } from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, Trash2, Plus, Minus, ShoppingBag, User, FileText, Sparkles, AlertCircle, ShieldCheck } from 'lucide-react';

export default function CartDrawer({ onProceedToPayment }) {
    const {
        cartItems,
        isCartOpen,
        setIsCartOpen,
        updateQuantity,
        removeFromCart,
        clearCart,
        subtotal,
        taxPB1,
        grandTotal,
        totalItemCount,
        tableInfo,
        submitOrder
    } = useCart();

    const [buyerName, setBuyerName] = useState('');
    const [generalNotes, setGeneralNotes] = useState('');
    const [nameError, setNameError] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const nameInputRef = useRef(null);

    if (!isCartOpen) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const handleProceed = async () => {
        if (!buyerName || !buyerName.trim()) {
            setNameError(true);
            if (nameInputRef.current) {
                nameInputRef.current.focus();
                nameInputRef.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        setNameError(false);

        if (typeof onProceedToPayment === 'function') {
            onProceedToPayment(buyerName.trim(), generalNotes);
        } else {
            // Fallback direct checkout if payment page prop is not passed
            setIsSubmitting(true);
            try {
                const combinedNote = `[Pemesan: ${buyerName.trim()}] ${generalNotes || ''}`.trim();
                await submitOrder(combinedNote);
            } finally {
                setIsSubmitting(false);
            }
        }
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/70 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[94vh] sm:h-[88vh] bg-[#FFFDF9] rounded-t-[32px] sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative border-t sm:border border-amber-500/20">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3.5 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => setIsCartOpen(false)}
                            className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95 cursor-pointer"
                            aria-label="Kembali ke menu"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </button>
                        <div>
                            <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                                Keranjang Pesanan
                            </h2>
                            <p className="text-[11px] font-bold text-[#82181A] flex items-center gap-1">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Dine-in • {tableInfo?.name || `Meja ${tableInfo?.number || '01'}`}
                            </p>
                        </div>
                    </div>

                    {cartItems.length > 0 && (
                        <button
                            onClick={clearCart}
                            className="text-[11px] font-semibold text-rose-600 hover:text-rose-700 px-2.5 py-1 rounded-xl hover:bg-rose-50 transition cursor-pointer"
                        >
                            Kosongkan
                        </button>
                    )}
                </div>

                {/* Body Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4 pb-36">
                    {cartItems.length === 0 ? (
                        <div className="py-20 text-center px-4">
                            <div className="w-20 h-20 mx-auto mb-4 rounded-3xl bg-amber-100/70 border border-amber-200/80 flex items-center justify-center text-amber-800 shadow-inner">
                                <ShoppingBag className="w-10 h-10" />
                            </div>
                            <h3 className="font-display font-extrabold text-base text-stone-900">
                                Keranjang Pesanan Kosong
                            </h3>
                            <p className="text-xs text-stone-500 max-w-xs mx-auto mt-1.5 mb-6 leading-relaxed">
                                Pilih aneka kopi, minuman segar, dan hidangan lezat khas Kedai Dynasty untuk memulai pesanan Anda.
                            </p>
                            <button
                                onClick={() => setIsCartOpen(false)}
                                className="px-6 py-3 rounded-2xl bg-[#82181A] hover:bg-[#661012] text-white font-display font-bold text-xs shadow-lg shadow-red-950/20 active:scale-95 transition cursor-pointer"
                            >
                                Mulai Pilih Menu 🍽️
                            </button>
                        </div>
                    ) : (
                        <>
                            {/* Items List */}
                            <div className="space-y-3">
                                <div className="flex items-center justify-between px-1">
                                    <span className="font-display font-bold text-xs uppercase tracking-wider text-stone-500">
                                        Daftar Pesanan ({totalItemCount} item)
                                    </span>
                                    <button
                                        onClick={() => setIsCartOpen(false)}
                                        className="text-[11px] font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 cursor-pointer"
                                    >
                                        <Plus className="w-3.5 h-3.5" />
                                        <span>Tambah Menu</span>
                                    </button>
                                </div>

                                {cartItems.map((item) => (
                                    <div
                                        key={item.cartItemId}
                                        className="bg-white p-3.5 rounded-2xl border border-stone-200/90 shadow-xs flex flex-col gap-2.5 transition-all hover:border-amber-400/50"
                                    >
                                        <div className="flex gap-3">
                                            {/* Thumbnail */}
                                            <img
                                                src={item.menuItem.gambar}
                                                alt={item.menuItem.nama}
                                                className="w-16 h-16 rounded-xl object-cover bg-stone-100 shrink-0 border border-stone-100"
                                                loading="lazy"
                                            />

                                            {/* Details */}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between gap-1">
                                                    <h4 className="font-display font-bold text-xs text-stone-900 line-clamp-1">
                                                        {item.menuItem.nama}
                                                    </h4>
                                                    <button
                                                        onClick={() => removeFromCart(item.cartItemId)}
                                                        className="text-stone-400 hover:text-rose-500 p-1 rounded-lg transition cursor-pointer"
                                                        title="Hapus dari keranjang"
                                                    >
                                                        <Trash2 className="w-3.5 h-3.5" />
                                                    </button>
                                                </div>

                                                <div className="text-[11px] text-stone-500 mt-0.5">
                                                    {formatRupiah(item.unitPrice)} / porsi
                                                </div>

                                                {/* Special notes for kitchen */}
                                                {item.notes && (
                                                    <p className="text-[10px] text-amber-900 bg-amber-50/80 p-1.5 rounded-lg mt-1 border border-amber-200/60 leading-tight">
                                                        <span className="font-bold">Catatan:</span> "{item.notes}"
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        {/* Quantity & Item Subtotal */}
                                        <div className="flex items-center justify-between pt-2 border-t border-stone-100">
                                            <span className="font-display font-black text-xs text-[#82181A]">
                                                {formatRupiah(item.totalPrice)}
                                            </span>

                                            <div className="flex items-center border border-stone-200 rounded-xl bg-stone-50 p-0.5 shadow-2xs">
                                                <button
                                                    onClick={() => updateQuantity(item.cartItemId, -1)}
                                                    className="w-6 h-6 rounded-lg bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 transition active:scale-95 cursor-pointer"
                                                    aria-label="Kurangi jumlah"
                                                >
                                                    <Minus className="w-3 h-3" />
                                                </button>
                                                <span className="w-7 text-center font-display font-bold text-xs text-stone-900">
                                                    {item.quantity}
                                                </span>
                                                <button
                                                    onClick={() => updateQuantity(item.cartItemId, 1)}
                                                    className="w-6 h-6 rounded-lg bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 transition active:scale-95 cursor-pointer"
                                                    aria-label="Tambah jumlah"
                                                >
                                                    <Plus className="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* FLOW 2 — Data Pembeli (WAJIB) */}
                            <div 
                                className={`bg-white p-4 rounded-2xl border transition-all ${
                                    nameError 
                                        ? 'border-rose-400 bg-rose-50/20 ring-2 ring-rose-400/20' 
                                        : 'border-stone-200/90 shadow-xs'
                                }`}
                            >
                                <div className="flex items-center justify-between mb-2">
                                    <div className="flex items-center gap-1.5">
                                        <div className="w-6 h-6 rounded-lg bg-amber-100 text-amber-900 flex items-center justify-center">
                                            <User className="w-3.5 h-3.5" />
                                        </div>
                                        <label htmlFor="customer-name-input" className="font-display font-extrabold text-xs text-stone-900">
                                            Data Pembeli
                                        </label>
                                    </div>
                                    <span className={`text-[10px] font-extrabold px-2 py-0.5 rounded-md uppercase tracking-wider ${
                                        nameError ? 'text-rose-700 bg-rose-100' : 'text-amber-800 bg-amber-100/80'
                                    }`}>
                                        Nama Wajib Diisi *
                                    </span>
                                </div>

                                <div className="space-y-1.5">
                                    <label htmlFor="customer-name-input" className="block text-[11px] font-semibold text-stone-700">
                                        Nama Pembeli <span className="text-rose-600 font-bold">*</span>
                                    </label>
                                    <input
                                        id="customer-name-input"
                                        ref={nameInputRef}
                                        type="text"
                                        value={buyerName}
                                        onChange={(e) => {
                                            setBuyerName(e.target.value);
                                            if (nameError && e.target.value.trim()) {
                                                setNameError(false);
                                            }
                                        }}
                                        placeholder="Masukkan nama Anda (Contoh: Budi Santoso)..."
                                        className={`w-full p-2.5 rounded-xl border text-xs font-semibold text-stone-900 placeholder:text-stone-400 placeholder:font-normal focus:outline-none transition ${
                                            nameError
                                                ? 'border-rose-400 bg-white focus:ring-2 focus:ring-rose-400/30'
                                                : 'border-stone-200 bg-stone-50/80 focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500'
                                        }`}
                                    />
                                    {nameError && (
                                        <p className="text-[11px] font-bold text-rose-600 mt-1.5 flex items-center gap-1 animate-fade-in">
                                            <AlertCircle className="w-3.5 h-3.5 shrink-0" />
                                            <span>Nama pembeli wajib diisi sebelum lanjut ke pembayaran.</span>
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Catatan Tambahan (Opsional) */}
                            <div className="bg-white p-3.5 rounded-2xl border border-stone-200/90 shadow-xs space-y-1.5">
                                <div className="flex items-center gap-1.5">
                                    <FileText className="w-3.5 h-3.5 text-stone-400" />
                                    <label htmlFor="general-order-notes" className="font-display font-bold text-xs text-stone-800">
                                        Catatan untuk Seluruh Pesanan (Opsional)
                                    </label>
                                </div>
                                <textarea
                                    id="general-order-notes"
                                    value={generalNotes}
                                    onChange={(e) => setGeneralNotes(e.target.value)}
                                    rows="2"
                                    placeholder="Contoh: Tolong disajikan bersamaan saat semua pesanan siap..."
                                    className="w-full p-2.5 rounded-xl border border-stone-200 bg-stone-50/80 text-xs text-stone-800 placeholder:text-stone-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
                                />
                            </div>

                            {/* Ringkasan Biaya */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/90 space-y-2.5 shadow-xs">
                                <h3 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                    Ringkasan Biaya
                                </h3>
                                <div className="flex justify-between text-xs text-stone-600">
                                    <span>Subtotal ({totalItemCount} porsi)</span>
                                    <span className="font-semibold text-stone-800">{formatRupiah(subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-xs text-stone-600">
                                    <span>Pajak Restoran PB1 (10%)</span>
                                    <span className="font-semibold text-stone-800">{formatRupiah(taxPB1)}</span>
                                </div>
                                <div className="pt-2.5 border-t border-stone-100 flex justify-between items-baseline">
                                    <div>
                                        <span className="font-display font-extrabold text-xs text-stone-900 block">Total Pesanan</span>
                                        <span className="text-[10px] text-stone-400">Termasuk PB1 10%</span>
                                    </div>
                                    <span className="font-display font-black text-lg text-[#82181A]">
                                        {formatRupiah(grandTotal)}
                                    </span>
                                </div>
                            </div>
                        </>
                    )}
                </div>

                {/* FLOW 1 — Sticky Checkout Bar with Prominent "Bayar" CTA */}
                {cartItems.length > 0 && (
                    <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl">
                        <button
                            onClick={handleProceed}
                            disabled={isSubmitting}
                            className="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] hover:from-[#5B0F11] hover:to-[#82181A] text-white font-display font-extrabold text-xs tracking-wider shadow-lg shadow-red-950/20 active:scale-[0.98] disabled:opacity-50 transition-all flex items-center justify-between uppercase cursor-pointer group"
                        >
                            <span className="flex items-center gap-2">
                                <span>Bayar Sekarang</span>
                                <Sparkles className="w-3.5 h-3.5 text-amber-300 group-hover:rotate-12 transition-transform" />
                            </span>
                            <span className="bg-white/20 px-3 py-1 rounded-xl text-amber-200 text-xs font-black">
                                {formatRupiah(grandTotal)}
                            </span>
                        </button>
                    </div>
                )}

            </div>
        </div>
    );
}


