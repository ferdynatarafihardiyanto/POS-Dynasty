import React, { useState } from 'react';
import { useCart } from '../context/CartContext';
import { WAITER_QUICK_ACTIONS } from '../data/mockData';
import { X, Bell, Send, Sparkles } from 'lucide-react';

export default function WaiterCallModal() {
    const { isWaiterModalOpen, setIsWaiterModalOpen, tableInfo, callWaiter } = useCart();
    const [selectedAction, setSelectedAction] = useState(WAITER_QUICK_ACTIONS[0]);
    const [customNote, setCustomNote] = useState('');

    if (!isWaiterModalOpen) return null;

    const handleSend = () => {
        callWaiter(selectedAction, customNote);
    };

    return (
        <div className="fixed inset-0 z-50 bg-stone-950/60 backdrop-blur-xs flex justify-center items-end sm:items-center p-0 animate-fade-in">
            <div className="w-full max-w-md bg-stone-50 rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden animate-slide-up relative">

                {/* Header */}
                <div className="bg-gradient-to-r from-[#7A1517] via-[#881B1E] to-[#6E1214] text-white p-4 flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                        <div className="w-10 h-10 rounded-2xl bg-amber-400 text-stone-950 flex items-center justify-center shadow-md">
                            <Bell className="w-5 h-5 fill-stone-950" />
                        </div>
                        <div>
                            <h3 className="font-display font-extrabold text-sm text-white">
                                Panggil Bantuan Pelayan
                            </h3>
                            <p className="text-[11px] text-amber-200">
                                Meja {tableInfo.number} • {tableInfo.name}
                            </p>
                        </div>
                    </div>

                    <button
                        onClick={() => setIsWaiterModalOpen(false)}
                        className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition"
                    >
                        <X className="w-4 h-4" />
                    </button>
                </div>

                {/* Body Options */}
                <div className="p-4 space-y-3 max-h-[70vh] overflow-y-auto no-scrollbar">
                    <p className="text-xs font-bold text-stone-700">
                        Pilih Kebutuhan Anda:
                    </p>

                    <div className="space-y-2">
                        {WAITER_QUICK_ACTIONS.map((action) => {
                            const isSelected = selectedAction.id === action.id;
                            return (
                                <div
                                    key={action.id}
                                    onClick={() => setSelectedAction(action)}
                                    className={`p-3 rounded-2xl border cursor-pointer transition-all flex items-center gap-3 ${
                                        isSelected
                                            ? 'border-amber-500 bg-amber-50/60 shadow-xs'
                                            : 'border-stone-200 hover:border-stone-300 bg-white'
                                    }`}
                                >
                                    <span className="text-xl">{action.icon}</span>
                                    <div className="flex-1">
                                        <div className="font-display font-bold text-xs text-stone-900">
                                            {action.label}
                                        </div>
                                        <div className="text-[10px] text-stone-500">
                                            {action.desc}
                                        </div>
                                    </div>
                                    <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                                        isSelected ? 'border-amber-600 bg-amber-500' : 'border-stone-300'
                                    }`}>
                                        {isSelected && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Custom Note */}
                    <div className="pt-2">
                        <label className="block text-[11px] font-bold text-stone-700 mb-1">
                            Pesan Tambahan (Opsional):
                        </label>
                        <input
                            type="text"
                            value={customNote}
                            onChange={(e) => setCustomNote(e.target.value)}
                            placeholder="Contoh: Minta sendok 2 dan tisu basah ya..."
                            className="w-full p-2.5 rounded-xl border border-stone-200 bg-white text-xs text-stone-800 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500"
                        />
                    </div>
                </div>

                {/* Footer Actions */}
                <div className="p-4 bg-white border-t border-stone-200/80 flex gap-2">
                    <button
                        onClick={() => setIsWaiterModalOpen(false)}
                        className="flex-1 py-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition"
                    >
                        Batal
                    </button>
                    <button
                        onClick={handleSend}
                        className="flex-2 py-3 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-stone-950 font-display font-extrabold text-xs shadow-md shadow-amber-500/20 active:scale-95 transition flex items-center justify-center gap-1.5"
                    >
                        <Send className="w-3.5 h-3.5" />
                        <span>Panggil Sekarang</span>
                    </button>
                </div>

            </div>
        </div>
    );
}
