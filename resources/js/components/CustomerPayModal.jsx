import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import axios from 'axios';
import { X, CheckCircle2, Loader2, User, QrCode, Building2 } from 'lucide-react';

const API_URL = import.meta.env.VITE_API_URL || '/api';

export default function CustomerPayModal({ isOpen, onClose, order }) {
    const { tableInfo, setActiveOrder, setOrders, showToast } = useCart();
    const [isSubmitting, setIsSubmitting] = useState(false);
    const customerName = (order?.customerName || order?.nama_pelanggan || localStorage.getItem('pos_customer_name') || 'Pelanggan').trim();

    if (!isOpen || !order) return null;

    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(num).replace('IDR', 'Rp');
    };

    const handleMidtransPayment = async () => {
        const trimmedName = customerName || 'Pelanggan';
        setIsSubmitting(true);
        const cleanOrderNumber = (order.orderNumber || '').replace(/^#/, '');

        try {
            // 1. Minta Snap Token dari Backend
            const res = await axios.post(`${API_URL}/pesanan/${cleanOrderNumber}/snap-token`, {
                nama_pelanggan: trimmedName
            });

            if (!res.data || !res.data.success || !res.data.data?.snap_token) {
                throw new Error(res.data?.message || 'Gagal mendapatkan tiket pembayaran Midtrans.');
            }

            const snapToken = res.data.data.snap_token;
            const redirectUrl = res.data.data.redirect_url;
            const clientKey = res.data.data.client_key || 'SB-Mid-client-Qx6UwA5DFHHUPt2B';

            const triggerPay = () => {
                if (window.snap && typeof window.snap.pay === 'function') {
                    window.snap.pay(snapToken, {
                        onSuccess: async function(result) {
                            console.log('Midtrans Payment Success:', result);
                            setIsSubmitting(true);

                            // Konfirmasi sinkron ke server kita
                            try {
                                await axios.post(`${API_URL}/pesanan/${cleanOrderNumber}/midtrans-confirm`, {
                                    payment_type: result?.payment_type || 'qris'
                                });
                            } catch (e) {
                                console.warn('Confirm fallback notice:', e);
                            }

                            const updatedOrder = {
                                ...order,
                                customerName: trimmedName,
                                nama_pelanggan: trimmedName,
                                metode_pembayaran: result?.payment_type || 'midtrans',
                                status: 'diproses',
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
                                showToast(`🎉 Pembayaran Berhasil! Pesanan a.n. ${trimmedName} segera dimasak.`, 'success');
                            }

                            setIsSubmitting(false);
                            if (typeof onClose === 'function') {
                                onClose();
                            }
                        },
                        onPending: function(result) {
                            console.log('Midtrans Payment Pending:', result);
                            showToast('Menunggu Anda menyelesaikan pembayaran...', 'info');
                            setIsSubmitting(false);
                        },
                        onError: function(result) {
                            console.error('Midtrans Payment Error:', result);
                            showToast('Pembayaran Midtrans gagal atau kadaluarsa.', 'warning');
                            setIsSubmitting(false);
                        },
                        onClose: function() {
                            console.log('Customer menutup pop-up Midtrans.');
                            setIsSubmitting(false);
                        }
                    });
                } else if (redirectUrl) {
                    // Fallback jika iframe diblokir oleh browser HP, buka halaman Midtrans resmi langsung
                    window.location.href = redirectUrl;
                } else {
                    showToast('Gagal memuat modul pembayaran. Coba sesaat lagi.', 'warning');
                    setIsSubmitting(false);
                }
            };

            // Jika script snap belum aktif, muat secara dinamis
            if (!window.snap) {
                const existingScript = document.getElementById('midtrans-snap-js');
                if (existingScript) existingScript.remove();

                const script = document.createElement('script');
                script.id = 'midtrans-snap-js';
                script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
                script.setAttribute('data-client-key', clientKey);
                script.onload = () => {
                    setTimeout(triggerPay, 150);
                };
                script.onerror = () => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        showToast('Gagal memuat Midtrans Snap SDK.', 'warning');
                        setIsSubmitting(false);
                    }
                };
                document.head.appendChild(script);
            } else {
                triggerPay();
            }

        } catch (err) {
            console.error('Midtrans Pay error:', err);
            showToast(err?.response?.data?.message || err.message || 'Gagal memproses pembayaran online.', 'warning');
            setIsSubmitting(false);
        }
    };

    return (
        <div className="fixed inset-0 z-60 bg-stone-950/50 backdrop-blur-[2px] flex justify-center items-end sm:items-center p-0 sm:p-4 animate-fade-in">
            <div className="w-full max-w-md bg-stone-50 rounded-t-2xl sm:rounded-2xl shadow-xl flex flex-col max-h-[92vh] overflow-hidden animate-slide-up relative border border-stone-200">

                {/* Header */}
                <div className="sticky top-0 z-10 bg-white px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                    <div>
                        <h3 className="font-display font-bold text-stone-900 text-sm">
                            Pembayaran Midtrans Snap
                        </h3>
                        <p className="text-[11px] text-stone-500 font-medium mt-0.5">
                            Dine-in • Meja {order.tableNumber || tableInfo.number} • #{order.orderNumber}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={isSubmitting}
                        className="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-600 flex items-center justify-center transition active:scale-95 cursor-pointer disabled:opacity-50"
                        aria-label="Tutup"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Body Content */}
                <div className="flex-1 overflow-y-auto no-scrollbar p-4 space-y-3">

                    {/* Total Tagihan Card */}
                    <div className="bg-[#881B1E] text-white p-4 rounded-xl shadow-xs text-center">
                        <span className="text-[11px] uppercase font-bold text-amber-200/90 tracking-wider block mb-0.5">
                            Total yang Harus Dibayar
                        </span>
                        <div className="font-display font-extrabold text-2xl tracking-tight text-white">
                            {formatRupiah(order.grandTotal)}
                        </div>
                        <span className="text-[11px] text-stone-200/80 block mt-1">
                            Termasuk rincian menu dan pajak resto
                        </span>
                    </div>

                    {/* Info Nama Pemesan (Tercatat dari Keranjang Pesanan) */}
                    <div className="bg-white p-3.5 rounded-xl border border-stone-200 shadow-2xs space-y-2">
                        <div className="flex items-center justify-between text-xs font-bold text-stone-800">
                            <span className="flex items-center gap-1.5">
                                <User className="w-3.5 h-3.5 text-[#881B1E]" />
                                <span>Nama Pemesan</span>
                            </span>
                            <span className="text-[10px] text-[#065F46] bg-[#ECFDF5] font-semibold px-2 py-0.5 rounded-md border border-[#A7F3D0] flex items-center gap-1">
                                <CheckCircle2 className="w-3 h-3 text-[#059669]" />
                                Dicatat di Struk
                            </span>
                        </div>

                        <div className="w-full px-3.5 py-2 rounded-lg border border-stone-200 text-xs font-bold text-stone-900 bg-stone-50 flex items-center justify-between select-none">
                            <span className="truncate text-stone-900 font-bold">{customerName || 'Pelanggan'}</span>
                            <span className="text-[10px] text-stone-400 font-medium shrink-0">Sesuai Keranjang</span>
                        </div>

                        <p className="text-[10px] text-stone-400 leading-tight">
                            Nama ini otomatis tercantum pada kolom <strong>Pembeli</strong> di struk nota pembayaran kasir.
                        </p>
                    </div>

                    {/* Midtrans Channel Highlight Card */}
                    <div className="bg-[#FFFBEB] p-3.5 rounded-xl border-2 border-[#FBBF24] shadow-2xs space-y-3">
                        <div className="flex items-center justify-between border-b border-amber-200/80 pb-2">
                            <span className="text-xs font-bold uppercase text-stone-900 tracking-wide">
                                Metode Resmi Midtrans
                            </span>
                            <span className="bg-[#ECFDF5] text-[#047857] border border-[#6EE7B7] text-[10px] font-bold px-2 py-0.5 rounded-md">
                                Verifikasi Otomatis
                            </span>
                        </div>

                        <p className="text-xs text-stone-600 leading-relaxed">
                            Selesaikan pembayaran langsung lewat HP. Pop-up resmi <strong>Midtrans Snap</strong> menyediakan pilihan metode lengkap:
                        </p>

                        <div className="grid grid-cols-2 gap-2 text-xs">
                            <div className="p-2.5 rounded-lg bg-white border border-stone-200 flex items-center gap-2">
                                <div className="w-7 h-7 rounded-md bg-[#FEF2F2] text-[#B91C1C] flex items-center justify-center shrink-0">
                                    <QrCode className="w-3.5 h-3.5" />
                                </div>
                                <div className="min-w-0">
                                    <span className="font-bold text-stone-900 block leading-tight text-[11px]">QRIS Instan</span>
                                    <span className="text-[10px] text-stone-500 block truncate">GoPay, OVO, Dana, ShopeePay</span>
                                </div>
                            </div>

                            <div className="p-2.5 rounded-lg bg-white border border-stone-200 flex items-center gap-2">
                                <div className="w-7 h-7 rounded-md bg-[#EFF6FF] text-[#1D4ED8] flex items-center justify-center shrink-0">
                                    <Building2 className="w-3.5 h-3.5" />
                                </div>
                                <div className="min-w-0">
                                    <span className="font-bold text-stone-900 block leading-tight text-[11px]">Virtual Account</span>
                                    <span className="text-[10px] text-stone-500 block truncate">BCA, Mandiri, BRI, BNI</span>
                                </div>
                            </div>
                        </div>

                        <div className="space-y-1.5 pt-1 text-[11px] text-stone-500 border-t border-amber-200/80">
                            <div className="flex items-center gap-1.5">
                                <CheckCircle2 className="w-3.5 h-3.5 text-[#059669] shrink-0" />
                                <span>Status pesanan otomatis lunas tanpa perlu konfirmasi manual</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <CheckCircle2 className="w-3.5 h-3.5 text-[#059669] shrink-0" />
                                <span>Pesanan langsung diteruskan ke koki dapur untuk dimasak</span>
                            </div>
                        </div>
                    </div>

                </div>

                {/* Footer Action */}
                <div className="p-4 bg-white border-t border-stone-200 space-y-2">
                    <button
                        type="button"
                        onClick={handleMidtransPayment}
                        disabled={isSubmitting}
                        className="w-full py-3.5 px-4 rounded-xl bg-emerald-600 text-white font-display font-bold text-xs sm:text-sm tracking-wide active:scale-[0.99] transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 className="w-4 h-4 animate-spin text-white" />
                                <span>Menghubungi Midtrans...</span>
                            </>
                        ) : (
                            <span>BAYAR VIA MIDTRANS SNAP</span>
                        )}
                    </button>

                    <button
                        type="button"
                        onClick={onClose}
                        disabled={isSubmitting}
                        className="w-full py-2 text-center text-xs text-stone-500 hover:text-stone-800 font-medium transition cursor-pointer disabled:opacity-50"
                    >
                        Tutup & Cek Status Pesanan
                    </button>
                </div>

            </div>
        </div>
    );
}
