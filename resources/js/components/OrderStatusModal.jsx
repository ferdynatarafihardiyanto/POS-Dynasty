import React from 'react';
import { useCart } from '../context/CartContext';
import { ArrowLeft, CheckCircle2, Clock, ChefHat, BellRing, Sparkles, ReceiptText, Plus, Bell } from 'lucide-react';

export default function OrderStatusModal() {
    const {
        isOrderStatusOpen,
        setIsOrderStatusOpen,
        activeOrder,
        orders,
        setActiveOrder,
        setIsWaiterModalOpen,
        tableInfo
    } = useCart();

    if (!isOrderStatusOpen) return null;

    const currentOrder = activeOrder || orders[0];

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const statusSteps = [
        { key: 'menunggu_konfirmasi', label: 'Pesanan Diterima', desc: 'Dapur telah menerima tiket pesananmu', icon: CheckCircle2 },
        { key: 'diproses', label: 'Sedang Dimasak di Dapur', desc: 'Koki sedang menyiapkan hidangan hangatmu', icon: ChefHat },
        { key: 'disajikan', label: 'Siap Disajikan ke Meja', desc: 'Pelayan sedang mengantarkan makanan', icon: BellRing },
        { key: 'selesai', label: 'Selesai / Menikmati Makanan', desc: 'Selamat menikmati hidangan lezatmu!', icon: Sparkles }
    ];

    const getCurrentStepIndex = () => {
        if (!currentOrder) return 0;
        const status = (currentOrder.status || 'menunggu_konfirmasi').toLowerCase();
        if (status.includes('selesai') || status.includes('paid')) return 3;
        if (status.includes('disajikan') || status.includes('ready')) return 2;
        if (status.includes('diproses') || status.includes('proses') || status.includes('cook')) return 1;
        return 0;
    };

    const activeStepIdx = getCurrentStepIndex();

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[92vh] sm:h-[88vh] bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <button
                        onClick={() => setIsOrderStatusOpen(false)}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>
                    <div className="text-center">
                        <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                            Status Pesanan
                        </h2>
                        <p className="text-[11px] font-medium text-[#881B1E]">
                            Dine-in • Meja {currentOrder?.tableNumber || tableInfo.number}
                        </p>
                    </div>
                    <div className="w-9"></div>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4 pb-28">
                    {!currentOrder ? (
                        <div className="py-16 text-center">
                            <Clock className="w-12 h-12 text-stone-300 mx-auto mb-2" />
                            <h3 className="font-display font-bold text-stone-700 text-sm">Belum Ada Pesanan Aktif</h3>
                            <p className="text-xs text-stone-400 mt-1">Silakan pilih hidangan di menu dan kirim pesanan.</p>
                        </div>
                    ) : (
                        <>
                            {/* Order Number Banner */}
                            <div className="bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white p-4 rounded-2xl shadow-md flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] uppercase font-bold text-amber-300 block tracking-wider">
                                        Nomor Pesanan
                                    </span>
                                    <h3 className="font-display font-extrabold text-lg text-white">
                                        #{currentOrder.orderNumber}
                                    </h3>
                                    <span className="text-[11px] text-amber-200/80">
                                        {new Date(currentOrder.timestamp || Date.now()).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WIB
                                    </span>
                                </div>
                                <div className="text-right">
                                    <span className="text-[10px] font-bold text-amber-300 block">
                                        Meja
                                    </span>
                                    <span className="font-display font-black text-2xl text-white">
                                        {currentOrder.tableNumber}
                                    </span>
                                </div>
                            </div>

                            {/* Stepper Tracking Progress */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/80 space-y-4">
                                <h4 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                    Pelacakan Langsung
                                </h4>

                                <div className="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-stone-200 before:z-0">
                                    {statusSteps.map((step, idx) => {
                                        const StepIcon = step.icon;
                                        const isCompleted = idx <= activeStepIdx;
                                        const isCurrent = idx === activeStepIdx;

                                        return (
                                            <div key={step.key} className="flex items-start gap-3 relative z-10">
                                                <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs transition-colors shrink-0 ${
                                                    isCompleted
                                                        ? 'bg-amber-400 text-stone-950 font-black shadow-sm ring-4 ring-amber-100'
                                                        : 'bg-stone-100 text-stone-400'
                                                }`}>
                                                    <StepIcon className="w-3.5 h-3.5" />
                                                </div>

                                                <div className="flex-1">
                                                    <div className="flex items-center justify-between">
                                                        <span className={`font-display text-xs ${
                                                            isCurrent
                                                                ? 'font-extrabold text-[#881B1E]'
                                                                : isCompleted
                                                                    ? 'font-bold text-stone-800'
                                                                    : 'font-medium text-stone-400'
                                                        }`}>
                                                            {step.label}
                                                        </span>
                                                        {isCurrent && (
                                                            <span className="text-[9px] font-extrabold bg-amber-100 text-amber-900 px-1.5 py-0.5 rounded-full animate-pulse">
                                                                Sedang Berlangsung
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-[10px] text-stone-500 mt-0.5">
                                                        {step.desc}
                                                    </p>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Order Details Struk */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/80 space-y-3">
                                <div className="flex items-center gap-2 border-b border-stone-100 pb-2">
                                    <ReceiptText className="w-4 h-4 text-[#881B1E]" />
                                    <h4 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                        Rincian Menu Dipesan
                                    </h4>
                                </div>

                                <div className="space-y-2.5 divide-y divide-stone-100 text-xs">
                                    {currentOrder.items?.map((item, idx) => (
                                        <div key={idx} className="pt-2 first:pt-0 flex justify-between gap-2">
                                            <div className="flex-1">
                                                <div className="font-bold text-stone-800">
                                                    {item.quantity}x {item.menuItem?.nama || item.nama}
                                                </div>
                                                <div className="text-[10px] text-stone-500 space-x-1">
                                                    {item.customizations?.carb && <span>• {item.customizations.carb.name}</span>}
                                                    {item.customizations?.spice && <span>• {item.customizations.spice.name}</span>}
                                                    {item.customizations?.toppings?.map(t => (
                                                        <span key={t.id}>• {t.name}</span>
                                                    ))}
                                                </div>
                                                {item.notes && (
                                                    <div className="text-[10px] text-amber-800 italic">
                                                        "{item.notes}"
                                                    </div>
                                                )}
                                            </div>
                                            <span className="font-bold text-stone-900 shrink-0">
                                                {formatRupiah(item.totalPrice || (item.harga * item.jumlah))}
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                <div className="pt-2 border-t border-dashed border-stone-300 space-y-1 text-xs text-stone-600">
                                    <div className="flex justify-between">
                                        <span>Subtotal</span>
                                        <span>{formatRupiah(currentOrder.subtotal || currentOrder.grandTotal)}</span>
                                    </div>
                                    {currentOrder.tax > 0 && (
                                        <div className="flex justify-between">
                                            <span>Pajak Restoran PB1 (10%)</span>
                                            <span>{formatRupiah(currentOrder.tax)}</span>
                                        </div>
                                    )}
                                    <div className="flex justify-between font-display font-black text-sm text-[#881B1E] pt-1">
                                        <span>Total Bayar</span>
                                        <span>{formatRupiah(currentOrder.grandTotal)}</span>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                </div>

                {/* Bottom Action Floating */}
                <div className="absolute bottom-0 inset-x-0 bg-white/95 backdrop-blur-md p-4 border-t border-stone-200/80 shadow-2xl flex items-center gap-2.5">
                    <button
                        onClick={() => {
                            setIsOrderStatusOpen(false);
                            setIsWaiterModalOpen(true);
                        }}
                        className="py-3 px-3 rounded-2xl bg-amber-400 hover:bg-amber-300 text-stone-950 font-display font-bold text-xs shadow-xs active:scale-95 transition flex items-center justify-center gap-1.5 shrink-0"
                    >
                        <Bell className="w-3.5 h-3.5 fill-stone-950" />
                        <span>Panggil Pelayan</span>
                    </button>

                    <button
                        onClick={() => setIsOrderStatusOpen(false)}
                        className="flex-1 py-3 px-4 rounded-2xl bg-[#881B1E] hover:bg-[#731417] text-white font-display font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5"
                    >
                        <Plus className="w-4 h-4" />
                        <span>Pesan Menu Tambahan</span>
                    </button>
                </div>

            </div>
        </div>
    );
}
