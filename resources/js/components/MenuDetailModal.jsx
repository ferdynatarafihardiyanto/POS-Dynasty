import React, { useState, useEffect } from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, Heart, Share2, Star, Clock, Sparkles, Plus, Minus, AlertCircle } from 'lucide-react';

export default function MenuDetailModal({ item, onClose }) {
    const { addToCart, tableInfo } = useCart();

    const [isFavorite, setIsFavorite] = useState(false);
    const [quantity, setQuantity] = useState(1);
    const [notes, setNotes] = useState('');

    const maxStock = typeof item?.stok === 'number' ? item.stok : 99;
    const isOutOfStock = maxStock <= 0;
    const isLowStock = maxStock > 0 && maxStock <= 5;

    useEffect(() => {
        document.body.style.overflow = 'hidden';
        return () => {
            document.body.style.overflow = 'auto';
        };
    }, []);

    if (!item) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const unitPrice = item.harga || 0;
    const totalPrice = unitPrice * quantity;

    const handleAddToCart = () => {
        if (isOutOfStock) return;
        addToCart(item, quantity, notes);
        onClose();
    };

    const handleShare = () => {
        if (navigator.share) {
            navigator.share({
                title: `${item.nama} - Kedai Dynasty`,
                text: item.deskripsi || item.deskripsi_singkat,
                url: window.location.href
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(window.location.href);
            alert('Link menu berhasil disalin!');
        }
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[92vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative">
                
                {/* Fixed Top Nav */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <button
                        onClick={onClose}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95 cursor-pointer"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>

                    <div className="text-center">
                        <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                            Detail Menu
                        </h2>
                        <p className="text-[11px] font-medium text-[#82181A]">
                            Kedai Dynasty • Meja {tableInfo?.number || '01'}
                        </p>
                    </div>

                    <div className="flex items-center gap-1">
                        <button
                            onClick={() => setIsFavorite(!isFavorite)}
                            className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 flex items-center justify-center transition active:scale-95 cursor-pointer"
                        >
                            <Heart className={`w-4 h-4 ${isFavorite ? 'fill-red-500 text-red-500' : 'text-stone-600'}`} />
                        </button>
                        <button
                            onClick={handleShare}
                            className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 flex items-center justify-center transition active:scale-95 cursor-pointer"
                        >
                            <Share2 className="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {/* Scrollable Content Body */}
                <div className="flex-1 overflow-y-auto no-scrollbar pb-32">
                    {/* Hero Image */}
                    <div className="relative h-64 w-full bg-stone-200 overflow-hidden">
                        <img
                            src={item.gambar}
                            alt={item.nama}
                            className="w-full h-full object-cover"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                        {/* Floating Badges */}
                        <div className="absolute top-3 left-3 flex flex-wrap gap-1.5">
                            {isOutOfStock ? (
                                <span className="bg-stone-900 text-white font-black text-xs px-2.5 py-1 rounded-full shadow-md">
                                    Stok Habis
                                </span>
                            ) : isLowStock ? (
                                <span className="bg-amber-400 text-stone-950 font-black text-xs px-2.5 py-1 rounded-full shadow-md flex items-center gap-1">
                                    <Sparkles className="w-3.5 h-3.5" />
                                    Sisa {maxStock} Porsi
                                </span>
                            ) : (
                                <span className="bg-[#82181A] text-white font-bold text-xs px-2.5 py-1 rounded-full shadow-md">
                                    {item.kategori?.nama || 'Menu Dynasty'}
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Main Information Section */}
                    <div className="p-4 bg-white border-b border-stone-200/70">
                        <div className="flex items-start justify-between gap-2">
                            <h1 className="font-display font-extrabold text-xl text-stone-900 tracking-tight">
                                {item.nama}
                            </h1>
                        </div>

                        {/* Price Row */}
                        <div className="mt-2 flex items-baseline gap-2 flex-wrap">
                            <span className="font-display font-black text-2xl text-[#82181A]">
                                {formatRupiah(item.harga)}
                            </span>
                        </div>

                        {/* Meta Tags */}
                        <div className="mt-3.5 flex items-center gap-2 flex-wrap text-xs font-semibold text-stone-600">
                            <div className="flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200/80 rounded-xl">
                                <Star className="w-3.5 h-3.5 fill-amber-400 text-amber-500" />
                                <span>{item.rating || '4.8'} ({item.reviews_count || 48} ulasan)</span>
                            </div>
                            <div className="flex items-center gap-1 px-2.5 py-1 bg-stone-100 text-stone-700 rounded-xl">
                                <Clock className="w-3.5 h-3.5 text-stone-500" />
                                <span>{item.prep_time || '10 - 15 Menit'}</span>
                            </div>
                            {item.stok !== undefined && (
                                <div className={`flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold ${
                                    isOutOfStock 
                                        ? 'bg-red-50 text-red-700 border border-red-200' 
                                        : 'bg-emerald-50 text-emerald-800 border border-emerald-200/80'
                                }`}>
                                    <span>{isOutOfStock ? 'Habis' : `Tersedia ${maxStock} porsi`}</span>
                                </div>
                            )}
                        </div>

                        {/* Description */}
                        <p className="mt-3.5 text-xs text-stone-600 leading-relaxed">
                            {item.deskripsi || item.deskripsi_singkat}
                        </p>
                    </div>

                    {/* Catatan Khusus untuk Koki / Barista */}
                    <div className="mt-2.5 p-4 bg-white border-y border-stone-200/70">
                        <div className="flex items-center justify-between mb-1">
                            <h3 className="font-display font-bold text-sm text-stone-900 flex items-center gap-1.5">
                                <span className="w-1.5 h-1.5 rounded-full bg-[#82181A]"></span>
                                Catatan Khusus untuk Dapur / Barista
                            </h3>
                            <span className="text-[10px] font-medium text-stone-400">
                                Opsional
                            </span>
                        </div>
                        <p className="text-[11px] text-stone-400 mb-2">
                            Beri instruksi khusus untuk persiapan menu ini
                        </p>

                        <div className="relative">
                            <textarea
                                value={notes}
                                onChange={(e) => setNotes(e.target.value.slice(0, 120))}
                                rows="2"
                                placeholder="Contoh: Less sugar / jangan terlalu manis, es sedikit, kuah dipisah..."
                                className="w-full p-3 rounded-2xl border border-stone-200 bg-stone-50 text-xs text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 focus:bg-white transition"
                            />
                            <div className="text-right text-[10px] text-stone-400 mt-1">
                                {notes.length}/120 karakter
                            </div>
                        </div>
                    </div>
                </div>

                {/* Sticky Bottom Action Bar */}
                <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl flex items-center gap-3">
                    {/* Quantity Selector */}
                    <div className="flex items-center border border-stone-200 rounded-2xl bg-stone-50 p-1">
                        <button
                            onClick={() => setQuantity(q => Math.max(1, q - 1))}
                            disabled={quantity <= 1 || isOutOfStock}
                            className="w-8 h-8 rounded-xl bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 disabled:opacity-40 disabled:hover:bg-white transition active:scale-95 cursor-pointer"
                        >
                            <Minus className="w-3.5 h-3.5" />
                        </button>
                        <span className="w-8 text-center font-display font-extrabold text-sm text-stone-900">
                            {isOutOfStock ? 0 : quantity}
                        </span>
                        <button
                            onClick={() => setQuantity(q => Math.min(maxStock, q + 1))}
                            disabled={quantity >= maxStock || isOutOfStock}
                            className="w-8 h-8 rounded-xl bg-white border border-stone-200/80 text-stone-700 flex items-center justify-center hover:bg-stone-100 disabled:opacity-40 transition active:scale-95 cursor-pointer"
                        >
                            <Plus className="w-3.5 h-3.5" />
                        </button>
                    </div>

                    {/* Add to Cart Submit Button */}
                    <button
                        onClick={handleAddToCart}
                        disabled={isOutOfStock}
                        className={`flex-1 py-3 px-4 rounded-2xl font-display font-extrabold text-xs tracking-wide shadow-lg shadow-red-950/20 active:scale-[0.98] transition-all flex items-center justify-between cursor-pointer ${
                            isOutOfStock
                                ? 'bg-stone-300 text-stone-500 cursor-not-allowed shadow-none'
                                : 'bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] hover:from-[#5B0F11] hover:to-[#82181A] text-white'
                        }`}
                    >
                        <span>{isOutOfStock ? 'Menu Sedang Habis' : '+ Tambah ke Pesanan'}</span>
                        {!isOutOfStock && (
                            <span className="bg-white/20 px-2 py-0.5 rounded-lg text-amber-200 text-xs">
                                {formatRupiah(totalPrice)}
                            </span>
                        )}
                    </button>
                </div>

            </div>
        </div>
    );
}

