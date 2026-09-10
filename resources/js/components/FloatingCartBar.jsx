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
        <div className="fixed bottom-4 inset-x-0 z-40 px-4 max-w-lg sm:max-w-xl mx-auto pointer-events-none">
            <div
                onClick={() => setIsCartOpen(true)}
                className="bg-[#82181A] text-white p-2.5 sm:p-3 rounded-xl shadow-lg border border-black/15 flex items-center justify-between pointer-events-auto cursor-pointer transition-transform duration-150 active:scale-[0.99]"
            >
                {/* Left Side: Cart Icon, Item Count & Subtotal */}
                <div className="flex items-center gap-2.5 sm:gap-3">
                    <div className="relative flex items-center justify-center w-9 h-9 rounded-lg bg-black/20 border border-white/10 text-amber-300 shrink-0">
                        <ShoppingBag className="w-4 h-4 stroke-[2]" />
                        <span className="absolute -top-1 -right-1 min-w-[18px] px-1 py-0.2 rounded-full bg-amber-400 text-stone-950 text-[10px] font-bold text-center leading-tight">
                            {totalItemCount}
                        </span>
                    </div>

                    <div>
                        <span className="text-[10px] text-amber-100/80 font-normal leading-none block">
                            Total sementara
                        </span>
                        <span className="font-display font-bold text-sm sm:text-base text-white leading-tight">
                            {formatRupiah(subtotal)}
                        </span>
                    </div>
                </div>

                {/* Right Side: View Cart Button */}
                <button
                    type="button"
                    onClick={(e) => {
                        e.stopPropagation();
                        setIsCartOpen(true);
                    }}
                    className="flex items-center gap-1 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg bg-amber-400 hover:bg-amber-300 text-stone-950 font-semibold text-xs sm:text-sm transition active:scale-95 cursor-pointer"
                >
                    <span>Lihat Pesanan</span>
                    <ChevronRight className="w-3.5 h-3.5 sm:w-4 sm:h-4 stroke-[2.25]" />
                </button>
            </div>
        </div>
    );
}
