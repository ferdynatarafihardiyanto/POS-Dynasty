import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, Trash2, Plus, Minus, UtensilsCrossed, AlertCircle, ShoppingBag, ChevronRight, Sparkles } from 'lucide-react';

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
        submitOrder
    } = useCart();

    const [generalNotes, setGeneralNotes] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    if (!isCartOpen) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const handleCheckout = async () => {
        setIsSubmitting(true);
        try {
            await submitOrder(generalNotes);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md md:ml-auto md:mr-4 md:my-auto md:h-[96vh] md:rounded-3xl h-[94vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => setIsCartOpen(false)}
                            className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95"
                        >
                            <ArrowLeft className="w-5 h-5" />
                        </button>
                        <div>
                            <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                                Keranjang Pesanan
                            </h2>
                            <p className="text-[11px] font-medium text-[#881B1E]">
                                Dine-in • Meja {tableInfo.number} ({tableInfo.name})
                            </p>
                        </div>
                    </div>

                    {cartItems.length > 0 && (
                        <button
                            onClick={clearCart}
                            className="text-[11px] font-semibold text-red-600 hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition"
                        >
                            Kosongkan
                        </button>
                    )}
                </div>

                {/* Body Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4 pb-36">
                    {cartItems.length === 0 ? (
                        <div className="py-16 text-center">
                            <div className="w-16 h-16 mx-auto mb-3 rounded-3xl bg-amber-100/80 flex items-center justify-center text-amber-700">
                                <ShoppingBag className="w-8 h-8" />
                            </div>
                            <h3 className="font-display font-bold text-base text-stone-800">
                                Keranjangmu Masih Kosong
                            </h3>
                            <p className="text-xs text-stone-500 max-w-xs mx-auto mt-1 mb-6">
                                Pilih menu lezat dari daftar rekomendasi koki untuk memulai pesanan meja Anda.
                            </p>
                            <button
                                onClick={() => setIsCartOpen(false)}
                                className="px-5 py-2.5 rounded-2xl bg-[#881B1E] hover:bg-[#731417] text-white font-bold text-xs shadow-md active:scale-95 transition"
                            >
                                Mulai Pilih Menu
                            </button>
                        </div>
                    ) : (
                        <>
                            {/* Items List */}
                            <div className="space-y-3">
                                {cartItems.map((item) => (
                                    <div
                                        key={item.cartItemId}
                                        className="bg-white p-3.5 rounded-2xl border border-stone-200/80 shadow-xs flex flex-col gap-2.5"
                                    >
                                        <div className="flex gap-3">
                                            {/* Thumbnail */}
                                            <img
                                                src={item.menuItem.gambar}
                                                alt={item.menuItem.nama}
                                                className="w-16 h-16 rounded-xl object-cover bg-stone-100 shrink-0"
                                            />

                                            {/* Details */}
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between gap-1">
                                                    <h4 className="font-display font-bold text-xs text-stone-900 line-clamp-1">
                                                        {item.menuItem.nama}
                                                    </h4>
                                                    <button
                                                        onClick={() => removeFromCart(item.cartItemId)}
                                                        className="text-stone-400 hover:text-red-500 p-1 rounded-lg transition"
                                                        title="Hapus"
                                                    >
                                                        <Trash2 className="w-3.5 h-3.5" />
                                                    </button>
                                                </div>

                                                {/* Selected Modifiers Pills */}
                                                <div className="flex flex-wrap gap-1 mt-1">
                                                    {item.customizations.carb && (
                                                        <span className="text-[10px] bg-stone-100 text-stone-700 px-1.5 py-0.5 rounded-md font-medium">
                                                            🍚 {item.customizations.carb.name}
                                                        </span>
                                                    )}
                                                    {item.customizations.spice && (
                                                        <span className="text-[10px] bg-amber-50 text-amber-800 px-1.5 py-0.5 rounded-md font-medium">
                                                            🌶️ {item.customizations.spice.name}
                                                        </span>
                                                    )}
                                                    {item.customizations.toppings && item.customizations.toppings.map(t => (
                                                        <span key={t.id} className="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.5 rounded-md font-medium">
                                                            + {t.name}
                                                        </span>
                                                    ))}
                                                </div>

                                                {/* Special notes for kitchen */}
                                                {item.notes && (
                                                    <p className="text-[10px] text-amber-800 italic bg-amber-50/60 p-1 rounded-lg mt-1 border border-amber-200/50">
                                                        "{item.notes}"
                                                    </p>
                                                )}
                                            </div>
                                        </div>

                                        {/* Quantity & Item Subtotal */}
                                        <div className="flex items-center justify-between pt-2 border-t border-stone-100">
                                            <span className="font-display font-black text-xs text-[#881B1E]">
                                                {formatRupiah(item.totalPrice)}
                                            </span>

                                            <div className="flex items-center border border-stone-200 rounded-xl bg-stone-50 p-0.5">
                                                <button
                                                    onClick={() => updateQuantity(item.cartItemId, -1)}
                                                    className="w-6 h-6 rounded-lg bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 transition active:scale-95"
                                                >
                                                    <Minus className="w-3 h-3" />
                                                </button>
                                                <span className="w-6 text-center font-display font-bold text-xs text-stone-900">
                                                    {item.quantity}
                                                </span>
                                                <button
                                                    onClick={() => updateQuantity(item.cartItemId, 1)}
                                                    className="w-6 h-6 rounded-lg bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 transition active:scale-95"
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
                                className="w-full py-2.5 px-3 rounded-2xl border-2 border-dashed border-stone-300 hover:border-amber-500 text-stone-600 hover:text-stone-900 text-xs font-bold transition flex items-center justify-center gap-1.5 bg-white"
                            >
                                <Plus className="w-4 h-4 text-amber-600" />
                                <span>Tambah Menu Lainnya</span>
                            </button>

                            {/* General Order Notes */}
                            <div className="bg-white p-3.5 rounded-2xl border border-stone-200/80">
                                <label className="block font-display font-bold text-xs text-stone-800 mb-1">
                                    📝 Catatan Umum untuk Dapur / Pelayan
                                </label>
                                <textarea
                                    value={generalNotes}
                                    onChange={(e) => setGeneralNotes(e.target.value)}
                                    rows="2"
                                    placeholder="Contoh: Tolong disajikan bersamaan ya mas, terima kasih..."
                                    className="w-full p-2.5 rounded-xl border border-stone-200 bg-stone-50 text-xs text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
                                />
                            </div>

                            {/* Ringkasan Pembayaran */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/80 space-y-2">
                                <h3 className="font-display font-bold text-xs text-stone-900 uppercase tracking-wider mb-2">
                                    Ringkasan Biaya
                                </h3>
                                <div className="flex justify-between text-xs text-stone-600">
                                    <span>Subtotal ({totalItemCount} item)</span>
                                    <span className="font-semibold">{formatRupiah(subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-xs text-stone-600">
                                    <span>Pajak Restoran PB1 (10%)</span>
                                    <span className="font-semibold">{formatRupiah(taxPB1)}</span>
                                </div>
                                <div className="pt-2 border-t border-stone-200 flex justify-between items-baseline">
                                    <span className="font-display font-bold text-sm text-stone-900">Total Pembayaran</span>
                                    <span className="font-display font-black text-base text-[#881B1E]">
                                        {formatRupiah(grandTotal)}
                                    </span>
                                </div>
                            </div>
                        </>
                    )}
                </div>

                {/* Sticky Checkout Bar */}
                {cartItems.length > 0 && (
                    <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl">
                        <button
                            onClick={handleCheckout}
                            disabled={isSubmitting}
                            className="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-[#82181A] via-[#8E1B1E] to-[#6E1214] hover:from-[#751417] hover:to-[#82181A] text-white font-display font-extrabold text-xs tracking-wider shadow-lg shadow-red-950/20 active:scale-[0.98] disabled:opacity-50 transition-all flex items-center justify-between uppercase"
                        >
                            <span>{isSubmitting ? 'Mengirim Pesanan...' : 'Kirim Pesanan ke Dapur 🍳'}</span>
                            <span className="bg-white/20 px-2.5 py-0.5 rounded-lg text-amber-200 text-xs font-bold">
                                {formatRupiah(grandTotal)}
                            </span>
                        </button>
                    </div>
                )}

            </div>
        </div>
    );
}
