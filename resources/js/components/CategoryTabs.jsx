import React from 'react';
import { useCart } from '../context/CartContext';
import {
    UtensilsCrossed,
    Utensils,
    Coffee,
    CupSoda,
    GlassWater,
    Soup,
    Cookie,
    Sandwich,
    Flame
} from 'lucide-react';

const getCategoryIcon = (cat) => {
    const name = (cat?.nama || '').toLowerCase();
    const id = String(cat?.id || '').toLowerCase();
    const icon = (cat?.icon || '').toLowerCase();

    if (id === 'all' || name.includes('semua') || icon === 'utensils') return UtensilsCrossed;
    if (id === 'popular' || name.includes('populer') || icon === 'flame') return Flame;
    if (name.includes('non') && (name.includes('kopi') || name.includes('coffee'))) return CupSoda;
    if (name.includes('coffee') || name.includes('kopi') || icon === 'coffee') return Coffee;
    if (name.includes('milk') || name.includes('susu')) return GlassWater;
    if (name.includes('tea') || name.includes('teh') || name.includes('minum') || name.includes('drink') || icon === 'cup-soda') return CupSoda;
    if (name.includes('mie') || name.includes('bakso') || name.includes('sop') || name.includes('soup') || name.includes('kuah') || icon === 'soup') return Soup;
    if (name.includes('snack') || name.includes('dessert') || name.includes('cake') || icon === 'cookie') return Cookie;
    if (name.includes('roti') || name.includes('sandwich') || name.includes('toast')) return Sandwich;
    if (name.includes('makan') || name.includes('food') || name.includes('nasi') || name.includes('ayam')) return Utensils;
    return Utensils;
};

export default function CategoryTabs({ selectedCategory, onSelectCategory }) {
    const { categories } = useCart();
    const list = categories && categories.length > 0 ? categories : [
        { id: 'all', nama: 'Semua Menu', icon: 'utensils' }
    ];

    return (
        <div className="pt-2 pb-1.5 w-full">
            <div 
                className="flex gap-2 overflow-x-auto no-scrollbar px-4 sm:px-6 lg:px-8 pb-1 scroll-pl-4 sm:scroll-pl-6 lg:scroll-pl-8"
                style={{ WebkitOverflowScrolling: 'touch' }}
            >
                {list.map((cat) => {
                    const isActive = selectedCategory === cat.id;
                    const IconComponent = getCategoryIcon(cat);

                    return (
                        <button
                            key={cat.id}
                            type="button"
                            onClick={() => onSelectCategory(cat.id)}
                            className={`shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs transition-colors duration-150 cursor-pointer ${
                                isActive
                                    ? 'bg-[#82181A] text-white font-semibold border border-[#82181A] shadow-xs'
                                    : 'bg-stone-100 hover:bg-stone-200/70 text-stone-600 hover:text-stone-900 font-medium border border-stone-200/80'
                            }`}
                        >
                            <IconComponent className={`w-3.5 h-3.5 shrink-0 stroke-[1.75] ${isActive ? 'text-amber-300' : 'text-stone-500'}`} />
                            <span className="whitespace-nowrap">{cat.nama}</span>
                        </button>
                    );
                })}
                {/* Trailing padding spacer to prevent clipping at the end of scroll */}
                <div className="shrink-0 w-1 sm:w-2" aria-hidden="true" />
            </div>
        </div>
    );
}
