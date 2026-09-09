import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { AVAILABLE_TABLES } from '../data/mockData';
import { X, QrCode, Check, Camera, Sparkles } from 'lucide-react';

export default function TableScannerModal() {
    const { isTableModalOpen, setIsTableModalOpen, tableInfo, setTableInfo, showToast, availableTables = AVAILABLE_TABLES } = useCart();
    const [simulatedScanning, setSimulatedScanning] = useState(false);

    if (!isTableModalOpen) return null;

    const handleSelectTable = (table) => {
        setTableInfo(table);
        try {
            localStorage.setItem('dynasty_table', JSON.stringify(table));
        } catch (e) {}
        setIsTableModalOpen(false);
        showToast(`Berhasil tersambung ke Meja ${table.number} (${table.name})! 📍`, 'success');
    };

    const handleSimulateScan = () => {
        setSimulatedScanning(true);
        setTimeout(() => {
            setSimulatedScanning(false);
            const tablesList = availableTables.length > 0 ? availableTables : AVAILABLE_TABLES;
            const randomTable = tablesList[Math.floor(Math.random() * tablesList.length)];
            handleSelectTable(randomTable);
        }, 1200);
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white p-4 flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                        <div className="w-10 h-10 rounded-2xl bg-amber-400 text-stone-950 flex items-center justify-center shadow-md">
                            <QrCode className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="font-display font-extrabold text-sm text-white">
                                Barcode & Pilih Meja
                            </h3>
                            <p className="text-[11px] text-amber-200">
                                Saat ini: Meja {tableInfo.number}
                            </p>
                        </div>
                    </div>

                    <button
                        onClick={() => setIsTableModalOpen(false)}
                        className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Body Content */}
                <div className="p-4 space-y-4 max-h-[72vh] overflow-y-auto no-scrollbar">
                    {/* Simulated QR Code Scan Box */}
                    <div className="bg-stone-900 text-white p-4 rounded-2xl text-center relative overflow-hidden border border-stone-800">
                        <div className="w-24 h-24 mx-auto my-2 rounded-2xl border-2 border-dashed border-amber-400/80 flex items-center justify-center bg-stone-800/80 relative">
                            <QrCode className="w-12 h-12 text-amber-400" />
                            {simulatedScanning && (
                                <div className="absolute inset-x-0 h-1 bg-amber-400 shadow-amber-400 shadow-lg animate-bounce" />
                            )}
                        </div>
                        <p className="text-xs font-semibold text-stone-300">
                            Scan QR Code yang tertera di meja Anda
                        </p>
                        <button
                            onClick={handleSimulateScan}
                            disabled={simulatedScanning}
                            className="mt-3 px-4 py-2 bg-amber-400 hover:bg-amber-300 text-stone-950 font-bold text-xs rounded-xl flex items-center gap-1.5 mx-auto active:scale-95 transition"
                        >
                            <Camera className="w-3.5 h-3.5" />
                            <span>{simulatedScanning ? 'Memindai QR...' : 'Simulasi Scan Barcode'}</span>
                        </button>
                    </div>

                    {/* Or Manual Table Selection */}
                    <div>
                        <div className="flex items-center justify-between mb-2">
                            <h4 className="font-display font-bold text-xs text-stone-900 uppercase tracking-wider">
                                Atau Pilih Nomor Meja Manual:
                            </h4>
                        </div>

                        <div className="grid grid-cols-2 gap-2">
                            {(availableTables && availableTables.length > 0 ? availableTables : AVAILABLE_TABLES).map((table) => {
                                const isCurrent = tableInfo.number === table.number;
                                return (
                                    <button
                                        key={table.token}
                                        onClick={() => handleSelectTable(table)}
                                        className={`p-3 rounded-2xl border text-left flex items-center justify-between transition-all ${
                                            isCurrent
                                                ? 'border-amber-500 bg-amber-50/70 shadow-xs'
                                                : 'border-stone-200 bg-white hover:border-stone-300'
                                        }`}
                                    >
                                        <div>
                                            <div className="font-display font-extrabold text-sm text-stone-900">
                                                Meja {table.number}
                                            </div>
                                            <div className="text-[10px] text-stone-500">
                                                {table.name}
                                            </div>
                                            <div className="text-[9px] text-amber-700 font-semibold mt-0.5">
                                                {table.capacity}
                                            </div>
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
                        className="w-full py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition"
                    >
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    );
}
