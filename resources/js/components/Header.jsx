import React from 'react';
import { useCart } from '../context/CartContext';
import { Bell, QrCode, Clock, ChevronDown } from 'lucide-react';

export default function Header() {
    const { tableInfo, setIsWaiterModalOpen, setIsTableModalOpen, setIsOrderStatusOpen, orders } = useCart();
    
    // Check if there are active orders
    const hasActiveOrders = orders.length > 0;

    return (
        <header className="sticky top-0 z-30 bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] text-white shadow-lg border-b border-amber-500/20">
            <div className="max-w-md mx-auto px-4 py-3 flex items-center justify-between gap-3">
                {/* Left: Table Badge & Restaurant Info */}
                <button
                    onClick={() => setIsTableModalOpen(true)}
                    className="flex items-center gap-2.5 text-left group focus:outline-none cursor-pointer"
                    title="Ganti / Scan Meja"
                >
                    <div className="w-10 h-10 rounded-2xl bg-amber-400/20 border border-amber-400/40 flex items-center justify-center font-bold text-amber-300 font-display text-sm shadow-inner group-hover:bg-amber-400/30 transition">
                        {tableInfo?.number || '01'}
                    </div>
                    <div>
                        <div className="flex items-center gap-1">
                            <h1 className="font-display font-extrabold text-sm tracking-tight text-white flex items-center gap-1">
                                Kedai Dynasty
                            </h1>
                            <ChevronDown className="w-3.5 h-3.5 text-amber-300/80 group-hover:translate-y-0.5 transition-transform" />
                        </div>
                        <p className="text-[11px] font-medium text-amber-200/90 flex items-center gap-1">
                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Dine-In • {tableInfo?.name || `Meja ${tableInfo?.number || '01'}`}
                        </p>
                    </div>
                </button>

                {/* Right: Actions */}
                <div className="flex items-center gap-1.5">
                    {/* Active Order Button if exists */}
                    {hasActiveOrders && (
                        <button
                            onClick={() => setIsOrderStatusOpen(true)}
                            className="flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold text-amber-200 transition cursor-pointer"
                            title="Status Pesanan"
                        >
                            <Clock className="w-3.5 h-3.5 text-amber-300 animate-spin" style={{ animationDuration: '6s' }} />
                            <span>Pesanan</span>
                        </button>
                    )}

                    {/* QR Code / Table Switcher */}
                    <button
                        onClick={() => setIsTableModalOpen(true)}
                        className="p-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-amber-200 transition cursor-pointer"
                        title="Scan QR Meja"
                    >
                        <QrCode className="w-4 h-4" />
                    </button>

                    {/* Pelayan / Call Waiter Button */}
                    <button
                        onClick={() => setIsWaiterModalOpen(true)}
                        className="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-stone-950 font-bold text-xs shadow-md shadow-amber-500/20 hover:scale-[1.02] active:scale-95 transition-all cursor-pointer"
                    >
                        <Bell className="w-3.5 h-3.5 fill-stone-950" />
                        <span>Pelayan</span>
                    </button>
                </div>
            </div>
        </header>
    );
}

