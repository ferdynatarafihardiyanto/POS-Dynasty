import React, { useState, useMemo } from 'react';
import { CartProvider, useCart } from './context/CartContext';
import Header from './components/Header';
import SearchBar from './components/SearchBar';
import PromoBanner from './components/PromoBanner';
import CategoryTabs from './components/CategoryTabs';
import MenuCard from './components/MenuCard';
import MenuDetailModal from './components/MenuDetailModal';
import FloatingCartBar from './components/FloatingCartBar';
import CartDrawer from './components/CartDrawer';
import OrderStatusModal from './components/OrderStatusModal';
import OrderHistoryModal from './components/OrderHistoryModal';
import Toast from './components/Toast';
import { Sparkles, UtensilsCrossed, SearchX } from 'lucide-react';

function MainCatalog() {
    const { menuList, selectedDetailItem, setSelectedDetailItem } = useCart();
    const [selectedCategory, setSelectedCategory] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');

    // Filter menu items by category & search query
    const filteredItems = useMemo(() => {
        return menuList.filter((item) => {
            const matchesSearch = searchQuery === '' || 
                item.nama.toLowerCase().includes(searchQuery.toLowerCase()) ||
                (item.deskripsi && item.deskripsi.toLowerCase().includes(searchQuery.toLowerCase()));

            if (!matchesSearch) return false;

            if (selectedCategory === 'all') return true;
            if (selectedCategory === 'popular') return item.is_promo || item.rating >= 4.8 || item.badge?.includes('Populer') || item.badge?.includes('Best');
            return item.kategori_id == selectedCategory;
        });
    }, [menuList, selectedCategory, searchQuery]);

    const handleViewAllMenu = () => {
        setSelectedCategory('all');
        setSearchQuery('');
        const menuSection = document.getElementById('catalog-menu-section');
        if (menuSection) {
            menuSection.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <div className="min-h-screen bg-stone-50 text-stone-900 selection:bg-amber-500 selection:text-white pb-24 flex flex-col w-full">
            {/* Toast Notification */}
            <Toast />

            {/* Top Sticky Header */}
            <Header />

            {/* Main Scrollable View */}
            <main className="flex-1 w-full">
                {/* Search Bar */}
                <SearchBar
                    searchQuery={searchQuery}
                    setSearchQuery={setSearchQuery}
                />

                {/* Promo Banner / Chef Recommendations (Hidden while searching to keep focus) */}
                {!searchQuery && (
                    <PromoBanner
                        onSelectItem={(item) => setSelectedDetailItem(item)}
                        onViewAll={handleViewAllMenu}
                    />
                )}

                {/* Category Tabs */}
                <CategoryTabs
                    selectedCategory={selectedCategory}
                    onSelectCategory={setSelectedCategory}
                />

                {/* Section Header */}
                <section id="catalog-menu-section" className="px-4 sm:px-6 lg:px-8 pt-3 pb-2 flex items-center justify-between">
                    <div className="flex items-center gap-1.5">
                        <h2 className="font-display font-black text-sm sm:text-base text-stone-900 tracking-tight">
                            {searchQuery ? 'Hasil Pencarian Menu' : (selectedCategory === 'all' ? 'Semua Menu' : 'Daftar Menu')}
                        </h2>
                    </div>
                    <span className="text-[11px] font-semibold text-stone-400 bg-stone-200/70 px-2 py-0.5 rounded-full">
                        {filteredItems.length} Menu Tersedia
                    </span>
                </section>

                {/* Responsive Food Grid */}
                <section className="px-4 sm:px-6 lg:px-8 pb-12">
                    {filteredItems.length === 0 ? (
                        <div className="py-16 text-center">
                            <SearchX className="w-10 h-10 text-stone-300 mx-auto mb-2" />
                            <h3 className="font-display font-bold text-stone-700 text-sm">
                                Menu Tidak Ditemukan
                            </h3>
                            <p className="text-xs text-stone-400 mt-1 max-w-xs mx-auto">
                                Coba gunakan kata kunci pencarian yang lain atau pilih kategori Semua Menu.
                            </p>
                            <button
                                onClick={() => {
                                    setSearchQuery('');
                                    setSelectedCategory('all');
                                }}
                                className="mt-4 px-4 py-2 bg-[#881B1E] text-white rounded-xl text-xs font-bold shadow-xs active:scale-95 transition"
                            >
                                Tampilkan Semua Menu
                            </button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
                            {filteredItems.map((item) => (
                                <MenuCard
                                    key={item.id}
                                    item={item}
                                    onSelect={(selected) => setSelectedDetailItem(selected)}
                                />
                            ))}
                        </div>
                    )}
                </section>
            </main>

            {/* Floating Bottom Cart Bar */}
            <FloatingCartBar />

            {/* Modals & Drawers */}
            {selectedDetailItem && (
                <MenuDetailModal
                    key={`detail-${selectedDetailItem.id || selectedDetailItem.backend_id}-${selectedDetailItem.initialQuantity || 1}-${selectedDetailItem.initialNotes || ''}`}
                    item={selectedDetailItem}
                    onClose={() => setSelectedDetailItem(null)}
                />
            )}

            <CartDrawer />
            <OrderStatusModal />
            <OrderHistoryModal />
        </div>
    );
}

export default function CustomerApp() {
    return (
        <CartProvider>
            <MainCatalog />
        </CartProvider>
    );
}
