import React from 'react';
import { useCart } from '../context/CartContext';
import { Sparkles, Plus, ChevronRight } from 'lucide-react';

export default function PromoBanner({ onSelectItem, onViewAll }) {
    const { promoItems } = useCart();

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    if (!promoItems || promoItems.length === 0) return null;

    return (
        <section className="pt-3 pb-2 w-full">
            {/* Header */}
            <div className="px-4 sm:px-6 lg:px-8 flex items-center justify-between mb-2">
                <div>
                    <div className="flex items-center gap-1.5">
                        <Sparkles className="w-3.5 h-3.5 text-amber-600 stroke-[2]" />
                        <h2 className="font-display font-bold text-sm sm:text-base text-stone-900 tracking-tight leading-tight">
                            Menu Favorit
                        </h2>
                    </div>
                    <p className="text-[11px] text-stone-500 mt-0.5">
                        Pilihan yang sering dipesan
                    </p>
                </div>
                {promoItems.length > 0 && (
                    <button
                        type="button"
                        onClick={onViewAll}
                        className="text-[11px] font-medium text-stone-500 hover:text-stone-800 flex items-center gap-0.5 transition cursor-pointer"
                    >
                        <span>Lihat Semua</span>
                        <ChevronRight className="w-3.5 h-3.5" />
                    </button>
                )}
            </div>

            {/* Horizontal Scroll Cards */}
            <div 
                className="flex gap-3 px-4 sm:px-6 lg:px-8 overflow-x-auto no-scrollbar pb-1.5 scroll-pl-4 sm:scroll-pl-6 lg:scroll-pl-8"
                style={{ WebkitOverflowScrolling: 'touch' }}
            >
                {promoItems.map((item) => (
                    <div
                        key={item.id}
                        onClick={() => onSelectItem(item)}
                        className="shrink-0 w-[200px] sm:w-[220px] bg-white rounded-xl border border-stone-200 shadow-xs hover:border-stone-300 transition-colors duration-150 overflow-hidden cursor-pointer flex flex-col justify-between group"
                    >
                        {/* Image Container */}
                        <div className="relative h-28 w-full overflow-hidden bg-stone-100">
                            <img
                                src={item.gambar}
                                alt={item.nama}
                                className="w-full h-full object-cover"
                                loading="lazy"
                                onError={(e) => {
                                    e.target.onerror = null;
                                    e.target.src = '/images/produk/americano.jpg';
                                }}
                            />
                            {/* Badge Kategori */}
                            {item.kategori_nama && (
                                <div className="absolute top-2 left-2">
                                    <span className="bg-stone-900/60 text-white font-medium text-[9px] px-1.5 py-0.5 rounded">
                                        {item.kategori_nama}
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Content */}
                        <div className="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 className="font-display font-semibold text-xs sm:text-sm text-stone-900 line-clamp-1 group-hover:text-[#82181A] transition-colors">
                                    {item.nama}
                                </h3>
                                <p className="text-[10px] sm:text-[11px] text-stone-500 line-clamp-1 mt-0.5">
                                    {item.deskripsi_singkat}
                                </p>
                            </div>

                            <div className="mt-2.5 flex items-center justify-between gap-2 pt-2 border-t border-stone-100">
                                <div>
                                    <div className="text-xs sm:text-sm font-bold text-[#82181A]">
                                        {formatRupiah(item.harga)}
                                    </div>
                                    {item.harga_coret && (
                                        <div className="text-[9px] text-stone-400 line-through">
                                            {formatRupiah(item.harga_coret)}
                                        </div>
                                    )}
                                </div>

                                <button
                                    type="button"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onSelectItem(item);
                                    }}
                                    className="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-lg bg-[#82181A] hover:bg-[#6e1214] text-white font-medium text-[11px] sm:text-xs transition active:scale-95 cursor-pointer"
                                >
                                    <Plus className="w-3.5 h-3.5 stroke-[2]" />
                                    <span>Tambah</span>
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
                {/* Trailing padding spacer to prevent clipping at the end of scroll */}
                <div className="shrink-0 w-1 sm:w-2" aria-hidden="true" />
            </div>
        </section>
    );
}
