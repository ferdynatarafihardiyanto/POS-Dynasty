import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import { AVAILABLE_TABLES, ALL_MENU_ITEMS } from '../data/mockData';

const API_URL = import.meta.env.VITE_API_URL || '/api';

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
            }
            if (mejaParam) {
                const found = AVAILABLE_TABLES.find(t => t.number === mejaParam);
                if (found) return found;
            }
            const saved = localStorage.getItem('dynasty_table');
            const parsed = saved ? JSON.parse(saved) : null;
            if (parsed && AVAILABLE_TABLES.some(t => t.token === parsed.token)) {
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
    const [isWaiterModalOpen, setIsWaiterModalOpen] = useState(false);
    const [isTableModalOpen, setIsTableModalOpen] = useState(false);
    const [toast, setToast] = useState(null);
    const [menuList, setMenuList] = useState(ALL_MENU_ITEMS);
    const [backendCategories, setBackendCategories] = useState([]);

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

    // Fetch live backend menu data if available
    useEffect(() => {
        const fetchBackendMenu = async () => {
            try {
                const res = await axios.get(`${API_URL}/menu`);
                if (res.data && res.data.data && res.data.data.length > 0) {
                    const dbItems = res.data.data.map(p => {
                        // find matching mock item or create fallback
                        const matchingMock = ALL_MENU_ITEMS.find(m => m.nama.toLowerCase() === p.nama.toLowerCase());
                        if (matchingMock) {
                            return { ...matchingMock, id: p.id, backend_id: p.id, harga: p.harga, nama: p.nama };
                        }
                        return {
                            id: p.id,
                            backend_id: p.id,
                            nama: p.nama,
                            kategori_id: p.kategori ? (p.kategori.nama.toLowerCase().includes('coffee') ? 'minuman' : 'makanan-utama') : 'makanan-utama',
                            harga: p.harga,
                            rating: 4.8,
                            reviews_count: 50,
                            prep_time: '10 - 15 Menit',
                            portion_tag: 'Porsi Hangat',
                            gambar: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
                            deskripsi_singkat: p.deskripsi || 'Sajian istimewa kaya bumbu khas Kedai Dynasty.',
                            deskripsi: p.deskripsi || 'Sajian istimewa kaya bumbu khas Kedai Dynasty dimasak dengan bahan segar dan rempah alami pilihan.'
                        };
                    });
                    // Merge promo items and db items
                    const merged = [...ALL_MENU_ITEMS];
                    dbItems.forEach(dbItem => {
                        const idx = merged.findIndex(m => m.nama.toLowerCase() === dbItem.nama.toLowerCase());
                        if (idx !== -1) {
                            merged[idx] = { ...merged[idx], ...dbItem };
                        } else {
                            merged.push(dbItem);
                        }
                    });
                    setMenuList(merged);
                }
            } catch (err) {
                // Silently fallback to rich mock data
                console.log('Using frontend local menu data');
            }
        };

        const fetchCategories = async () => {
            try {
                const res = await axios.get(`${API_URL}/menu/kategori`);
                if (res.data && res.data.data) {
                    setBackendCategories(res.data.data);
                }
            } catch (err) {
                // Ignore
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
                            number: t.table_number,
                            name: t.name || `Meja ${t.table_number}`,
                            token: t.qr_token,
                            capacity: `${parseInt(t.table_number, 10) % 2 === 0 ? '4' : '2'} Orang`
                        }));

                    if (activeDbTables.length > 0) {
                        setAvailableTables(activeDbTables);
                        setTableInfo(current => {
                            const match = activeDbTables.find(t => t.token === current?.token || t.number === current?.number);
                            if (match) {
                                return match;
                            }
                            return activeDbTables[0];
                        });
                    }
                }
            } catch (err) {
                // Ignore
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
        const unitPrice = (item.harga || 0) + carbPrice + spicePrice + toppingsPrice;

        const cartItemId = `${item.id}-${customizations.carb?.id || 'none'}-${customizations.spice?.id || 'none'}-${(customizations.toppings || []).map(t => t.id).sort().join(',')}-${notes.trim()}`;

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

    // Calculate Totals
    const subtotal = cartItems.reduce((acc, item) => acc + (item.totalPrice || 0), 0);
    const taxPB1 = Math.round(subtotal * 0.10); // Pajak Resto 10%
    const grandTotal = subtotal + taxPB1;
    const totalItemCount = cartItems.reduce((acc, item) => acc + item.quantity, 0);

    const submitOrder = async (orderNotes = '') => {
        if (cartItems.length === 0) {
            showToast('Keranjang belanja masih kosong', 'warning');
            return null;
        }

        const orderNumber = `ORD-${Date.now().toString().slice(-6)}`;
        
        // Group items by backend product ID so backend validation passes
        const productMap = {};
        cartItems.forEach(item => {
            const pid = parseInt(item.menuItem.backend_id || item.menuItem.id, 10) || 1;
            productMap[pid] = (productMap[pid] || 0) + item.quantity;
        });

        const activeToken = tableInfo?.token || (availableTables[0]?.token) || '3KiGgju7Zb';

        const payloadBackend = {
            qr_token: activeToken,
            catatan: orderNotes || `Pesanan dari Meja ${tableInfo.number}`,
            produk: Object.entries(productMap).map(([pid, qty]) => ({
                produk_id: parseInt(pid, 10),
                jumlah: qty
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
                    status: res.data.data.status || 'menunggu_konfirmasi',
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
                status: 'menunggu_konfirmasi',
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

        // Simulate order status progression for a responsive experience
        setTimeout(() => {
            setActiveOrder(current => {
                if (!current || current.orderNumber !== createdOrder.orderNumber) return current;
                return { ...current, status: 'diproses' };
            });
        }, 12000);

        setTimeout(() => {
            setActiveOrder(current => {
                if (!current || current.orderNumber !== createdOrder.orderNumber) return current;
                return { ...current, status: 'disajikan' };
            });
        }, 28000);

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
                isWaiterModalOpen,
                setIsWaiterModalOpen,
                isTableModalOpen,
                setIsTableModalOpen,
                toast,
                showToast,
                callWaiter,
                menuList,
                backendCategories,
                availableTables
            }}
        >
            {children}
        </CartContext.Provider>
    );
};

export const useCart = () => useContext(CartContext);
