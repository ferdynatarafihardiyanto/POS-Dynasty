import React from 'react';
import { useCart } from '../context/CartContext';
import { AVAILABLE_TABLES } from '../data/mockData';
import { X, QrCode, Check, Smartphone, Info } from 'lucide-react';

export default function TableScannerModal() {
    const { isTableModalOpen, setIsTableModalOpen, tableInfo, setTableInfo, showToast, availableTables } = useCart();

    if (!isTableModalOpen) return null;

    const tablesList = Array.isArray(availableTables) && availableTables.length > 0 ? availableTables : AVAILABLE_TABLES;

    const handleSelectTable = (table) => {
        setTableInfo(table);
        setIsTableModalOpen(false);
        showToast(`Tersambung ke ${table.name || 'Meja ' + table.number}! 📍`, 'success');
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/70 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md bg-[#FFFDF9] rounded-t-[32px] sm:rounded-3xl shadow-2xl overflow-hidden animate-slide-up relative border-t sm:border border-amber-500/20">

                {/* Header */}
                <div className="bg-gradient-to-r from-[#661012] via-[#82181A] to-[#661012] text-white p-4 flex items-center justify-between shadow-md">
                    <div className="flex items-center gap-2.5">
                        <div className="w-10 h-10 rounded-2xl bg-amber-400 text-stone-950 flex items-center justify-center shadow-md">
                            <QrCode className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="font-display font-extrabold text-sm text-white">
                                Informasi Meja Dine-In
                            </h3>
                            <p className="text-[11px] text-amber-200">
                                Kedai Dynasty • {tableInfo?.name || `Meja ${tableInfo?.number || '01'}`}
                            </p>
                        </div>
                    </div>

                    <button
                        onClick={() => setIsTableModalOpen(false)}
                        className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer"
                        aria-label="Tutup modal meja"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Body Content */}
                <div className="p-4 space-y-4 max-h-[72vh] overflow-y-auto no-scrollbar">
                    {/* Google Lens Physical QR Information Box */}
                    <div className="bg-amber-50/80 border border-amber-200/80 p-3.5 rounded-2xl text-stone-800 space-y-2">
                        <div className="flex items-center gap-2">
                            <Smartphone className="w-4 h-4 text-[#82181A]" />
                            <h4 className="font-display font-extrabold text-xs text-[#82181A]">
                                Cara Akses Menu Meja
                            </h4>
                        </div>
                        <p className="text-[11px] text-stone-600 leading-relaxed">
                            Arahkan kamera smartphone atau <strong>Google Lens</strong> ke barcode fisik yang tertempel di meja Anda untuk langsung memesan hidangan.
                        </p>
                    </div>

                    {/* Manual Table Selection */}
                    <div>
                        <div className="flex items-center justify-between mb-2 px-1">
                            <h4 className="font-display font-bold text-xs text-stone-900 uppercase tracking-wider">
                                Daftar Meja Tersedia:
                            </h4>
                            <span className="text-[10px] text-stone-400 font-semibold">
                                {tablesList.length} Meja
                            </span>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            {tablesList.map((table) => {
                                const isCurrent = String(tableInfo?.number) === String(table.number);
                                return (
                                    <button
                                        key={table.token || table.number}
                                        onClick={() => handleSelectTable(table)}
                                        className={`p-3 rounded-2xl border text-left flex items-center justify-between transition-all cursor-pointer ${
                                            isCurrent
                                                ? 'border-amber-500 bg-amber-50/70 shadow-xs ring-2 ring-amber-400/30'
                                                : 'border-stone-200 bg-white hover:border-stone-300'
                                        }`}
                                    >
                                        <div>
                                            <div className="font-display font-extrabold text-sm text-stone-900">
                                                Meja {table.number}
                                            </div>
                                            <div className="text-[10px] text-stone-500 line-clamp-1">
                                                {table.name}
                                            </div>
                                            {table.capacity && (
                                                <div className="text-[9px] text-amber-800 font-bold mt-0.5">
                                                    {table.capacity}
                                                </div>
                                            )}
                                        </div>

                                        {isCurrent && (
                                            <div className="w-5 h-5 rounded-full bg-amber-500 text-stone-950 flex items-center justify-center shrink-0">
                                                <Check className="w-3 h-3 stroke-[3]" />
                                            </div>
                                        )}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="p-4 bg-white border-t border-stone-200/80">
                    <button
                        onClick={() => setIsTableModalOpen(false)}
                        className="w-full py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    );
}


