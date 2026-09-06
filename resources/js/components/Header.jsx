import React from 'react';
import { useCart } from '../context/CartContext';
import { Bell, QrCode, UtensilsCrossed, Clock, ChevronDown } from 'lucide-react';

export default function Header() {
    const { tableInfo, setIsWaiterModalOpen, setIsTableModalOpen, setIsOrderStatusOpen, orders } = useCart();
    
    // Check if there are active orders
    const hasActiveOrders = orders.length > 0;

    return (
        <header className="sticky top-0 z-30 bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white shadow-lg border-b border-amber-500/20">
            <div className="max-w-md mx-auto px-4 py-3.5 flex items-center justify-between gap-3">
                {/* Left: Table Badge & Restaurant Info */}
                <button
                    onClick={() => setIsTableModalOpen(true)}
                    className="flex items-center gap-2.5 text-left group focus:outline-none"
                    title="Ganti / Scan Meja"
                >
                    <div className="w-10 h-10 rounded-2xl bg-amber-500/20 border border-amber-400/40 flex items-center justify-center font-bold text-amber-300 font-display text-base shadow-inner group-hover:bg-amber-500/30 transition">
                        {tableInfo.number}
                    </div>
                    <div>
                        <div className="flex items-center gap-1.5">
                            <h1 className="font-display font-extrabold text-base tracking-tight text-white flex items-center gap-1">
                                Kedai Dynasty
                            </h1>
                            <ChevronDown className="w-3.5 h-3.5 text-amber-300/80 group-hover:translate-y-0.5 transition-transform" />
                        </div>
                        <p className="text-[11px] font-medium text-amber-200/90 flex items-center gap-1">
                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Dine-In • Meja NO. {tableInfo.number}
                        </p>
                    </div>
                </button>

                {/* Right: Actions */}
                <div className="flex items-center gap-2">
                    {/* Active Order Button if exists */}
                    {hasActiveOrders && (
                        <button
                            onClick={() => setIsOrderStatusOpen(true)}
                            className="flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold text-amber-200 transition"
                            title="Status Pesanan"
                        >
                            <Clock className="w-3.5 h-3.5 text-amber-300" />
                            <span className="hidden xs:inline">Status</span>
                        </button>
                    )}

                    {/* QR Code / Table Switcher */}
                    <button
                        onClick={() => setIsTableModalOpen(true)}
                        className="p-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-amber-200 transition"
                        title="Scan QR Meja"
                    >
                        <QrCode className="w-4 h-4" />
                    </button>

                    {/* Pelayan / Call Waiter Button */}
                    <button
                        onClick={() => setIsWaiterModalOpen(true)}
                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-stone-950 font-bold text-xs shadow-md shadow-amber-500/30 hover:scale-[1.02] active:scale-95 transition-all"
                    >
                        <Bell className="w-3.5 h-3.5 fill-stone-950" />
                        <span>Pelayan</span>
                    </button>
                </div>
            </div>
        </header>
    );
}
