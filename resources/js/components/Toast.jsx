import React from 'react';
import { useCart } from '../context/CartContext';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-react';

export default function Toast() {
    const { toast } = useCart();

    if (!toast) return null;

    const icons = {
        success: <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0" />,
        warning: <AlertCircle className="w-5 h-5 text-amber-400 shrink-0" />,
        error: <AlertCircle className="w-5 h-5 text-rose-400 shrink-0" />,
        info: <Info className="w-5 h-5 text-sky-400 shrink-0" />
    };

    const bgStyles = {
        success: 'border-emerald-500/30 bg-stone-900/95 text-stone-100 shadow-emerald-950/30',
        warning: 'border-amber-500/30 bg-stone-900/95 text-stone-100 shadow-amber-950/30',
        error: 'border-rose-500/30 bg-stone-900/95 text-stone-100 shadow-rose-950/30',
        info: 'border-sky-500/30 bg-stone-900/95 text-stone-100 shadow-sky-950/30'
    };

    const type = toast.type || 'info';

    return (
        <div className="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-sm pointer-events-none animate-slide-up">
            <div className={`flex items-center gap-3 px-4 py-3 rounded-2xl border shadow-2xl backdrop-blur-md pointer-events-auto ${bgStyles[type] || bgStyles.info}`}>
                {icons[type] || icons.info}
                <p className="text-xs font-semibold leading-snug flex-1 font-sans">
                    {toast.message}
                </p>
            </div>
        </div>
    );
}
