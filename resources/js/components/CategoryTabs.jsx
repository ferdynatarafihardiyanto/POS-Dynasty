import React from 'react';
import { CATEGORIES } from '../data/mockData';

export default function CategoryTabs({ selectedCategory, onSelectCategory }) {
    return (
        <div className="pt-2 pb-2 max-w-7xl w-full mx-auto">
            <div className="flex gap-2.5 overflow-x-auto no-scrollbar px-4 pb-2 snap-x">
                {CATEGORIES.map((cat) => {
                    const isActive = selectedCategory === cat.id;
                    return (
                        <button
                            key={cat.id}
                            onClick={() => onSelectCategory(cat.id)}
                            className={`snap-start shrink-0 flex items-center gap-1.5 px-3.5 py-2 rounded-2xl text-xs font-bold transition-all duration-200 ${
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
            </div>
        </div>
    );
}
