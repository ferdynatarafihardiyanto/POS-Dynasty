import React from 'react';
import { Plus, Star } from 'lucide-react';
import { useCart } from '../context/CartContext';

export default function MenuCard({ item, onSelect }) {
    const { addToCart } = useCart();

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const isOutOfStock = typeof item.stok === 'number' && item.stok <= 0;
    const isLowStock = typeof item.stok === 'number' && item.stok > 0 && item.stok <= 5;

    const handleQuickAdd = (e) => {
        e.stopPropagation();
        if (isOutOfStock) return;
        addToCart(item, 1);
    };

    return (
        <div
            onClick={() => onSelect(item)}
            className={`bg-white rounded-2xl border border-stone-200/80 shadow-xs hover:shadow-md overflow-hidden flex flex-col justify-between group transition-all duration-200 cursor-pointer active:scale-[0.98] ${
                isOutOfStock ? 'opacity-65 grayscale-[20%]' : ''
            }`}
        >
            {/* Image Container */}
            <div className="relative aspect-4/3 w-full overflow-hidden bg-stone-100">
                <img
                    src={item.gambar}
                    alt={item.nama}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                />

                {/* Badges Overlay */}
                <div className="absolute top-2 left-2 flex flex-col gap-1">
                    {isOutOfStock ? (
                        <span className="bg-stone-800 text-white font-extrabold text-[9px] px-2 py-0.5 rounded-full shadow-sm">
                            Stok Habis
                        </span>
                    ) : isLowStock ? (
                        <span className="bg-amber-500 text-stone-950 font-black text-[9px] px-2 py-0.5 rounded-full shadow-sm">
                            Sisa {item.stok}
                        </span>
                    ) : item.kategori?.nama ? (
                        <span className="bg-[#82181A]/90 backdrop-blur-xs text-white font-bold text-[9px] px-2 py-0.5 rounded-full shadow-sm">
                            {item.kategori.nama}
                        </span>
                    ) : null}
                </div>

                {/* Rating Badge */}
                <div className="absolute bottom-2 right-2 bg-stone-900/80 backdrop-blur-xs text-white text-[10px] font-bold px-1.5 py-0.5 rounded-lg flex items-center gap-0.5">
                    <Star className="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                    <span>{item.rating || '4.8'}</span>
                </div>
            </div>

            {/* Information Body */}
            <div className="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 className="font-display font-bold text-xs text-stone-900 line-clamp-1 group-hover:text-[#82181A] transition-colors">
                        {item.nama}
                    </h3>
                    <p className="text-[10px] text-stone-500 line-clamp-2 mt-1 leading-relaxed">
                        {item.deskripsi || item.deskripsi_singkat}
                    </p>
                </div>

                {/* Price and Add button */}
                <div className="mt-3 pt-2 border-t border-stone-100 flex items-center justify-between gap-1.5">
                    <div>
                        <span className="text-xs font-black text-[#82181A] block">
                            {formatRupiah(item.harga)}
                        </span>
                    </div>

                    <button
                        onClick={handleQuickAdd}
                        disabled={isOutOfStock}
                        className={`flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold text-[10px] shadow-xs transition active:scale-95 cursor-pointer ${
                            isOutOfStock
                                ? 'bg-stone-200 text-stone-400 cursor-not-allowed'
                                : 'bg-gradient-to-r from-[#82181A] to-[#A32023] hover:from-[#661012] hover:to-[#82181A] text-white'
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
}

