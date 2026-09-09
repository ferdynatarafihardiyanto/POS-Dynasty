import React from 'react';
import { useCart } from '../context/CartContext';

export default function CategoryTabs({ selectedCategory, onSelectCategory }) {
    const { categories } = useCart();
    const list = categories && categories.length > 0 ? categories : [
        { id: 'all', nama: 'Semua Menu', icon: '🍽️' }
    ];

    return (
        <div className="pt-2 pb-2 w-full">
            <div 
                className="flex gap-2.5 overflow-x-auto no-scrollbar px-4 sm:px-6 lg:px-8 pb-2 scroll-pl-4 sm:scroll-pl-6 lg:scroll-pl-8"
                style={{ WebkitOverflowScrolling: 'touch' }}
            >
                {list.map((cat) => {
                    const isActive = selectedCategory === cat.id;
                    return (
                        <button
                            key={cat.id}
                            onClick={() => onSelectCategory(cat.id)}
                            className={`shrink-0 flex items-center gap-1.5 px-3.5 py-2 rounded-2xl text-xs font-bold transition-all duration-200 cursor-pointer ${
                                isActive
                                    ? 'bg-amber-400 text-stone-950 shadow-sm shadow-amber-400/30 scale-[1.02]'
                                    : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200/80 hover:border-stone-300'
                            }`}
                        >
                            <span className="text-sm">{cat.icon}</span>
                            <span>{cat.nama}</span>
                        </button>
                    );
                })}
                {/* Trailing padding spacer to prevent clipping at the end of scroll */}
                <div className="shrink-0 w-1 sm:w-2" aria-hidden="true" />
            </div>
        </div>
    );
}
