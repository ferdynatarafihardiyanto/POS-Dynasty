import React from 'react';
import { Plus, Star, Sparkles } from 'lucide-react';

export default function MenuCard({ item, onSelect }) {
    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    return (
        <div
            onClick={() => onSelect(item)}
            className="bg-white rounded-2xl border border-stone-200/80 shadow-xs hover:shadow-md overflow-hidden cursor-pointer flex flex-col justify-between group transition-all duration-200 active:scale-[0.98]"
        >
            {/* Image Container */}
            <div className="relative aspect-4/3 w-full overflow-hidden bg-stone-100">
                <img
                    src={item.gambar}
                    alt={item.nama}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                />

                {/* Badges Overlay (Kategori Asli POS) */}
                <div className="absolute top-2 left-2 flex flex-col gap-1">
                    {item.kategori_nama && (
                        <span className="bg-stone-900/80 backdrop-blur-xs text-white font-bold text-[9px] px-2 py-0.5 rounded-full shadow-xs">
                            {item.kategori_nama}
                        </span>
                    )}
                </div>
            </div>

            {/* Information Body */}
            <div className="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 className="font-display font-bold text-xs text-stone-900 line-clamp-1 group-hover:text-[#881B1E] transition-colors">
                        {item.nama}
                    </h3>
                    <p className="text-[10px] text-stone-500 line-clamp-2 mt-1 leading-relaxed">
                        {item.deskripsi_singkat || item.deskripsi}
                    </p>
                </div>

                {/* Price and Add button */}
                <div className="mt-3 pt-2 border-t border-stone-100 flex items-center justify-between gap-1.5">
                    <div>
                        <span className="text-xs font-black text-[#881B1E] block">
                            {formatRupiah(item.harga)}
                        </span>
                        {item.harga_coret && (
                            <span className="text-[9px] text-stone-400 line-through block">
                                {formatRupiah(item.harga_coret)}
                            </span>
                        )}
                    </div>

                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            onSelect(item);
                        }}
                        className="flex items-center gap-1 px-2.5 py-1 rounded-xl bg-gradient-to-r from-[#881B1E] to-[#A32023] hover:from-[#751417] hover:to-[#881B1E] text-white font-bold text-[10px] shadow-xs active:scale-95 transition"
                    >
                        <Plus className="w-3 h-3" />
                        <span>Tambah</span>
                    </button>
                </div>
            </div>
        </div>
    );
}
