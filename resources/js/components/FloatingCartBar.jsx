import React from 'react';
import { useCart } from '../context/CartContext';
import { ShoppingBag, ChevronRight } from 'lucide-react';

export default function FloatingCartBar() {
    const { totalItemCount, subtotal, setIsCartOpen } = useCart();

    if (totalItemCount === 0) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    return (
        <div className="fixed bottom-3 inset-x-0 z-40 px-4 max-w-7xl mx-auto pointer-events-none animate-slide-up">
            <div
                onClick={() => setIsCartOpen(true)}
                className="bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white p-2.5 pl-3 rounded-2xl shadow-2xl border border-amber-500/30 flex items-center justify-between pointer-events-auto cursor-pointer hover:shadow-amber-950/40 hover:scale-[1.01] active:scale-[0.99] transition-all"
            >
                {/* Left Side: Cart Icon & Total Label */}
                <div className="flex items-center gap-3">
                    <div className="relative w-10 h-10 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center shadow-md">
                        <ShoppingBag className="w-5 h-5 fill-stone-950" />
                        <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white rounded-full text-[10px] font-black flex items-center justify-center border-2 border-[#7A1517] animate-pulse">
                            {totalItemCount}
                        </span>
                    </div>

                    <div>
                        <span className="text-[10px] font-bold tracking-wider text-amber-300 uppercase block leading-tight">
                            Total Sementara
                        </span>
                        <span className="font-display font-black text-sm text-white leading-tight">
                            {formatRupiah(subtotal)}
                        </span>
                    </div>
                </div>

                {/* Right Side: View Cart Button */}
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        setIsCartOpen(true);
                    }}
                    className="flex items-center gap-1 px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-stone-950 font-display font-extrabold text-xs shadow-md active:scale-95 transition"
                >
                    <span>Lihat Pesanan</span>
                    <ChevronRight className="w-4 h-4" />
                </button>
            </div>
        </div>
    );
}
