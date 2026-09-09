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
import PaymentPage from './components/PaymentPage';
import OrderStatusModal from './components/OrderStatusModal';
import WaiterCallModal from './components/WaiterCallModal';
import TableScannerModal from './components/TableScannerModal';
import Toast from './components/Toast';
import { Sparkles, UtensilsCrossed, SearchX } from 'lucide-react';

function MainCatalog() {
    const { menuList, isLoadingMenu, menuError, setIsCartOpen } = useCart();
    const [selectedCategory, setSelectedCategory] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedDetailItem, setSelectedDetailItem] = useState(null);
    const [isPaymentOpen, setIsPaymentOpen] = useState(false);
    const [buyerName, setBuyerName] = useState('');
    const [orderNotes, setOrderNotes] = useState('');

    // Filter menu items by category & search query
    const filteredItems = useMemo(() => {
        return menuList.filter((item) => {
            const matchesSearch = searchQuery === '' || 
                item.nama.toLowerCase().includes(searchQuery.toLowerCase()) ||
                (item.deskripsi && item.deskripsi.toLowerCase().includes(searchQuery.toLowerCase()));

            if (!matchesSearch) return false;

            if (selectedCategory === 'all') return true;
            if (selectedCategory === 'popular') return item.is_promo || item.rating >= 4.8 || item.badge?.includes('Populer') || item.badge?.includes('Best');
            return String(item.kategori_id) === String(selectedCategory);
        });
    }, [menuList, selectedCategory, searchQuery]);

    const handleProceedToPayment = (name, notes) => {
        setBuyerName(name);
        setOrderNotes(notes);
        setIsCartOpen(false);
        setIsPaymentOpen(true);
    };

    return (
        <div className="min-h-screen bg-stone-100 flex justify-center selection:bg-amber-500 selection:text-white pb-24">
            {/* Mobile View Container */}
            <div className="w-full max-w-md bg-stone-50 min-h-screen shadow-2xl relative flex flex-col border-x border-stone-200/60">

                {/* Toast Notification */}
                <Toast />

                {/* Top Sticky Header */}
                <Header />

                {/* Main Scrollable View */}
                <main className="flex-1">
                    {/* Search Bar */}
                    <SearchBar
                        searchQuery={searchQuery}
                        setSearchQuery={setSearchQuery}
                    />

                    {/* Promo Banner / Chef Recommendations (Hidden while searching to keep focus) */}
                    {!searchQuery && (
                        <PromoBanner onSelectItem={(item) => setSelectedDetailItem(item)} />
                    )}

                    {/* Category Tabs */}
                    <CategoryTabs
                        selectedCategory={selectedCategory}
                        onSelectCategory={setSelectedCategory}
                    />

                    {/* Section Header */}
                    <section className="px-4 pt-3 pb-2 flex items-center justify-between">
                        <div className="flex items-center gap-1.5">
                            <h2 className="font-display font-black text-sm text-stone-900 tracking-tight">
                                {searchQuery ? 'Hasil Pencarian Menu' : 'Daftar Menu Kedai'}
                            </h2>
                            {!searchQuery && (
                                <Sparkles className="w-3.5 h-3.5 text-amber-500 fill-amber-400" />
                            )}
                        </div>
                        <span className="text-[11px] font-semibold text-stone-400 bg-stone-200/70 px-2 py-0.5 rounded-full">
                            {isLoadingMenu ? 'Memuat...' : `${filteredItems.length} Menu Tersedia`}
                        </span>
                    </section>

                    {/* Error Banner if any */}
                    {menuError && (
                        <div className="mx-4 mb-4 p-3 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 flex items-center justify-between">
                            <span>{menuError}</span>
                            <button
                                onClick={() => window.location.reload()}
                                className="font-bold underline text-rose-800 ml-2"
                            >
                                Muat Ulang
                            </button>
                        </div>
                    )}

                    {/* 2-Column Food Grid */}
                    <section className="px-4 pb-12">
                        {isLoadingMenu ? (
                            <div className="grid grid-cols-2 gap-3">
                                {[1, 2, 3, 4, 5, 6].map((n) => (
                                    <div key={n} className="bg-white rounded-3xl p-3 border border-stone-100 shadow-xs space-y-2">
                                        <div className="w-full aspect-square rounded-2xl bg-stone-200 shimmer" />
                                        <div className="h-3 w-3/4 bg-stone-200 rounded-full shimmer" />
                                        <div className="h-2.5 w-1/2 bg-stone-200 rounded-full shimmer" />
                                        <div className="flex justify-between items-center pt-2">
                                            <div className="h-4 w-1/3 bg-stone-200 rounded-full shimmer" />
                                            <div className="w-7 h-7 rounded-xl bg-stone-200 shimmer" />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : filteredItems.length === 0 ? (
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
                            <div className="grid grid-cols-2 gap-3">
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
                        item={selectedDetailItem}
                        onClose={() => setSelectedDetailItem(null)}
                    />
                )}

                <CartDrawer onProceedToPayment={handleProceedToPayment} />
                <PaymentPage 
                    isOpen={isPaymentOpen} 
                    onClose={() => setIsPaymentOpen(false)} 
                    buyerName={buyerName} 
                    orderNotes={orderNotes} 
                />
                <OrderStatusModal />
                <WaiterCallModal />
                <TableScannerModal />

            </div>
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
