import React from 'react';
import { useCart } from '../context/CartContext';
import { Sparkles, Plus, Star } from 'lucide-react';

export default function PromoBanner({ onSelectItem }) {
    const { menuList, addToCart } = useCart();

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    // Pick top items from real backend menuList
    const featuredItems = menuList.slice(0, 4);

    if (featuredItems.length === 0) return null;

    return (
        <section className="pt-3 pb-2 max-w-md mx-auto w-full">
            {/* Header */}
            <div className="px-4 flex items-center justify-between mb-2">
                <div className="flex items-center gap-1.5">
                    <span className="text-amber-500 text-sm">⭐</span>
                    <h2 className="font-display font-extrabold text-xs tracking-wider uppercase text-[#82181A] flex items-center gap-1">
                        Rekomendasi Favorit
                    </h2>
                </div>
                <span className="text-[11px] font-semibold text-amber-600">
                    Kedai Dynasty
                </span>
            </div>

            {/* Horizontal Scroll Cards */}
            <div className="flex gap-3 px-4 overflow-x-auto no-scrollbar pb-1 snap-x">
                {featuredItems.map((item) => {
                    const isOutOfStock = typeof item.stok === 'number' && item.stok <= 0;
                    return (
                        <div
                            key={item.id}
                            onClick={() => onSelectItem(item)}
                            className="snap-start shrink-0 w-[220px] bg-white rounded-2xl border border-stone-200/80 shadow-xs hover:shadow-md overflow-hidden cursor-pointer transition-all duration-200 group flex flex-col justify-between"
                        >
                            {/* Image Container */}
                            <div className="relative h-28 w-full overflow-hidden bg-stone-100">
                                <img
                                    src={item.gambar}
                                    alt={item.nama}
                                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                />
                                {/* Badges */}
                                <div className="absolute top-2 left-2 flex flex-col gap-1">
                                    <span className="bg-amber-400 text-stone-950 font-black text-[9px] px-2 py-0.5 rounded-full shadow-sm flex items-center gap-0.5">
                                        <Sparkles className="w-2.5 h-2.5" />
                                        Favorit
                                    </span>
                                </div>

                                {/* Rating */}
                                <div className="absolute bottom-2 right-2 bg-stone-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-1">
                                    <Star className="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                    <span>{item.rating || '4.9'}</span>
                                </div>
                            </div>

                            {/* Content */}
                            <div className="p-3 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 className="font-display font-bold text-xs text-stone-900 line-clamp-1 group-hover:text-[#82181A] transition-colors">
                                        {item.nama}
                                    </h3>
                                    <p className="text-[10px] text-stone-500 line-clamp-1 mt-0.5">
                                        {item.deskripsi || item.deskripsi_singkat}
                                    </p>
                                </div>

                                <div className="mt-2.5 flex items-center justify-between gap-2 pt-2 border-t border-stone-100">
                                    <div className="text-xs font-black text-[#82181A]">
                                        {formatRupiah(item.harga)}
                                    </div>

                                    <button
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            if (isOutOfStock) return;
                                            addToCart(item, 1);
                                        }}
                                        disabled={isOutOfStock}
                                        className={`flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold text-[11px] shadow-xs active:scale-95 transition cursor-pointer ${
                                            isOutOfStock
                                                ? 'bg-stone-200 text-stone-400 cursor-not-allowed'
                                                : 'bg-amber-400 hover:bg-amber-500 text-stone-950'
                                        }`}
                                    >
                                        {isOutOfStock ? (
                                            <span>Habis</span>
                                        ) : (
                                            <>
                                                <Plus className="w-3 h-3" />
                                                <span>Tambah</span>
                                            </>
                                        )}
                                    </button>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </section>
    );
}

