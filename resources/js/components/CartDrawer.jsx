import React, { useState, useEffect } from 'react';
import { useCart } from '../context/CartContext';
import { ShoppingBag, ArrowLeft, Trash2, Plus, Minus, User, AlertCircle } from 'lucide-react';

export default function CartDrawer() {
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
        activeOrder,
        submitOrder
    } = useCart();

    const extractCustomerName = (order) => {
        let name = order?.customerName || order?.nama_pelanggan || localStorage.getItem('pos_customer_name') || '';
        if (!name && order?.notes && order.notes.startsWith('Pemesan:')) {
            const parts = order.notes.split('|');
            name = parts[0].replace('Pemesan:', '').trim();
        }
        return name;
    };

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [customerName, setCustomerName] = useState(() => {
        return extractCustomerName(activeOrder);
    });
    const [nameError, setNameError] = useState('');

    useEffect(() => {
        if (activeOrder) {
            const name = extractCustomerName(activeOrder);
            if (name && !customerName) {
                setCustomerName(name);
            }
        }
    }, [activeOrder]);

    if (!isCartOpen) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const handleCheckout = async () => {
        const trimmedName = customerName.trim();
        if (!trimmedName) {
            setNameError('Mohon masukkan nama pemesan terlebih dahulu');
            return;
        }
        localStorage.setItem('pos_customer_name', trimmedName);
        setIsSubmitting(true);
        try {
            await submitOrder('', trimmedName);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/50 backdrop-blur-[2px] flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md md:ml-auto md:mr-4 md:my-auto md:h-[96vh] md:rounded-3xl h-[94vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative border border-stone-200/80">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3.5 border-b border-stone-200/80 flex items-center justify-between shadow-2xs">
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => setIsCartOpen(false)}
                            className="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95"
                            aria-label="Tutup Keranjang"
                        >
                            <ArrowLeft className="w-4 h-4" />
                        </button>
                        <div>
                            <h2 className="font-display font-bold text-sm text-stone-900 leading-tight">
                                Keranjang Pesanan
                            </h2>
                            <p className="text-[11px] font-medium text-[#881B1E] mt-0.5">
                                Dine-in • Meja {tableInfo.number} ({tableInfo.name})
                            </p>
                        </div>
                    </div>

                    {cartItems.length > 0 && (
                        <button
                            onClick={clearCart}
                            className="text-xs font-semibold text-red-600 hover:text-red-700 hover:bg-red-50/80 px-2.5 py-1.5 rounded-lg transition active:scale-95"
                        >
                            Kosongkan
                        </button>
                    )}
                </div>

                {/* Body Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-3.5 pb-36">
                    {cartItems.length === 0 ? (
                        <div className="py-16 text-center">
                            <div className="w-16 h-16 mx-auto mb-3.5 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-700 shadow-2xs">
                                <ShoppingBag className="w-7 h-7" />
                            </div>
                            <h3 className="font-display font-bold text-base text-stone-900">
                                Keranjangmu Masih Kosong
                            </h3>
                            <p className="text-xs text-stone-500 max-w-xs mx-auto mt-1 mb-6 leading-relaxed">
                                Pilih menu lezat dari daftar rekomendasi koki untuk memulai pesanan meja Anda.
                            </p>
                            <button
                                onClick={() => setIsCartOpen(false)}
                                className="px-5 py-2.5 rounded-xl bg-[#881B1E] hover:bg-[#731417] text-white font-bold text-xs shadow-sm hover:shadow active:scale-95 transition inline-flex items-center gap-1.5"
                            >
                                Mulai Pilih Menu
                            </button>
                        </div>
                    ) : (
                        <>
                            {/* Items List */}
                            <div className="space-y-2.5">
                                {cartItems.map((item) => (
                                    <div
                                        key={item.cartItemId}
                                        className="bg-white p-3.5 rounded-2xl border border-stone-200/80 shadow-2xs flex flex-col gap-2.5 hover:border-stone-300 transition-colors"
                                    >
                                        <div className="flex gap-3">
                                            {/* Thumbnail */}
                                            <img
                                                src={item.menuItem?.gambar || item.menuItem?.gambar_url || '/images/produk/americano.jpg'}
                                                alt={item.menuItem?.nama || 'Menu'}
                                                className="w-16 h-16 rounded-xl object-cover bg-stone-100 shrink-0 border border-stone-100"
                                                onError={(e) => {
                                                    e.target.onerror = null;
                                                    e.target.src = '/images/produk/americano.jpg';
                                                }}
                                            />

                                            {/* Details */}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between gap-1">
                                                    <h4 className="font-display font-bold text-xs text-stone-900 line-clamp-1 leading-snug">
                                                        {item.menuItem.nama}
                                                    </h4>
                                                    <button
                                                        onClick={() => removeFromCart(item.cartItemId)}
                                                        className="text-stone-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition"
                                                        title="Hapus"
                                                    >
                                                        <Trash2 className="w-3.5 h-3.5" />
                                                    </button>
                                                </div>

                                                {/* Selected Modifiers Pills */}
                                                <div className="flex flex-wrap gap-1 mt-1.5">
                                                    {item.customizations.carb && (
                                                        <span className="text-[10px] bg-stone-100 text-stone-700 px-2 py-0.5 rounded-md font-medium border border-stone-200/60">
                                                            🍚 {item.customizations.carb.name}
                                                        </span>
                                                    )}
                                                    {item.customizations.spice && (
                                                        <span className="text-[10px] bg-amber-50 text-amber-800 px-2 py-0.5 rounded-md font-medium border border-amber-200/60">
                                                            🌶️ {item.customizations.spice.name}
                                                        </span>
                                                    )}
                                                    {item.customizations.toppings && item.customizations.toppings.map(t => (
                                                        <span key={t.id} className="text-[10px] bg-red-50 text-red-700 px-2 py-0.5 rounded-md font-medium border border-red-200/50">
                                                            + {t.name}
                                                        </span>
                                                    ))}
                                                    {item.customizations.modifiers && item.customizations.modifiers.map(m => (
                                                        <span key={m.id} className="text-[10px] bg-stone-100 text-stone-800 border border-stone-200/80 px-2 py-0.5 rounded-md font-medium">
                                                            + {m.nama} {parseFloat(m.harga_tambahan) > 0 ? `(${formatRupiah(m.harga_tambahan)})` : ''}
                                                        </span>
                                                    ))}
                                                </div>

                                                {/* Special notes for kitchen */}
                                                {item.notes && (
                                                    <p className="text-[11px] text-stone-600 italic bg-amber-50/40 p-2 rounded-lg mt-1.5 border border-amber-200/50 leading-relaxed">
                                                        "{item.notes}"
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        {/* Quantity & Item Subtotal */}
                                        <div className="flex items-center justify-between pt-2.5 border-t border-stone-100">
                                            <span className="font-display font-extrabold text-xs text-[#881B1E]">
                                                {formatRupiah(item.totalPrice)}
                                            </span>

                                            <div className="flex items-center border border-stone-200 rounded-lg bg-stone-50 p-0.5 shadow-2xs">
                                                <button
                                                    onClick={() => updateQuantity(item.cartItemId, -1)}
                                                    className="w-6 h-6 rounded-md bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 hover:text-stone-900 transition active:scale-90 shadow-2xs"
                                                >
                                                    <Minus className="w-3 h-3" />
                                                </button>
                                                <span className="w-7 text-center font-display font-bold text-xs text-stone-900 select-none">
                                                    {item.quantity}
                                                </span>
                                                <button
                                                    onClick={() => updateQuantity(item.cartItemId, 1)}
                                                    className="w-6 h-6 rounded-md bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 hover:text-stone-900 transition active:scale-90 shadow-2xs"
                                                >
                                                    <Plus className="w-3 h-3" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Add More Items Button */}
                            <button
                                onClick={() => setIsCartOpen(false)}
                                className="w-full py-2.5 px-3 rounded-xl border border-dashed border-stone-300 hover:border-[#881B1E] hover:bg-[#881B1E]/5 text-stone-600 hover:text-[#881B1E] text-xs font-bold transition flex items-center justify-center gap-1.5 bg-white shadow-2xs active:scale-[0.99]"
                            >
                                <Plus className="w-4 h-4 text-[#881B1E]" />
                                <span>Tambah Menu Lainnya</span>
                            </button>

                            {/* Input Identitas Pemesan */}
                            <div className="bg-white p-3.5 rounded-2xl border border-stone-200/80 shadow-2xs">
                                <div>
                                    <label className="block text-xs font-bold text-stone-800 flex items-center justify-between mb-1.5">
                                        <span className="flex items-center gap-1.5">
                                            <User className="w-3.5 h-3.5 text-[#881B1E]" />
                                            Nama Pemesan <span className="text-red-500">*</span>
                                        </span>
                                        <span className="text-[10px] text-amber-800 bg-amber-50 font-semibold px-2 py-0.5 rounded-md border border-amber-200/60">
                                            Dicatat di POS Kasir
                                        </span>
                                    </label>
                                    <input
                                        type="text"
                                        value={customerName}
                                        onChange={(e) => {
                                            setCustomerName(e.target.value);
                                            localStorage.setItem('pos_customer_name', e.target.value);
                                            if (nameError) setNameError('');
                                        }}
                                        placeholder="Masukkan nama Anda (misal: Natan / Fian)..."
                                        maxLength={40}
                                        className={`w-full px-3.5 py-2.5 rounded-xl border text-xs font-medium text-stone-900 bg-stone-50/50 focus:bg-white focus:outline-none transition ${
                                            nameError
                                                ? 'border-red-400 focus:ring-2 focus:ring-red-400/20'
                                                : 'border-stone-200 focus:border-[#881B1E] focus:ring-2 focus:ring-[#881B1E]/10'
                                        }`}
                                    />
                                    {nameError && (
                                        <p className="text-[11px] text-red-600 font-medium flex items-center gap-1 mt-1.5">
                                            <AlertCircle className="w-3.5 h-3.5 shrink-0" />
                                            {nameError}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Ringkasan Pembayaran */}
                            <div className="bg-white p-3.5 rounded-2xl border border-stone-200/80 shadow-2xs space-y-2.5">
                                <h3 className="font-display font-bold text-xs text-stone-900 uppercase tracking-wider mb-2">
                                    Ringkasan Biaya
                                </h3>
                                <div className="flex justify-between text-xs text-stone-600">
                                    <span>Subtotal ({totalItemCount} item)</span>
                                    <span className="font-semibold text-stone-800">{formatRupiah(subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-xs text-stone-600">
                                    <span>Pajak Restoran PB1 (10%)</span>
                                    <span className="font-semibold text-stone-800">{formatRupiah(taxPB1)}</span>
                                </div>
                                <div className="pt-2.5 border-t border-stone-200/80 flex justify-between items-baseline">
                                    <span className="font-display font-bold text-xs text-stone-900">Total Pembayaran</span>
                                    <span className="font-display font-extrabold text-base text-[#881B1E]">
                                        {formatRupiah(grandTotal)}
                                    </span>
                                </div>
                            </div>
                        </>
                    )}
                </div>

                {/* Sticky Checkout Bar */}
                {cartItems.length > 0 && (
                    <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-[0_-4px_12px_rgba(0,0,0,0.05)]">
                        <button
                            onClick={handleCheckout}
                            disabled={isSubmitting}
                            className="w-full py-3.5 px-4 rounded-xl bg-[#881B1E] hover:bg-[#731417] text-white font-display font-bold text-xs tracking-wide shadow-sm hover:shadow active:scale-[0.99] disabled:opacity-50 transition flex items-center justify-between uppercase cursor-pointer disabled:cursor-not-allowed"
                        >
                            <span>{isSubmitting ? 'Memproses Pesanan...' : 'Lanjut Bayar (QRIS / TF) 📱'}</span>
                            <span className="bg-white/15 px-2.5 py-1 rounded-lg text-white font-display font-bold text-xs tracking-normal">
                                {formatRupiah(grandTotal)}
                            </span>
                        </button>
                    </div>
                )}

            </div>
        </div>
    );
}
