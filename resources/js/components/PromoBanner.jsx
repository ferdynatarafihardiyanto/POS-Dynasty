import React from 'react';
import { PROMO_ITEMS } from '../data/mockData';
import { Sparkles, Plus, Star, ChevronRight } from 'lucide-react';

export default function PromoBanner({ onSelectItem }) {
    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    return (
        <section className="pt-3 pb-2 max-w-md mx-auto">
            {/* Header */}
            <div className="px-4 flex items-center justify-between mb-2.5">
                <div className="flex items-center gap-1.5">
                    <span className="text-amber-500 text-sm">⭐</span>
                    <h2 className="font-display font-extrabold text-xs tracking-wider uppercase text-[#881B1E] flex items-center gap-1">
                        Rekomendasi Koki & Promo
                    </h2>
                </div>
                <button
                    onClick={() => onSelectItem(PROMO_ITEMS[0])}
                    className="text-[11px] font-semibold text-amber-600 hover:text-amber-700 flex items-center"
                >
                    Lihat Semua
                    <ChevronRight className="w-3.5 h-3.5" />
                </button>
            </div>

            {/* Horizontal Scroll Cards */}
            <div className="flex gap-3 px-4 overflow-x-auto no-scrollbar pb-1 snap-x">
                {PROMO_ITEMS.map((item) => (
                    <div
                        key={item.id}
                        onClick={() => onSelectItem(item)}
                        className="snap-start shrink-0 w-[240px] bg-white rounded-2xl border border-stone-200/80 shadow-sm hover:shadow-md overflow-hidden cursor-pointer transition-all duration-200 group flex flex-col justify-between"
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
                                {item.diskon && (
                                    <span className="bg-red-600 text-white font-black text-[9px] px-2 py-0.5 rounded-full shadow-sm">
                                        {item.diskon}
                                    </span>
                                )}
                                {item.badge && (
                                    <span className="bg-amber-500/90 backdrop-blur-xs text-stone-950 font-extrabold text-[9px] px-2 py-0.5 rounded-full shadow-sm flex items-center gap-0.5">
                                        <Sparkles className="w-2.5 h-2.5" />
                                        {item.badge}
                                    </span>
                                )}
                            </div>

                            {/* Rating */}
                            <div className="absolute bottom-2 right-2 bg-stone-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-1">
                                <Star className="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                <span>{item.rating}</span>
                            </div>
                        </div>

                        {/* Content */}
                        <div className="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 className="font-display font-bold text-xs text-stone-900 line-clamp-1 group-hover:text-[#881B1E] transition-colors">
                                    {item.nama}
                                </h3>
                                <p className="text-[10px] text-stone-500 line-clamp-1 mt-0.5">
                                    {item.deskripsi_singkat}
                                </p>
                            </div>

                            <div className="mt-2.5 flex items-center justify-between gap-2 pt-2 border-t border-stone-100">
                                <div>
                                    <div className="text-xs font-black text-[#881B1E]">
                                        {formatRupiah(item.harga)}
                                    </div>
                                    {item.harga_coret && (
                                        <div className="text-[9px] text-stone-400 line-through">
                                            {formatRupiah(item.harga_coret)}
                                        </div>
                                    )}
                                </div>

                                <button
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onSelectItem(item);
                                    }}
                                    className="flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-400 hover:bg-amber-500 text-stone-950 font-bold text-[11px] shadow-xs active:scale-95 transition"
                                >
                                    <Plus className="w-3 h-3" />
                                    <span>Tambah</span>
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}
