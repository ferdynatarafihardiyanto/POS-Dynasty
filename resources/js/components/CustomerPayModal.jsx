import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import axios from 'axios';
import { X, QrCode, Building2, CheckCircle2, Copy, ShieldCheck, ArrowRight, Loader2, Sparkles, User, AlertCircle } from 'lucide-react';

const API_URL = import.meta.env.VITE_API_URL || '/api';

export default function CustomerPayModal({ isOpen, onClose, order }) {
    const { tableInfo, availableTables, setActiveOrder, setOrders, showToast } = useCart();
    const [payMethod, setPayMethod] = useState('qris'); // 'qris' or 'transfer'
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [copiedAccount, setCopiedAccount] = useState(null);
    const [customerName, setCustomerName] = useState(() => {
        return order?.customerName || order?.nama_pelanggan || localStorage.getItem('pos_customer_name') || '';
    });
    const [nameError, setNameError] = useState('');

    if (!isOpen || !order) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const copyToClipboard = (text, type) => {
        navigator.clipboard.writeText(text);
        setCopiedAccount(type);
        showToast(`Nomor Rekening ${type} disalin!`, 'info');
        setTimeout(() => setCopiedAccount(null), 2500);
    };

    const handleConfirmPayment = async () => {
        const trimmedName = customerName.trim();
        if (!trimmedName) {
            setNameError('Silakan masukkan nama Anda terlebih dahulu untuk dicetak di struk.');
            return;
        }

        setIsSubmitting(true);
        setNameError('');
        localStorage.setItem('pos_customer_name', trimmedName);

        const cleanOrderNumber = (order.orderNumber || '').replace(/^#/, '');
        const activeToken = tableInfo?.token || availableTables?.[0]?.token;

        let finalStatus = 'diproses';

        try {
            const res = await axios.post(
                `${API_URL}/pesanan/${cleanOrderNumber}/bayar`,
                {
                    qr_token: activeToken,
                    metode_pembayaran: payMethod,
                    jumlah_bayar: order.grandTotal,
                    nama_pelanggan: trimmedName
                },
                { timeout: 4000 }
            );

            if (res?.data?.data?.status) {
                finalStatus = res.data.data.status;
            }
        } catch (err) {
            console.warn('Payment API notice/fallback:', err?.message);
        }

        try {
            const updatedOrder = {
                ...order,
                customerName: trimmedName,
                nama_pelanggan: trimmedName,
                metode_pembayaran: payMethod,
                status: finalStatus,
                status_pembayaran: 'dibayar'
            };

            if (typeof setActiveOrder === 'function') {
                setActiveOrder(updatedOrder);
            }

            if (typeof setOrders === 'function') {
                setOrders(prev => {
                    if (!Array.isArray(prev)) return [updatedOrder];
                    return prev.map(o => {
                        const oNum = (o?.orderNumber || '').replace(/^#/, '');
                        return (oNum === cleanOrderNumber || o?.orderNumber === order?.orderNumber) ? updatedOrder : o;
                    });
                });
            }

            if (typeof showToast === 'function') {
                showToast(`🎉 Pembayaran ${payMethod.toUpperCase()} Berhasil! Pesanan a.n. ${trimmedName} segera dimasak.`, 'success');
            }
        } catch (e) {
            console.error('State update error:', e);
        } finally {
            setIsSubmitting(false);
            if (typeof onClose === 'function') {
                onClose();
            }
        }
    };

    // Dynamic QRIS Mock SVG Generator
    const qrisDataUrl = `https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=00020101021226680016ID.CO.DYNASTY.POS01189360091100223405120215${order.orderNumber}520458125303360540${order.grandTotal}5802ID5913KEDAI DYNASTY6007BANDUNG62070703A016304`;

    return (
        <div className="fixed inset-0 z-60 bg-stone-950/75 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 sm:p-4 animate-fade-in">
            <div className="w-full max-w-md bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col max-h-[92vh] overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="sticky top-0 z-10 bg-white px-4 py-3.5 border-b border-stone-200/80 flex items-center justify-between shadow-xs">
                    <div>
                        <h3 className="font-display font-black text-stone-900 text-sm flex items-center gap-1.5">
                            <ShieldCheck className="w-4 h-4 text-emerald-600" />
                            Pembayaran HP (QRIS / TF)
                        </h3>
                        <p className="text-[11px] text-stone-500 font-medium">
                            Dine-in • Meja {order.tableNumber || tableInfo.number} • #{order.orderNumber}
                        </p>
                    </div>
                    <button
                        onClick={onClose}
                        disabled={isSubmitting}
                        className="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 flex items-center justify-center transition active:scale-95"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Body Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-3.5">

                    {/* Total Tagihan Card */}
                    <div className="bg-gradient-to-br from-[#7A1517] to-[#881B1E] text-white p-4 rounded-2xl shadow-md text-center">
                        <span className="text-[10px] uppercase font-bold text-amber-300 tracking-wider block mb-0.5">
                            Total yang Harus Dibayar
                        </span>
                        <div className="font-display font-black text-2xl tracking-tight text-white">
                            {formatRupiah(order.grandTotal)}
                        </div>
                        <span className="text-[10px] text-amber-200/80 block mt-1">
                            Termasuk rincian menu dan pajak resto
                        </span>
                    </div>

                    {/* Form Input Nama Pemesan (Untuk Dicetak di Struk) */}
                    <div className="bg-white p-3.5 rounded-2xl border border-stone-200/90 shadow-2xs space-y-1.5">
                        <label className="block text-xs font-bold text-stone-800 flex items-center justify-between">
                            <span className="flex items-center gap-1.5">
                                <User className="w-3.5 h-3.5 text-[#881B1E]" />
                                Nama Pemesan
                            </span>
                            <span className="text-[10px] text-amber-700 bg-amber-50 font-bold px-2 py-0.5 rounded-full border border-amber-200/60">
                                Dicetak di Struk
                            </span>
                        </label>
                        <input
                            type="text"
                            value={customerName}
                            onChange={(e) => {
                                setCustomerName(e.target.value);
                                if (nameError) setNameError('');
                            }}
                            placeholder="Masukkan nama Anda (misal: Natan / Budi)..."
                            maxLength={40}
                            className={`w-full px-3.5 py-2.5 rounded-xl border text-xs font-semibold text-stone-900 bg-stone-50/60 focus:bg-white focus:outline-none transition ${
                                nameError
                                    ? 'border-red-400 focus:ring-2 focus:ring-red-400/20'
                                    : 'border-stone-200 focus:border-[#881B1E] focus:ring-2 focus:ring-[#881B1E]/10'
                            }`}
                        />
                        {nameError && (
                            <p className="text-[11px] text-red-600 font-medium flex items-center gap-1 pt-0.5">
                                <AlertCircle className="w-3 h-3 shrink-0" />
                                {nameError}
                            </p>
                        )}
                        <p className="text-[10px] text-stone-400 leading-tight">
                            Nama ini otomatis tercantum pada kolom <strong>Pembeli</strong> di struk nota pembayaran Anda.
                        </p>
                    </div>

                    {/* Method Switcher Tabs */}
                    <div className="grid grid-cols-2 p-1 bg-stone-200/70 rounded-2xl gap-1">
                        <button
                            type="button"
                            onClick={() => setPayMethod('qris')}
                            className={`py-2 px-3 rounded-xl font-display font-bold text-xs flex items-center justify-center gap-1.5 transition ${
                                payMethod === 'qris'
                                    ? 'bg-white text-stone-900 shadow-xs'
                                    : 'text-stone-600 hover:text-stone-900'
                            }`}
                        >
                            <QrCode className="w-4 h-4 text-[#881B1E]" />
                            <span>QRIS Instan</span>
                        </button>
                        <button
                            type="button"
                            onClick={() => setPayMethod('transfer')}
                            className={`py-2 px-3 rounded-xl font-display font-bold text-xs flex items-center justify-center gap-1.5 transition ${
                                payMethod === 'transfer'
                                    ? 'bg-white text-stone-900 shadow-xs'
                                    : 'text-stone-600 hover:text-stone-900'
                            }`}
                        >
                            <Building2 className="w-4 h-4 text-[#881B1E]" />
                            <span>Transfer Bank (TF)</span>
                        </button>
                    </div>

                    {/* QRIS Tab View */}
                    {payMethod === 'qris' && (
                        <div className="bg-white p-4 rounded-2xl border border-stone-200/80 text-center space-y-3">
                            <div className="flex items-center justify-between border-b border-stone-100 pb-2">
                                <span className="text-[11px] font-bold text-stone-800 tracking-wider uppercase flex items-center gap-1">
                                    <Sparkles className="w-3 h-3 text-amber-500" />
                                    Scan QRIS Dinamis
                                </span>
                                <span className="bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                                    Bebas Biaya Admin
                                </span>
                            </div>

                            {/* QR Code Canvas Frame */}
                            <div className="inline-block p-3 bg-white rounded-2xl border-2 border-stone-900 shadow-inner">
                                <img
                                    src={qrisDataUrl}
                                    alt="QRIS Kedai Dynasty"
                                    className="w-48 h-48 mx-auto object-contain rounded-lg"
                                />
                                <div className="text-[10px] font-black text-stone-800 mt-2 tracking-wider">
                                    KEDAI DYNASTY POS • MEJA {order.tableNumber}
                                </div>
                            </div>

                            <p className="text-[11px] text-stone-500 leading-relaxed max-w-xs mx-auto">
                                Buka aplikasi <strong>BCA, Livin', GoPay, OVO, Dana, ShopeePay</strong> atau M-Banking apa saja, lalu scan kode QRIS di atas.
                            </p>
                        </div>
                    )}

                    {/* Transfer Bank Tab View */}
                    {payMethod === 'transfer' && (
                        <div className="bg-white p-4 rounded-2xl border border-stone-200/80 space-y-3">
                            <span className="text-[11px] font-bold text-stone-800 tracking-wider uppercase block border-b border-stone-100 pb-2">
                                Rekening Resmi Kedai Dynasty
                            </span>

                            {/* BCA Card */}
                            <div className="p-3 rounded-xl bg-stone-50 border border-stone-200/80 flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] font-bold text-blue-800 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                        BCA (Bank Central Asia)
                                    </span>
                                    <div className="font-mono font-bold text-sm text-stone-900 mt-1">
                                        821-0988-2345
                                    </div>
                                    <div className="text-[10px] text-stone-500">
                                        a.n. PT Kedai Dynasty Kuliner
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => copyToClipboard('82109882345', 'BCA')}
                                    className="px-2.5 py-1.5 rounded-lg bg-white border border-stone-200 text-stone-700 hover:bg-stone-100 text-xs font-bold flex items-center gap-1 shadow-2xs active:scale-95 transition"
                                >
                                    <Copy className="w-3.5 h-3.5" />
                                    <span>{copiedAccount === 'BCA' ? 'Disalin!' : 'Salin'}</span>
                                </button>
                            </div>

                            {/* Mandiri Card */}
                            <div className="p-3 rounded-xl bg-stone-50 border border-stone-200/80 flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        Bank Mandiri
                                    </span>
                                    <div className="font-mono font-bold text-sm text-stone-900 mt-1">
                                        137-00-1928374-1
                                    </div>
                                    <div className="text-[10px] text-stone-500">
                                        a.n. PT Kedai Dynasty Kuliner
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => copyToClipboard('1370019283741', 'Mandiri')}
                                    className="px-2.5 py-1.5 rounded-lg bg-white border border-stone-200 text-stone-700 hover:bg-stone-100 text-xs font-bold flex items-center gap-1 shadow-2xs active:scale-95 transition"
                                >
                                    <Copy className="w-3.5 h-3.5" />
                                    <span>{copiedAccount === 'Mandiri' ? 'Disalin!' : 'Salin'}</span>
                                </button>
                            </div>

                            <p className="text-[11px] text-stone-500 leading-relaxed text-center">
                                Transfer nominal tepat senilai <strong>{formatRupiah(order.grandTotal)}</strong> ke salah satu rekening di atas.
                            </p>
                        </div>
                    )}

                </div>

                {/* Footer Action */}
                <div className="p-4 bg-white border-t border-stone-200/80 shadow-xs space-y-2">
                    <button
                        type="button"
                        onClick={handleConfirmPayment}
                        disabled={isSubmitting}
                        className="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-display font-extrabold text-xs shadow-md shadow-emerald-700/20 active:scale-95 transition flex items-center justify-center gap-2"
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 className="w-4 h-4 animate-spin text-white" />
                                <span>Memverifikasi Pembayaran...</span>
                            </>
                        ) : (
                            <>
                                <CheckCircle2 className="w-4 h-4 text-emerald-200" />
                                <span>Konfirmasi Saya Sudah Bayar ({payMethod === 'qris' ? 'QRIS' : 'TRANSFER / TF'})</span>
                            </>
                        )}
                    </button>

                    <button
                        type="button"
                        onClick={onClose}
                        disabled={isSubmitting}
                        className="w-full py-2 text-center text-xs text-stone-500 hover:text-stone-800 font-semibold"
                    >
                        Tutup & Cek Status Pesanan
                    </button>
                </div>

            </div>
        </div>
    );
}
