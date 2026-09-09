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
        <header className="sticky top-0 z-30 bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white shadow-lg border-b border-amber-500/20 w-full">
            <div className="w-full px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-3">
                {/* Left: Static Table Badge & Restaurant Info (Terkunci dari QR Meja) */}
                <div className="flex items-center gap-2.5 text-left">
                    <div className="w-10 h-10 rounded-2xl bg-amber-500/20 border border-amber-400/40 flex items-center justify-center font-bold text-amber-300 font-display text-base shadow-inner">
                        {tableInfo.number}
                    </div>
                    <div>
                        <h1 className="font-display font-extrabold text-base tracking-tight text-white">
                            Kedai Dynasty
                        </h1>
                        <p className="text-[11px] font-medium text-amber-200/90 flex items-center gap-1">
                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Dine-In • Meja NO. {tableInfo.number}
                        </p>
                    </div>
                </div>

                {/* Right: Actions */}
                <div className="flex items-center gap-2">
                    {/* Active Order Button if exists */}
                    {hasActiveOrders && (
                        <button
                            onClick={() => setIsOrderStatusOpen(true)}
                            className="flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold text-amber-200 transition"
                            title="Status Pesanan Terakhir"
                        >
                            <Clock className="w-3.5 h-3.5 text-amber-300" />
                            <span className="hidden xs:inline">Status</span>
                        </button>
                    )}

                    {/* Riwayat Pesanan Button */}
                    <button
                        onClick={() => {
                            fetchOrderHistory();
                            setIsOrderHistoryOpen(true);
                        }}
                        className="flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold text-amber-200 transition"
                        title="Riwayat Pesanan Meja Ini"
                    >
                        <ReceiptText className="w-3.5 h-3.5 text-amber-300" />
                        <span className="hidden sm:inline">Riwayat</span>
                        {orders.length > 0 && (
                            <span className="w-4 h-4 rounded-full bg-amber-400 text-stone-950 font-black text-[9px] flex items-center justify-center">
                                {orders.length}
                            </span>
                        )}
                    </button>
                </div>
            </div>
        </header>
    );
}
