import React from 'react';
import { useCart } from '../context/CartContext';
import { getCategoryIcon } from '../data/mockData';

export default function CategoryTabs({ selectedCategory, onSelectCategory }) {
    const { backendCategories, menuList } = useCart();

    // Build dynamic tabs starting with 'all' and 'popular', then actual backend categories
    const tabs = [
        { id: 'all', nama: 'Semua Menu' },
        { id: 'popular', nama: 'Populer' },
        ...backendCategories.map(cat => ({
            id: String(cat.id),
            backend_id: cat.id,
            nama: cat.nama,
        }))
    ];

    const getItemCount = (tabId) => {
        if (tabId === 'all') return menuList.length;
        if (tabId === 'popular') return Math.min(menuList.length, 4);
        return menuList.filter(item => String(item.kategori_id) === String(tabId)).length;
    };

    return (
        <div className="pt-2 pb-2 max-w-md mx-auto w-full">
            <div className="flex gap-2 px-4 overflow-x-auto no-scrollbar snap-x">
                {tabs.map((tab) => {
                    const isActive = String(selectedCategory) === String(tab.id);
                    const icon = getCategoryIcon(tab.nama, tab.id);
                    const count = getItemCount(tab.id);

                    return (
                        <button
                            key={tab.id}
                            onClick={() => onSelectCategory(tab.id)}
                            className={`snap-start shrink-0 flex items-center gap-1.5 px-3.5 py-2 rounded-2xl text-xs font-bold transition-all duration-200 cursor-pointer ${
                                isActive
                                    ? 'bg-gradient-to-r from-amber-400 to-amber-500 text-stone-950 shadow-md shadow-amber-500/20 scale-[1.02]'
                                    : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200/80 hover:border-stone-300'
                            }`}
                        >
                            <span className="text-sm">{icon}</span>
                            <span>{tab.nama}</span>
                            <span className={`text-[10px] px-1.5 py-0.2 rounded-full font-bold ${
                                isActive ? 'bg-stone-950/15 text-stone-950' : 'bg-stone-100 text-stone-500'
                            }`}>
                                {count}
                            </span>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}

