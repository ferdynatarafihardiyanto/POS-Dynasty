import React from 'react';
import { Search, X } from 'lucide-react';

export default function SearchBar({ searchQuery, setSearchQuery }) {
    return (
        <div className="w-full px-4 sm:px-6 lg:px-8 pt-3 pb-1">
            <div className="relative flex items-center">
                <Search className="w-4 h-4 text-stone-400 absolute left-3 pointer-events-none stroke-[1.75]" />
                <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Cari menu..."
                    className="w-full pl-9 pr-9 py-2 sm:py-2.5 bg-white rounded-xl border border-stone-200 text-xs sm:text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none focus:border-[#82181A] focus:ring-1 focus:ring-[#82181A]/20 transition-colors shadow-2xs"
                />
                {searchQuery && (
                    <button
                        type="button"
                        onClick={() => setSearchQuery('')}
                        className="absolute right-2.5 p-1 text-stone-400 hover:text-stone-700 rounded-md transition cursor-pointer"
                        aria-label="Hapus pencarian"
                    >
                        <X className="w-3.5 h-3.5 stroke-[2]" />
                    </button>
                )}
            </div>
        </div>
    );
}
