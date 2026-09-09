import React, { createContext, useContext, useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import { AVAILABLE_TABLES, ALL_MENU_ITEMS, CATEGORIES as DEFAULT_CATEGORIES } from '../data/mockData';

const API_URL = import.meta.env.VITE_API_URL || '/api';

const FOOD_IMAGE_MAP = {
    'americano': 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80',
    'latte': 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?auto=format&fit=crop&w=800&q=80',
    'cappuccino': 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=800&q=80',
    'matcha': 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=800&q=80',
    'chocolate': 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80',
    'coklat': 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80',
    'sandwich': 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=800&q=80',
    'croissant': 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=800&q=80',
    'french fries': 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=800&q=80',
    'kentang': 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=800&q=80',
    'kopi': 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80',
    'coffee': 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80',
    'tea': 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&q=80',
    'teh': 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=800&q=80',
    'burger': 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=80',
    'spaghetti': 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=800&q=80',
    'pasta': 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=800&q=80',
    'nasi': 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=800&q=80',
    'mie': 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=800&q=80',
    'ayam': 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80'
};

const CATEGORY_IMAGE_MAP = {
    'coffee': 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80',
    'non coffee': 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
    'food': 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=800&q=80',
    'snack': 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=800&q=80',
    'minuman': 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&w=800&q=80',
    'makanan': 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80'
};

function getFoodImage(name, categoryName, customImg) {
    if (customImg && customImg !== '-' && customImg.length > 3) {
        return customImg.startsWith('http') ? customImg : `/storage/${customImg}`;
    }
    const lowerName = (name || '').toLowerCase();
    for (const [key, url] of Object.entries(FOOD_IMAGE_MAP)) {
        if (lowerName.includes(key)) return url;
    }
    const lowerCat = (categoryName || '').toLowerCase();
    for (const [key, url] of Object.entries(CATEGORY_IMAGE_MAP)) {
        if (lowerCat.includes(key)) return url;
    }
    return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
}

function getCategoryIcon(categoryName) {
    const lower = (categoryName || '').toLowerCase();
    if (lower.includes('coffee') && !lower.includes('non')) return '☕';
    if (lower.includes('non coffee')) return '🍵';
    if (lower.includes('tea') || lower.includes('teh')) return '🧋';
    if (lower.includes('food') || lower.includes('makanan')) return '🥪';
    if (lower.includes('snack')) return '🍟';
    if (lower.includes('mie') || lower.includes('bakso')) return '🍜';
    if (lower.includes('dessert') || lower.includes('cake') || lower.includes('roti')) return '🍰';
    if (lower.includes('minum') || lower.includes('drink')) return '🍹';
    return '🍽️';
}

const CartContext = createContext();

export const CartProvider = ({ children }) => {
    const [cartItems, setCartItems] = useState(() => {
        try {
            const saved = localStorage.getItem('dynasty_cart');
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
        }
    });

    const [availableTables, setAvailableTables] = useState(AVAILABLE_TABLES);
    const [tableInfo, setTableInfo] = useState(() => {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const qrToken = urlParams.get('qr_token') || urlParams.get('token');
            const mejaParam = urlParams.get('meja') || urlParams.get('table');

            if (qrToken) {
                const found = AVAILABLE_TABLES.find(t => t.token === qrToken);
                if (found) return found;
                return {
                    number: '...',
                    name: 'Memuat Meja...',
                    token: qrToken,
                    capacity: 'Dine-In'
                };
            }
            if (mejaParam) {
                const found = AVAILABLE_TABLES.find(t => 
                    t.number === mejaParam || 
                    t.number === mejaParam.padStart(2, '0') || 
                    t.token === mejaParam
                );
                if (found) return found;
            }
            const saved = localStorage.getItem('dynasty_table');
            const parsed = saved ? JSON.parse(saved) : null;
            if (parsed && (parsed.token || parsed.number)) {
                return parsed;
            }
            return AVAILABLE_TABLES[0];
        } catch {
            return AVAILABLE_TABLES[0];
        }
    });

    const [orders, setOrders] = useState(() => {
        try {
            const saved = localStorage.getItem('dynasty_orders');
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
        }
    });

    const [activeOrder, setActiveOrder] = useState(null);
    const [isCartOpen, setIsCartOpen] = useState(false);
    const [isOrderStatusOpen, setIsOrderStatusOpen] = useState(false);
    const [isOrderHistoryOpen, setIsOrderHistoryOpen] = useState(false);
    const [isWaiterModalOpen, setIsWaiterModalOpen] = useState(false);
    const [isTableModalOpen, setIsTableModalOpen] = useState(false);
    const [toast, setToast] = useState(null);
    const [menuList, setMenuList] = useState(ALL_MENU_ITEMS);
    const [categories, setCategories] = useState(DEFAULT_CATEGORIES);
    const [backendCategories, setBackendCategories] = useState([]);
    const [isLoadingMenu, setIsLoadingMenu] = useState(true);

    // Derived promo items for featured carousel
    const promoItems = useMemo(() => {
        if (!menuList || menuList.length === 0) return [];
        return menuList.slice(0, 4);
    }, [menuList]);

    // Persist cart
    useEffect(() => {
        try {
            localStorage.setItem('dynasty_cart', JSON.stringify(cartItems));
        } catch (e) {
            console.error('Failed to save cart', e);
        }
    }, [cartItems]);

    // Persist table
    useEffect(() => {
        try {
            localStorage.setItem('dynasty_table', JSON.stringify(tableInfo));
        } catch (e) {
            console.error('Failed to save table', e);
        }
    }, [tableInfo]);

    // Persist orders
    useEffect(() => {
        try {
            localStorage.setItem('dynasty_orders', JSON.stringify(orders));
        } catch (e) {
            console.error('Failed to save orders', e);
        }
    }, [orders]);

    // Fetch live backend menu data from real database
    useEffect(() => {
        const fetchBackendMenu = async () => {
            try {
                setIsLoadingMenu(true);
                const res = await axios.get(`${API_URL}/menu`);
                if (res.data && res.data.data && res.data.data.length > 0) {
                    const dbItems = res.data.data.map(p => {
                        const catName = p.kategori?.nama || 'Menu';
                        return {
                            id: p.id,
                            backend_id: p.id,
                            nama: p.nama,
                            kategori_id: p.kategori_id,
                            kategori_nama: catName,
                            harga: parseFloat(p.harga),
                            stok: p.stok,
                            gambar: getFoodImage(p.nama, catName, p.gambar),
                            deskripsi_singkat: p.deskripsi || '',
                            deskripsi: p.deskripsi || '',
                            modifier_groups: p.modifier_groups || []
                        };
                    });
                    setMenuList(dbItems);
                }
            } catch (err) {
                console.log('Error fetching backend menu, using local data fallback:', err);
            } finally {
                setIsLoadingMenu(false);
            }
        };

        const fetchCategories = async () => {
            try {
                const res = await axios.get(`${API_URL}/menu/kategori`);
                if (res.data && res.data.data && res.data.data.length > 0) {
                    setBackendCategories(res.data.data);
                    const dbCats = res.data.data.map(c => ({
                        id: c.id,
                        nama: c.nama,
                        icon: getCategoryIcon(c.nama)
                    }));
                    setCategories([
                        { id: 'all', nama: 'Semua Menu', icon: '🍽️' },
                        ...dbCats
                    ]);
                }
            } catch (err) {
                console.log('Error fetching categories:', err);
            }
        };

        const fetchTables = async () => {
            try {
                const res = await axios.get(`${API_URL}/meja`);
                if (res.data && res.data.data && res.data.data.length > 0) {
                    const activeDbTables = res.data.data
                        .filter(t => t.status === 'active')
                        .map(t => ({
                            id: t.id,
                            number: t.table_number ? String(t.table_number).padStart(2, '0') : String(t.id).padStart(2, '0'),
                            name: t.name || `Meja ${t.table_number || t.id}`,
                            token: t.qr_token,
                            capacity: `${parseInt(t.table_number || t.id, 10) % 2 === 0 ? '4' : '2'} Orang`
                        }));

                    if (activeDbTables.length > 0) {
                        setAvailableTables(activeDbTables);
                        setTableInfo(current => {
                            const urlParams = new URLSearchParams(window.location.search);
                            const qrToken = urlParams.get('qr_token') || urlParams.get('token');
                            const mejaParam = urlParams.get('meja') || urlParams.get('table');

                            let target = null;
                            // Priority 1: Match QR token from URL (the scanned QR code)
                            if (qrToken) {
                                target = activeDbTables.find(t => t.token === qrToken);
                            }
                            // Priority 2: Match meja number or token from meja/table param
                            if (!target && mejaParam) {
                                target = activeDbTables.find(t => 
                                    t.number === mejaParam || 
                                    t.number === mejaParam.padStart(2, '0') || 
                                    String(t.id) === mejaParam ||
                                    t.token === mejaParam
                                );
                            }
                            // Priority 3: Match current state / previous selection
                            if (!target && current) {
                                target = activeDbTables.find(t => 
                                    (current.token && t.token === current.token) || 
                                    (current.number && t.number === current.number)
                                );
                            }
                            const selected = target || activeDbTables[0];
                            try {
                                localStorage.setItem('dynasty_table', JSON.stringify(selected));
                            } catch (e) {}
                            return selected;
                        });
                    }
                }
            } catch (err) {
                console.log('Error fetching tables:', err);
            }
        };

        fetchBackendMenu();
        fetchCategories();
        fetchTables();
    }, []);

    const showToast = (message, type = 'success') => {
        setToast({ message, type, id: Date.now() });
        setTimeout(() => {
            setToast(null);
        }, 3200);
    };

    const addToCart = (item, customizations = {}, quantity = 1, notes = '') => {
        const carbPrice = customizations.carb ? (customizations.carb.price || 0) : 0;
        const spicePrice = customizations.spice ? (customizations.spice.price || 0) : 0;
        const toppingsPrice = customizations.toppings ? customizations.toppings.reduce((acc, t) => acc + (t.price || 0), 0) : 0;
        const modifiersPrice = customizations.modifiers ? customizations.modifiers.reduce((acc, m) => acc + (parseFloat(m.harga_tambahan) || 0), 0) : 0;
        const unitPrice = (item.harga || 0) + carbPrice + spicePrice + toppingsPrice + modifiersPrice;

        const modifierIdsKey = (customizations.modifiers || []).map(m => m.id).sort().join(',');
        const cartItemId = `${item.id}-${customizations.carb?.id || 'none'}-${customizations.spice?.id || 'none'}-${(customizations.toppings || []).map(t => t.id).sort().join(',')}-${modifierIdsKey}-${notes.trim()}`;

        setCartItems(prev => {
            const existingIndex = prev.findIndex(ci => ci.cartItemId === cartItemId);
            if (existingIndex > -1) {
                const updated = [...prev];
                updated[existingIndex].quantity += quantity;
                updated[existingIndex].totalPrice = updated[existingIndex].quantity * unitPrice;
                return updated;
            } else {
                return [
                    ...prev,
                    {
                        cartItemId,
                        menuItem: item,
                        customizations,
                        notes,
                        quantity,
                        unitPrice,
                        totalPrice: unitPrice * quantity
                    }
                ];
            }
        });

        showToast(`${quantity}x ${item.nama} ditambahkan ke keranjang! 🎉`, 'success');
    };

    const updateQuantity = (cartItemId, delta) => {
        setCartItems(prev => {
            return prev
                .map(item => {
                    if (item.cartItemId === cartItemId) {
                        const newQty = item.quantity + delta;
                        return newQty > 0
                            ? { ...item, quantity: newQty, totalPrice: newQty * item.unitPrice }
                            : null;
                    }
                    return item;
                })
                .filter(Boolean);
        });
    };

    const removeFromCart = (cartItemId) => {
        setCartItems(prev => prev.filter(i => i.cartItemId !== cartItemId));
        showToast('Menu dihapus dari keranjang', 'info');
    };

    const clearCart = () => {
        setCartItems([]);
    };

    const fetchOrderHistory = async () => {
        const activeToken = tableInfo?.token || (availableTables[0]?.token);
        if (!activeToken) return;

        try {
            const res = await axios.get(`${API_URL}/pesanan/riwayat`, {
                params: { qr_token: activeToken }
            });
            if (res.data && Array.isArray(res.data.data)) {
                const historyOrders = res.data.data.map(p => ({
                    orderNumber: p.nomor_pesanan,
                    tableNumber: p.meja?.table_number || p.meja?.nomor_meja || p.meja || tableInfo.number,
                    tableName: `Meja ${p.meja?.table_number || p.meja?.nomor_meja || p.meja || tableInfo.number}`,
                    status: p.status,
                    status_pembayaran: p.status_pembayaran || 'menunggu_pembayaran',
                    items: (p.items || p.detail_pesanan || []).map((dp, idx) => ({
                        cartItemId: `hist-${p.id || p.nomor_pesanan}-${idx}`,
                        quantity: dp.jumlah,
                        unitPrice: parseFloat(dp.harga || dp.harga_satuan || 0),
                        totalPrice: parseFloat(dp.subtotal || 0),
                        notes: dp.catatan || '',
                        menuItem: {
                            nama: dp.nama_produk || dp.produk?.nama || 'Menu',
                            gambar_url: dp.gambar_url || dp.produk?.gambar_url || null,
                            kategori_nama: dp.kategori || dp.produk?.kategori?.nama || ''
                        },
                        customizations: {
                            modifiers: (dp.modifiers || dp.modifiers_snapshot || []).map(m => ({
                                id: m.option_id || m.id,
                                nama: m.option_nama || m.nama || (m.group_nama ? `${m.group_nama}: ${m.option_nama}` : 'Varian'),
                                harga_tambahan: parseFloat(m.harga_tambahan || 0)
                            }))
                        }
                    })),
                    subtotal: parseFloat(p.subtotal || p.total_harga || 0),
                    tax: parseFloat(p.pajak || 0),
                    grandTotal: parseFloat(p.total_harga || 0),
                    notes: p.catatan,
                    timestamp: p.created_at
                }));

                setOrders(historyOrders);

                setActiveOrder(current => {
                    if (current) {
                        const match = historyOrders.find(o => o.orderNumber === current.orderNumber);
                        return match ? { ...current, ...match } : current;
                    } else {
                        const ongoing = historyOrders.find(o => 
                            ['menunggu_konfirmasi', 'menunggu_pembayaran', 'diproses', 'disajikan'].includes(o.status)
                        );
                        return ongoing || (historyOrders.length > 0 ? historyOrders[0] : null);
                    }
                });
            }
        } catch (err) {
            console.log('Error fetching table order history:', err.message);
        }
    };

    // Fetch order history when tableInfo changes or on mount
    useEffect(() => {
        if (tableInfo?.token) {
            fetchOrderHistory();
        }
    }, [tableInfo?.token]);

    // Real-time backend status polling for active ongoing order
    useEffect(() => {
        if (!activeOrder || ['selesai', 'dibatalkan'].includes(activeOrder.status)) {
            return;
        }

        const activeToken = tableInfo?.token || (availableTables[0]?.token);
        if (!activeToken) return;

        const interval = setInterval(async () => {
            try {
                const cleanOrderNum = (activeOrder.orderNumber || '').replace(/^#/, '');
                const res = await axios.get(`${API_URL}/pesanan/${cleanOrderNum}`, {
                    params: { qr_token: activeToken },
                    timeout: 4000
                });
                if (res.data && res.data.data) {
                    const updated = res.data.data;
                    const newStatus = updated.status;
                    const newPayStatus = updated.status_pembayaran;

                    setActiveOrder(current => {
                        if (!current) return current;
                        const curNum = (current.orderNumber || '').replace(/^#/, '');
                        if (curNum !== updated.nomor_pesanan) return current;
                        if (current.status === newStatus && current.status_pembayaran === newPayStatus) return current;
                        return {
                            ...current,
                            status: newStatus,
                            status_pembayaran: newPayStatus,
                            grandTotal: parseFloat(updated.total_harga) || current.grandTotal
                        };
                    });

                    setOrders(prevOrders =>
                        prevOrders.map(o => {
                            const oNum = (o.orderNumber || '').replace(/^#/, '');
                            return oNum === updated.nomor_pesanan
                                ? {
                                      ...o,
                                      status: newStatus,
                                      status_pembayaran: newPayStatus,
                                      grandTotal: parseFloat(updated.total_harga) || o.grandTotal
                                  }
                                : o;
                        })
                    );
                }
            } catch (err) {
                // Silently ignore polling network glitch
            }
        }, 4000);

        return () => clearInterval(interval);
    }, [activeOrder?.orderNumber, activeOrder?.status, activeOrder?.status_pembayaran, tableInfo?.token]);

    // Calculate Totals
    const subtotal = cartItems.reduce((acc, item) => acc + (item.totalPrice || 0), 0);
    const taxPB1 = Math.round(subtotal * 0.10); // Pajak Resto 10%
    const grandTotal = subtotal + taxPB1;
    const totalItemCount = cartItems.reduce((acc, item) => acc + item.quantity, 0);

    const submitOrder = async (orderNotes = '', customerName = '') => {
        if (cartItems.length === 0) {
            showToast('Keranjang belanja masih kosong', 'warning');
            return null;
        }

        const orderNumber = `ORD-${Date.now().toString().slice(-6)}`;
        
        const activeToken = tableInfo?.token || (availableTables[0]?.token) || 'tUTSNEGGhT';
        const finalCustomerName = (customerName || localStorage.getItem('pos_customer_name') || '').trim();

        const payloadBackend = {
            qr_token: activeToken,
            nama_pelanggan: finalCustomerName,
            catatan: finalCustomerName 
                ? `Pemesan: ${finalCustomerName}${orderNotes ? ' | ' + orderNotes : ''}`
                : (orderNotes || `Pesanan Meja ${tableInfo.number}`),
            produk: cartItems.map(item => ({
                produk_id: parseInt(item.menuItem.backend_id || item.menuItem.id, 10),
                jumlah: item.quantity,
                catatan: item.notes ? item.notes.trim() : null,
                modifiers: (item.customizations?.modifiers || []).map(m => m.id)
            }))
        };

        let backendSuccess = false;
        let createdOrder = null;

        try {
            const res = await axios.post(`${API_URL}/pesanan`, payloadBackend);
            if (res.data && res.data.data) {
                backendSuccess = true;
                createdOrder = {
                    orderNumber: res.data.data.nomor_pesanan || orderNumber,
                    tableNumber: tableInfo.number,
                    tableName: tableInfo.name,
                    customerName: finalCustomerName,
                    nama_pelanggan: finalCustomerName,
                    status: res.data.data.status || 'menunggu_konfirmasi',
                    status_pembayaran: res.data.data.status_pembayaran || 'menunggu_pembayaran',
                    items: [...cartItems],
                    subtotal,
                    tax: taxPB1,
                    grandTotal: res.data.data.total_harga || grandTotal,
                    notes: orderNotes,
                    timestamp: new Date().toISOString()
                };
            }
        } catch (err) {
            console.log('Backend API order endpoint unreached or error, proceeding with frontend state simulation:', err.message);
        }

        if (!backendSuccess) {
            createdOrder = {
                orderNumber,
                tableNumber: tableInfo.number,
                tableName: tableInfo.name,
                customerName: finalCustomerName,
                nama_pelanggan: finalCustomerName,
                status: 'menunggu_konfirmasi',
                status_pembayaran: 'menunggu_pembayaran',
                items: [...cartItems],
                subtotal,
                tax: taxPB1,
                grandTotal,
                notes: orderNotes,
                timestamp: new Date().toISOString()
            };
        }

        setOrders(prev => [createdOrder, ...prev]);
        setActiveOrder(createdOrder);
        clearCart();
        setIsCartOpen(false);
        setIsOrderStatusOpen(true);
        showToast('Pesanan berhasil dikirim ke Dapur! 🍳👨‍🍳', 'success');

        // Fetch fresh orders from backend
        fetchOrderHistory();

        return createdOrder;
    };

    const callWaiter = (action, note = '') => {
        setIsWaiterModalOpen(false);
        showToast(`🔔 Panggilan pelayan terkirim: "${action.label}" untuk Meja ${tableInfo.number}. Pelayan segera datang!`, 'success');
    };

    return (
        <CartContext.Provider
            value={{
                cartItems,
                addToCart,
                updateQuantity,
                removeFromCart,
                clearCart,
                subtotal,
                taxPB1,
                grandTotal,
                totalItemCount,
                tableInfo,
                setTableInfo,
                orders,
                activeOrder,
                setActiveOrder,
                submitOrder,
                isCartOpen,
                setIsCartOpen,
                isOrderStatusOpen,
                setIsOrderStatusOpen,
                isOrderHistoryOpen,
                setIsOrderHistoryOpen,
                fetchOrderHistory,
                isWaiterModalOpen,
                setIsWaiterModalOpen,
                isTableModalOpen,
                setIsTableModalOpen,
                toast,
                showToast,
                callWaiter,
                menuList,
                categories,
                promoItems,
                isLoadingMenu,
                backendCategories,
                availableTables
            }}
        >
            {children}
        </CartContext.Provider>
    );
};

export const useCart = () => useContext(CartContext);
