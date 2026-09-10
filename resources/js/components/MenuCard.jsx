import React from 'react';
import { Plus } from 'lucide-react';

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
            className="bg-white rounded-xl border border-stone-200 shadow-xs hover:border-stone-300 transition-colors duration-150 overflow-hidden cursor-pointer flex flex-col justify-between group"
        >
            {/* Image Container */}
            <div className="relative aspect-4/3 w-full overflow-hidden bg-stone-100">
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

            {/* Information Body */}
            <div className="p-3 flex-1 flex flex-col justify-between">
                <div>
                    <h3 className="font-display font-semibold text-xs sm:text-sm text-stone-900 line-clamp-1 group-hover:text-[#82181A] transition-colors">
                        {item.nama}
                    </h3>
                    <p className="text-[10px] sm:text-[11px] text-stone-500 line-clamp-2 mt-0.5 leading-relaxed">
                        {item.deskripsi_singkat || item.deskripsi}
                    </p>
                </div>

                {/* Price and Add button */}
                <div className="mt-2.5 pt-2 border-t border-stone-100 flex items-center justify-between gap-1.5">
                    <div>
                        <span className="text-xs sm:text-sm font-bold text-[#82181A] block">
                            {formatRupiah(item.harga)}
                        </span>
                        {item.harga_coret && (
                            <span className="text-[9px] text-stone-400 line-through block">
                                {formatRupiah(item.harga_coret)}
                            </span>
                        )}
                    </div>

                    <button
                        type="button"
                        onClick={(e) => {
                            e.stopPropagation();
                            onSelect(item);
                        }}
                        className="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-lg bg-[#82181A] hover:bg-[#6e1214] text-white font-medium text-[11px] sm:text-xs transition active:scale-95 cursor-pointer"
                    >
                        <Plus className="w-3.5 h-3.5 stroke-[2]" />
                        <span>Tambah</span>
                    </button>
                </div>
            </div>
        </div>
    );
}
