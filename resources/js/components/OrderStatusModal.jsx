import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { 
    ArrowLeft, 
    CheckCircle2, 
    Clock, 
    ChefHat, 
    Utensils, 
    Sparkles, 
    ReceiptText, 
    Plus, 
    Bell, 
    RefreshCw,
    Check,
    Coffee
} from 'lucide-react';

export default function OrderStatusModal() {
    const {
        isOrderStatusOpen,
        setIsOrderStatusOpen,
        activeOrder,
        orders,
        setIsWaiterModalOpen,
        tableInfo,
        refreshOrders
    } = useCart();

    const [isRefreshing, setIsRefreshing] = useState(false);

    if (!isOrderStatusOpen) return null;

    const currentOrder = (activeOrder && activeOrder.qrToken === tableInfo?.token)
        ? activeOrder
        : (orders && orders.length > 0 ? orders[0] : null);

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num || 0).replace('IDR', 'Rp');
    };

    const statusSteps = [
        { 
            key: 'menunggu_pembayaran', 
            label: 'Pesanan Diterima', 
            desc: 'Pesanan telah masuk antrean dapur Kedai Dynasty', 
            icon: CheckCircle2 
        },
        { 
            key: 'diproses', 
            label: 'Sedang Diproses', 
            desc: 'Koki & barista sedang menyiapkan hidangan Anda', 
            icon: ChefHat 
        },
        { 
            key: 'disajikan', 
            label: 'Disajikan ke Meja', 
            desc: 'Pelayan sedang mengantarkan pesanan ke meja', 
            icon: Utensils 
        },
        { 
            key: 'selesai', 
            label: 'Selesai', 
            desc: 'Selamat menikmati hidangan di Kedai Dynasty!', 
            icon: Sparkles 
        }
    ];

    const getCurrentStepIndex = () => {
        if (!currentOrder) return 0;
        const status = (currentOrder.status || 'menunggu_pembayaran').toLowerCase();
        if (status.includes('selesai') || status.includes('paid') || status.includes('lunas') || status.includes('dibayar') || status.includes('complete')) return 3;
        if (status.includes('disajikan') || status.includes('ready') || status.includes('saji') || status.includes('served')) return 2;
        if (status.includes('diproses') || status.includes('proses') || status.includes('cook') || status.includes('kitchen') || status.includes('bikin')) return 1;
        return 0;
    };

    const activeStepIdx = getCurrentStepIndex();

    const handleManualRefresh = async () => {
        setIsRefreshing(true);
        if (refreshOrders) {
            await refreshOrders();
        }
        setTimeout(() => setIsRefreshing(false), 600);
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/70 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md h-[92vh] sm:h-[88vh] bg-[#FFFDF9] rounded-t-[32px] sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-slide-up relative border-t sm:border border-amber-500/20">

                {/* Header */}
                <div className="sticky top-0 z-20 bg-white/95 backdrop-blur-md px-4 py-3.5 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <button
                        onClick={() => setIsOrderStatusOpen(false)}
                        className="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95 cursor-pointer"
                        aria-label="Kembali ke menu"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </button>
                    <div className="text-center">
                        <h2 className="font-display font-extrabold text-sm text-stone-900 leading-tight">
                            Status Pesanan
                        </h2>
                        <p className="text-[11px] font-bold text-[#82181A]">
                            Dine-in • {tableInfo?.name || `Meja ${currentOrder?.tableNumber || tableInfo?.number || '01'}`}
                        </p>
                    </div>
                    <button
                        onClick={handleManualRefresh}
                        className={`w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition active:scale-95 cursor-pointer ${
                            isRefreshing ? 'animate-spin text-[#82181A]' : ''
                        }`}
                        title="Perbarui Status Pesanan"
                        aria-label="Refresh status"
                    >
                        <RefreshCw className="w-4 h-4" />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4 pb-32">
                    {!currentOrder ? (
                        <div className="py-20 text-center px-4">
                            <Clock className="w-12 h-12 text-stone-300 mx-auto mb-3" />
                            <h3 className="font-display font-extrabold text-stone-800 text-sm">Belum Ada Pesanan Aktif</h3>
                            <p className="text-xs text-stone-400 mt-1 max-w-xs mx-auto">
                                Silakan pilih hidangan di menu dan selesaikan pesanan Anda di meja ini.
                            </p>
                            <button
                                onClick={() => setIsOrderStatusOpen(false)}
                                className="mt-4 px-5 py-2.5 bg-[#82181A] text-white rounded-xl text-xs font-bold shadow-sm active:scale-95 transition cursor-pointer"
                            >
                                Lihat Menu Kedai
                            </button>
                        </div>
                    ) : (
                        <>
                            {/* Hero Order Card */}
                            <div className="bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] text-white p-4 rounded-2xl shadow-md space-y-2.5">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-1.5 bg-emerald-500/20 border border-emerald-400/30 px-2.5 py-0.5 rounded-full text-emerald-300 text-[10px] font-extrabold">
                                        <CheckCircle2 className="w-3.5 h-3.5 text-emerald-400" />
                                        <span>Pembayaran Berhasil ✓</span>
                                    </div>
                                    <span className="text-[11px] font-semibold text-amber-200/90">
                                        {new Date(currentOrder.timestamp || Date.now()).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WIB
                                    </span>
                                </div>

                                <div className="flex items-end justify-between pt-1">
                                    <div>
                                        <span className="text-[10px] uppercase font-bold text-amber-300 block tracking-wider">
                                            Nomor Pesanan
                                        </span>
                                        <h3 className="font-display font-black text-xl text-white tracking-wide">
                                            #{currentOrder.orderNumber}
                                        </h3>
                                    </div>
                                    <div className="text-right">
                                        <span className="text-[10px] font-bold text-amber-300 block uppercase">
                                            Meja
                                        </span>
                                        <span className="font-display font-black text-2xl text-white">
                                            {currentOrder.tableNumber || tableInfo?.number || '01'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Stepper Tracking Progress (FLOW 5) */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/90 shadow-xs space-y-4">
                                <div className="flex items-center justify-between pb-1 border-b border-stone-100">
                                    <h4 className="font-display font-extrabold text-xs text-stone-900 uppercase tracking-wider">
                                        Tahapan Pesanan
                                    </h4>
                                    <span className="text-[10px] font-extrabold bg-amber-50 text-amber-900 border border-amber-200/80 px-2.5 py-0.5 rounded-full capitalize">
                                        {statusSteps[activeStepIdx]?.label || 'Diproses'}
                                    </span>
                                </div>

                                <div className="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-stone-200 before:z-0 pt-1">
                                    {statusSteps.map((step, idx) => {
                                        const StepIcon = step.icon;
                                        const isCompleted = idx <= activeStepIdx;
                                        const isCurrent = idx === activeStepIdx;

                                        return (
                                            <div key={step.key} className="flex items-start gap-3 relative z-10">
                                                <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs transition-all shrink-0 ${
                                                    isCompleted
                                                        ? 'bg-amber-400 text-stone-950 font-black shadow-sm ring-4 ring-amber-100/90'
                                                        : 'bg-stone-100 text-stone-400'
                                                }`}>
                                                    {idx < activeStepIdx ? (
                                                        <Check className="w-3.5 h-3.5 stroke-[3]" />
                                                    ) : (
                                                        <StepIcon className="w-3.5 h-3.5" />
                                                    )}
                                                </div>

                                                <div className="flex-1">
                                                    <div className="flex items-center justify-between">
                                                        <span className={`font-display text-xs ${
                                                            isCurrent
                                                                ? 'font-black text-[#82181A]'
                                                                : isCompleted
                                                                    ? 'font-bold text-stone-800'
                                                                    : 'font-medium text-stone-400'
                                                        }`}>
                                                            {step.label}
                                                        </span>
                                                        {isCurrent && (
                                                            <span className="text-[9px] font-black bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full animate-pulse">
                                                                Sedang Berlangsung
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-[10px] text-stone-500 mt-0.5 leading-tight">
                                                        {step.desc}
                                                    </p>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Order Details Struk */}
                            <div className="bg-white p-4 rounded-2xl border border-stone-200/90 shadow-xs space-y-3">
                                <div className="flex items-center gap-2 border-b border-stone-100 pb-2.5">
                                    <ReceiptText className="w-4 h-4 text-[#82181A]" />
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
                                                {item.notes && (
                                                    <div className="text-[10px] text-amber-900 italic mt-0.5">
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

                                <div className="pt-2.5 border-t border-dashed border-stone-300 space-y-1 text-xs text-stone-600">
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
                                    <div className="flex justify-between font-display font-black text-sm text-[#82181A] pt-1">
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
                        className="py-3 px-3.5 rounded-2xl bg-amber-400 hover:bg-amber-300 text-stone-950 font-display font-bold text-xs shadow-xs active:scale-95 transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer"
                    >
                        <Bell className="w-3.5 h-3.5 fill-stone-950" />
                        <span>Panggil Pelayan</span>
                    </button>

                    <button
                        onClick={() => setIsOrderStatusOpen(false)}
                        className="flex-1 py-3 px-4 rounded-2xl bg-[#82181A] hover:bg-[#661012] text-white font-display font-extrabold text-xs shadow-md active:scale-95 transition flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <Plus className="w-4 h-4" />
                        <span>Pesan Menu Tambahan</span>
                    </button>
                </div>

            </div>
        </div>
    );
}


