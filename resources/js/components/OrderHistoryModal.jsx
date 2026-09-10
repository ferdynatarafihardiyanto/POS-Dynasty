import React from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, Clock, CheckCircle2, ChefHat, BellRing, Sparkles, RefreshCw, ReceiptText, ChevronRight, XCircle } from 'lucide-react';

export default function OrderHistoryModal() {
    const {
        isOrderHistoryOpen,
        setIsOrderHistoryOpen,
        orders,
        setActiveOrder,
        setIsOrderStatusOpen,
        tableInfo,
        fetchOrderHistory,
        clearDeviceSession
    } = useCart();

    if (!isOrderHistoryOpen) return null;

    const formatRupiah = (num) => {
        const val = parseFloat(num) || 0;
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(val).replace('IDR', 'Rp');
    };

    const getStatusBadge = (order) => {
        const s = (order?.status || '').toLowerCase();
        const isPaid = order?.status_pembayaran === 'dibayar' || s === 'dibayar' || s === 'selesai';

        if (s === 'selesai') {
            return {
                label: 'Selesai',
                bg: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                icon: CheckCircle2
            };
        }
        if (s === 'disajikan') {
            return {
                label: 'Sudah Dikirim ke Meja',
                bg: 'bg-teal-100 text-teal-800 border-teal-200',
                icon: BellRing
            };
        }
        if (s === 'diproses' || (isPaid && (s === 'menunggu_pembayaran' || s === 'menunggu_konfirmasi'))) {
            return {
                label: 'Sedang Dimasak',
                bg: 'bg-indigo-100 text-indigo-800 border-indigo-200',
                icon: ChefHat
            };
        }
        if (s === 'dibatalkan' || s === 'batal') {
            return {
                label: 'Dibatalkan',
                bg: 'bg-rose-100 text-rose-800 border-rose-200',
                icon: XCircle
            };
        }
        return {
            label: 'Menunggu Pembayaran',
            bg: 'bg-amber-100 text-amber-900 border-amber-200',
            icon: Clock
        };
    };

    const handleSelectOrder = (order) => {
        setActiveOrder(order);
        setIsOrderHistoryOpen(false);
        setIsOrderStatusOpen(true);
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-lg h-[92vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3.5 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <button
                        onClick={() => setIsOrderHistoryOpen(false)}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>
                    <div className="text-center">
                        <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                            Riwayat Pesanan Meja
                        </h2>
                        <p className="text-[11px] font-medium text-[#881B1E]">
                            Dine-in • Meja {tableInfo.number}
                        </p>
                    </div>
                    <button
                        onClick={() => fetchOrderHistory()}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95"
                        title="Segarkan Riwayat"
                    >
                        <RefreshCw className="w-4 h-4 text-stone-600" />
                    </button>
                </div>

                {/* Content List */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-3.5">
                    {orders.length === 0 ? (
                        <div className="py-20 text-center">
                            <ReceiptText className="w-12 h-12 text-stone-300 mx-auto mb-2.5" />
                            <h3 className="font-display font-bold text-stone-700 text-sm">
                                Belum Ada Riwayat Pesanan
                            </h3>
                            <p className="text-xs text-stone-400 mt-1 max-w-xs mx-auto">
                                Semua pesanan yang dikirim oleh Meja {tableInfo.number} akan tercatat rapi di sini.
                            </p>
                            <button
                                onClick={() => setIsOrderHistoryOpen(false)}
                                className="mt-4 px-4 py-2 bg-[#881B1E] text-white rounded-xl text-xs font-bold shadow-xs active:scale-95 transition"
                            >
                                Mulai Pesan Makanan
                            </button>
                        </div>
                    ) : (
                        orders.map((order, idx) => {
                            const badge = getStatusBadge(order);
                            const BadgeIcon = badge.icon;
                            const isPaid = order.status_pembayaran === 'dibayar' || order.status === 'selesai';

                            return (
                                <div
                                    key={order.orderNumber || idx}
                                    onClick={() => handleSelectOrder(order)}
                                    className="bg-white rounded-2xl border border-stone-200/80 p-4 shadow-xs hover:border-amber-400 transition cursor-pointer active:scale-[0.99] group space-y-3"
                                >
                                    {/* Card Top: Order Number, Date, Status */}
                                    <div className="flex items-start justify-between gap-2 border-b border-stone-100 pb-2.5">
                                        <div>
                                            <div className="flex items-center gap-1.5">
                                                <span className="font-display font-extrabold text-sm text-stone-900 group-hover:text-[#881B1E] transition">
                                                    #{order.orderNumber}
                                                </span>
                                            </div>
                                            <span className="text-[11px] text-stone-400">
                                                {new Date(order.timestamp || Date.now()).toLocaleString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'short',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })} WIB
                                            </span>
                                        </div>

                                        <div className="flex flex-col items-end gap-1">
                                            {/* Kitchen/Serving Status Badge */}
                                            <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border ${badge.bg}`}>
                                                <BadgeIcon className="w-3 h-3" />
                                                <span>{badge.label}</span>
                                            </span>
                                            {/* Payment Badge */}
                                            <span className={`text-[10px] font-bold px-2 py-0.5 rounded-md ${
                                                isPaid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800'
                                            }`}>
                                                {isPaid ? '✓ Lunas' : '• Belum Bayar'}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Items Preview */}
                                    <div className="space-y-1.5 text-xs text-stone-700">
                                        {order.items?.map((item, itemIdx) => (
                                            <div key={itemIdx} className="flex justify-between items-start">
                                                <div className="flex-1 pr-2">
                                                    <span className="font-semibold text-stone-800">
                                                        {item.quantity}x {item.menuItem?.nama || item.nama}
                                                    </span>
                                                    {/* Modifiers tags */}
                                                    {(item.customizations?.modifiers?.length > 0) && (
                                                        <div className="text-[10px] text-stone-500">
                                                            {item.customizations.modifiers.map(m => `+ ${m.nama}`).join(', ')}
                                                        </div>
                                                    )}
                                                    {item.notes && (
                                                        <div className="text-[10px] text-amber-800 italic">
                                                            "{item.notes}"
                                                        </div>
                                                    )}
                                                </div>
                                                <span className="font-medium text-stone-600 shrink-0">
                                                    {formatRupiah(item.totalPrice || ((item.unitPrice || item.harga) * item.quantity))}
                                                </span>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Card Footer: Total & Track Button */}
                                    <div className="flex items-center justify-between pt-2.5 border-t border-stone-100">
                                        <div>
                                            <span className="text-[10px] text-stone-400 uppercase font-semibold block">
                                                Total Pembayaran
                                            </span>
                                            <span className="font-display font-extrabold text-sm text-[#881B1E]">
                                                {formatRupiah(order.grandTotal)}
                                            </span>
                                        </div>

                                        <button
                                            type="button"
                                            className="flex items-center gap-1 text-xs font-bold text-[#881B1E] group-hover:translate-x-0.5 transition-transform"
                                        >
                                            <span>Lihat Status</span>
                                            <ChevronRight className="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>

                {/* Footer button */}
                <div className="p-4 bg-white border-t border-stone-200/80 shadow-xs flex items-center gap-2">
                    {orders.length > 0 && (
                        <button
                            type="button"
                            onClick={clearDeviceSession}
                            className="py-3 px-4 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-display font-bold text-xs shadow-xs active:scale-95 transition"
                            title="Bersihkan riwayat dan mulai sesi pesanan baru"
                        >
                            Reset Sesi
                        </button>
                    )}
                    <button
                        onClick={() => setIsOrderHistoryOpen(false)}
                        className="flex-1 py-3 px-4 rounded-2xl bg-[#881B1E] hover:bg-[#731417] text-white font-display font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5"
                    >
                        <span>Tutup Riwayat</span>
                    </button>
                </div>

            </div>
        </div>
    );
}
