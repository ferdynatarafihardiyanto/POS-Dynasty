/**
 * Presentation Layer Metadata for Kedai Dynasty UI
 * Note: Database data (names, prices, IDs, stock, categories, orders) comes from Laravel.
 * This file only provides visual enhancements (icons, high-res cafe photography, quick actions).
 */

export const CATEGORY_ICONS = {
    'all': '🍽️',
    'popular': '🔥',
    'coffee': '☕',
    'kopi': '☕',
    'non coffee': '🧋',
    'non-coffee': '🧋',
    'non kopi': '🧋',
    'milk based': '🥛',
    'food': '🍛',
    'makanan': '🍛',
    'mie': '🍜',
    'roti bakar': '🍞',
    'snack': '🍟',
    'dessert': '🍰',
};

export const getCategoryIcon = (categoryName = '', categoryId = null) => {
    if (categoryId === 'all') return '🍽️';
    if (categoryId === 'popular') return '🔥';
    
    const key = String(categoryName).toLowerCase().trim();
    for (const [name, icon] of Object.entries(CATEGORY_ICONS)) {
        if (key.includes(name)) return icon;
    }
    return '🍽️';
};

// Curated high quality food & beverage photos mapped to product keywords
export const getProductImage = (productName = '', categoryName = '') => {
    const name = String(productName).toLowerCase();
    const cat = String(categoryName).toLowerCase();

    if (name.includes('americano') || name.includes('black coffee') || name.includes('espresso')) {
        return 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('latte') || name.includes('cappuccino') || name.includes('kopi susu')) {
        return 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('v60') || name.includes('manual brew') || name.includes('kopi')) {
        return 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('matcha')) {
        return 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('choco') || name.includes('coklat') || name.includes('milo')) {
        return 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('tea') || name.includes('teh') || name.includes('lemonade')) {
        return 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('shake') || name.includes('milk') || name.includes('velvet') || name.includes('taro')) {
        return 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('sandwich') || name.includes('roti') || name.includes('toast')) {
        return 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('croissant') || name.includes('pastry')) {
        return 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('fries') || name.includes('kentang')) {
        return 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('mie') || name.includes('noodle') || name.includes('spaghetti') || name.includes('pasta')) {
        return 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80';
    }
    if (name.includes('nasi') || name.includes('chicken') || name.includes('katsu') || name.includes('platter')) {
        return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
    }

    // Default by category
    if (cat.includes('coffee') || cat.includes('kopi')) {
        return 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80';
    }
    if (cat.includes('snack')) {
        return 'https://images.unsplash.com/photo-1541592106381-b31e9677c0e5?auto=format&fit=crop&w=800&q=80';
    }
    if (cat.includes('food') || cat.includes('makan')) {
        return 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80';
    }

    return 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80';
};

export const WAITER_QUICK_ACTIONS = [
    { id: 'cutlery', label: 'Minta Sendok / Garpu / Piring', icon: '🍴', desc: 'Pelayan akan membawakan perlengkapan makan tambahan' },
    { id: 'bill', label: 'Minta Nota / Tagihan Pembayaran', icon: '🧾', desc: 'Pelayan akan membawakan rincian tagihan ke meja' },
    { id: 'clean', label: 'Bersihkan Meja', icon: '🧽', desc: 'Pelayan akan membersihkan meja Anda' },
    { id: 'water', label: 'Minta Air Putih / Es Batu', icon: '🧊', desc: 'Pelayan akan membawakan air mineral atau es batu' },
    { id: 'other', label: 'Panggil Pelayan ke Meja', icon: '🙋', desc: 'Pelayan akan langsung menuju ke meja Anda' },
];

export const AVAILABLE_TABLES = [
    { number: '01', name: 'Meja 01 (Indoor Depan)', token: '3KiGgju7Zb', capacity: '2 Orang' },
    { number: '02', name: 'Meja 02 (Indoor Tengah)', token: 'ZX822wMXX5', capacity: '4 Orang' },
    { number: '03', name: 'Meja 03 (Indoor Sofa)', token: '6q2VRvfV0r', capacity: '6 Orang' },
    { number: '04', name: 'Meja 04 (Outdoor Garden)', token: 'KT1DTfb8cD', capacity: '4 Orang' },
    { number: '05', name: 'Meja 05 (Outdoor Balcony)', token: 'namXX41oft', capacity: '2 Orang' },
];

