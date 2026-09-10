import React from 'react';
import { useCart } from '../context/CartContext';
import { Clock, ReceiptText } from 'lucide-react';

export default function Header() {
    const {
        tableInfo,
        setIsOrderStatusOpen,
        setIsOrderHistoryOpen,
        fetchOrderHistory,
        orders
    } = useCart();
    
    // Check if there are active orders
    const hasActiveOrders = orders.length > 0;

    return (
        <header className="sticky top-0 z-30 bg-[#82181A] text-white border-b border-black/15 shadow-xs w-full">
            <div className="w-full px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 flex items-center justify-between gap-3">
                {/* Left: Table Indicator & Restaurant Info */}
                <div className="flex items-center gap-2.5 text-left">
                    <div className="flex flex-col items-center justify-center px-2 py-1 rounded-lg bg-black/20 border border-white/10 min-w-[34px] sm:min-w-[38px]">
                        <span className="text-[9px] uppercase tracking-wider text-amber-300 font-medium leading-none">Meja</span>
                        <span className="font-bold text-white text-xs sm:text-sm leading-tight">{tableInfo.number}</span>
                    </div>
                    <div>
                        <h1 className="font-display font-bold text-sm sm:text-base text-white tracking-tight leading-tight">
                            Kedai Dynasty
                        </h1>
                        <p className="text-[11px] text-amber-100/80 flex items-center gap-1.5 mt-0.5">
                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                            <span>Dine-In • Meja {tableInfo.number}</span>
                        </p>
                    </div>
                </div>

                {/* Right: Actions */}
                <div className="flex items-center gap-1.5 sm:gap-2">
                    {/* Active Order Button if exists */}
                    {hasActiveOrders && (
                        <button
                            type="button"
                            onClick={() => setIsOrderStatusOpen(true)}
                            className="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 active:bg-white/20 border border-white/10 text-xs font-medium text-white transition cursor-pointer"
                            title="Status Pesanan Terakhir"
                        >
                            <Clock className="w-3.5 h-3.5 text-amber-300 shrink-0" />
                            <span className="hidden xs:inline">Status</span>
                        </button>
                    )}

                    {/* Riwayat Pesanan Button */}
                    <button
                        type="button"
                        onClick={() => {
                            fetchOrderHistory();
                            setIsOrderHistoryOpen(true);
                        }}
                        className="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/15 active:bg-white/20 border border-white/10 text-xs font-medium text-white transition cursor-pointer"
                        title="Riwayat Pesanan Meja Ini"
                    >
                        <ReceiptText className="w-3.5 h-3.5 text-amber-300 shrink-0" />
                        <span>Riwayat</span>
                        {orders.length > 0 && (
                            <span className="px-1.5 py-0.2 min-w-[18px] text-center rounded-full bg-amber-400 text-stone-950 font-bold text-[10px] leading-tight">
                                {orders.length}
                            </span>
                        )}
                    </button>
                </div>
            </div>
        </header>
    );
}
