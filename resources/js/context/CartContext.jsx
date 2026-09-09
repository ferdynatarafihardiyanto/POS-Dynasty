import React, {
    createContext,
    useContext,
    useState,
    useEffect,
    useCallback,
    useMemo
} from 'react';
import axios from 'axios';
import { getProductImage, AVAILABLE_TABLES } from '../data/mockData';

const API_URL = import.meta.env.VITE_API_URL || '/api';

const CartContext = createContext();

const getQrTokenFromUrl = () => {
    try {
        const url = new URL(window.location.href);

        // Support the QR URL format used by the backend:
        // /menu/meja/{qr_token}
        const pathMatch = url.pathname.match(/\/menu\/meja\/([^/]+)/);
        if (pathMatch?.[1]) return decodeURIComponent(pathMatch[1]);

        return (
            url.searchParams.get('qr_token') ||
            url.searchParams.get('token') ||
            null
        );
    } catch {
        return null;
    }
};

const normalizeProduct = (product) => {
    const categoryName = product.kategori?.nama || '';
    const gambar = product.gambar || getProductImage(product.nama, categoryName);

    return {
        id: product.id,
        backend_id: product.id,
        nama: product.nama,
        deskripsi: product.deskripsi || '',
        deskripsi_singkat: product.deskripsi || `${product.nama} pilihan khas Kedai Dynasty.`,
        harga: Number(product.harga) || 0,
        stok: typeof product.stok === 'number' ? product.stok : (product.stok ? Number(product.stok) : 99),
        aktif: Boolean(product.aktif ?? true),
        kategori_id: product.kategori_id ?? product.kategori?.id,
        kategori: product.kategori || { id: product.kategori_id, nama: categoryName },
        gambar: gambar,
        rating: 4.8,
        reviews_count: 50 + (product.id * 17) % 200,
        prep_time: '10 - 15 Menit',
    };
};

export const CartProvider = ({ children }) => {
    // Initial data passed directly from Laravel Blade
    const initialData = typeof window !== 'undefined' && window.__INITIAL_DATA__ ? window.__INITIAL_DATA__ : null;

    const [tableInfo, setTableInfo] = useState(() => {
        if (initialData?.tableInfo) return initialData.tableInfo;
        try {
            const saved = localStorage.getItem('dynasty_table');
            return saved ? JSON.parse(saved) : {
                id: 1,
                number: '01',
                name: 'Meja 01',
                token: '3KiGgju7Zb',
            };
        } catch {
            return {
                id: 1,
                number: '01',
                name: 'Meja 01',
                token: '3KiGgju7Zb',
            };
        }
    });

    const [availableTables, setAvailableTables] = useState(() => {
        if (Array.isArray(initialData?.availableTables) && initialData.availableTables.length > 0) {
            return initialData.availableTables;
        }
        return AVAILABLE_TABLES;
    });

    const [backendCategories, setBackendCategories] = useState(() => {
        if (Array.isArray(initialData?.categories) && initialData.categories.length > 0) {
            return initialData.categories;
        }
        return [];
    });

    const [menuList, setMenuList] = useState(() => {
        if (Array.isArray(initialData?.products) && initialData.products.length > 0) {
            return initialData.products.map(normalizeProduct);
        }
        return [];
    });

    const [cartItems, setCartItems] = useState(() => {
        try {
            const saved = localStorage.getItem('dynasty_cart');
            return saved ? JSON.parse(saved) : [];
        } catch {
            return [];
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
    const [isLoadingMenu, setIsLoadingMenu] = useState(() => !initialData?.products?.length);
    const [menuError, setMenuError] = useState(null);

    // Filtered orders strictly for the current active table / qrToken
    const currentTableToken = tableInfo?.token;
    const tableOrders = useMemo(() => {
        if (!currentTableToken) return [];
        return orders.filter(o => o.qrToken && o.qrToken === currentTableToken);
    }, [orders, currentTableToken]);

    // Keep activeOrder aligned with the currently selected table
    useEffect(() => {
        const currentToken = tableInfo?.token;
        if (!currentToken) {
            setActiveOrder(null);
            return;
        }

        setActiveOrder(prevActive => {
            if (prevActive && prevActive.qrToken === currentToken) {
                return prevActive;
            }
            const currentTableOrders = orders.filter(o => o.qrToken && o.qrToken === currentToken);
            return currentTableOrders.length > 0 ? currentTableOrders[0] : null;
        });
    }, [tableInfo?.token, orders]);

    // Save state to localStorage
    useEffect(() => {
        try {
            localStorage.setItem('dynasty_cart', JSON.stringify(cartItems));
        } catch (e) {
            console.error('Failed to save cart', e);
        }
    }, [cartItems]);

    useEffect(() => {
        try {
            if (tableInfo) {
                localStorage.setItem('dynasty_table', JSON.stringify(tableInfo));
            }
        } catch (e) {
            console.error('Failed to save table', e);
        }
    }, [tableInfo]);

    useEffect(() => {
        try {
            localStorage.setItem('dynasty_orders', JSON.stringify(orders));
        } catch (e) {
            console.error('Failed to save orders', e);
        }
    }, [orders]);

    // Client-side fallback fetch only if server initial data was not available
    useEffect(() => {
        if (initialData?.products?.length > 0 && initialData?.categories?.length > 0) {
            setIsLoadingMenu(false);
            return;
        }

        const fetchFallbackData = async () => {
            setIsLoadingMenu(true);
            setMenuError(null);

            try {
                const qrToken = getQrTokenFromUrl();

                if (qrToken) {
                    try {
                        const tableResponse = await axios.get(`${API_URL}/meja/${encodeURIComponent(qrToken)}`);
                        if (tableResponse.data?.data) {
                            const mejaData = tableResponse.data.data;
                            setTableInfo({
                                id: mejaData.id,
                                number: String(mejaData.nomor_meja || mejaData.table_number || '01'),
                                name: mejaData.nama_meja || mejaData.name || `Meja ${mejaData.nomor_meja || '01'}`,
                                token: qrToken,
                            });
                        }
                    } catch (e) {
                        console.warn('Could not load specific table info via API', e);
                    }
                }

                const [menuRes, catRes] = await Promise.all([
                    axios.get(`${API_URL}/menu`),
                    axios.get(`${API_URL}/menu/kategori`),
                ]);

                const prods = Array.isArray(menuRes.data?.data) ? menuRes.data.data.map(normalizeProduct) : [];
                const cats = Array.isArray(catRes.data?.data) ? catRes.data.data : [];

                setMenuList(prods);
                setBackendCategories(cats);

                try {
                    const tablesRes = await axios.get(`${API_URL}/meja`);
                    if (Array.isArray(tablesRes.data?.data)) {
                        setAvailableTables(tablesRes.data.data.map(t => ({
                            id: t.id,
                            number: String(t.table_number || t.nomor_meja),
                            name: t.name || t.nama_meja || `Meja ${t.table_number || t.nomor_meja}`,
                            token: t.qr_token || t.kode_meja,
                        })));
                    }
                } catch (e) {
                    console.warn('Could not load available tables', e);
                }
            } catch (err) {
                console.error('Failed to load menu data:', err);
                setMenuError('Gagal memuat daftar menu dari server.');
            } finally {
                setIsLoadingMenu(false);
            }
        };

        fetchFallbackData();
    }, []);

    // Refresh live order statuses from backend
    const refreshOrders = useCallback(async () => {
        const qrToken = tableInfo?.token || getQrTokenFromUrl();
        if (!qrToken) return;

        const currentTableOrders = orders.filter(o => o.qrToken && o.qrToken === qrToken);
        if (currentTableOrders.length === 0) return;

        try {
            const response = await axios.get(`${API_URL}/pesanan`, {
                params: { qr_token: qrToken }
            });

            const backendOrders = Array.isArray(response.data?.data) ? response.data.data : [];
            if (backendOrders.length === 0) return;

            setOrders(prevOrders =>
                prevOrders.map(localOrder => {
                    if (localOrder.qrToken !== qrToken) return localOrder;

                    const matched = backendOrders.find(b => b.nomor_pesanan === localOrder.orderNumber);
                    if (!matched) return localOrder;

                    return {
                        ...localOrder,
                        status: matched.status || localOrder.status,
                        grandTotal: matched.total_harga ?? localOrder.grandTotal,
                    };
                })
            );

            setActiveOrder(prevActive => {
                if (!prevActive || prevActive.qrToken !== qrToken) return prevActive;
                const matched = backendOrders.find(b => b.nomor_pesanan === prevActive.orderNumber);
                if (!matched) return prevActive;

                return {
                    ...prevActive,
                    status: matched.status || prevActive.status,
                    grandTotal: matched.total_harga ?? prevActive.grandTotal,
                };
            });
        } catch (e) {
            console.warn('Could not refresh orders status from server', e);
        }
    }, [tableInfo?.token, orders]);

    useEffect(() => {
        if (tableOrders.length > 0) {
            const interval = setInterval(refreshOrders, 10000);
            return () => clearInterval(interval);
        }
    }, [refreshOrders, tableOrders.length]);

    const showToast = (message, type = 'success') => {
        setToast({ message, type, id: Date.now() });
        setTimeout(() => {
            setToast(null);
        }, 3200);
    };

    const addToCart = (item, quantity = 1, notes = '') => {
        const availableStock = item.stok ?? 99;
        if (availableStock <= 0) {
            showToast(`Maaf, menu ${item.nama} sedang habis.`, 'warning');
            return;
        }

        const cartItemId = `${item.id}-${notes.trim()}`;
        const unitPrice = item.harga || 0;

        setCartItems(prev => {
            const existingIndex = prev.findIndex(c => c.cartItemId === cartItemId);

            if (existingIndex > -1) {
                const updated = [...prev];
                const newQty = updated[existingIndex].quantity + quantity;
                if (newQty > availableStock) {
                    showToast(`Maksimal stok tersedia hanya ${availableStock}`, 'warning');
                    updated[existingIndex].quantity = availableStock;
                    updated[existingIndex].totalPrice = availableStock * unitPrice;
                } else {
                    updated[existingIndex].quantity = newQty;
                    updated[existingIndex].totalPrice = newQty * unitPrice;
                }
                return updated;
            }

            const initialQty = Math.min(quantity, availableStock);
            return [
                ...prev,
                {
                    cartItemId,
                    menuItem: item,
                    notes,
                    quantity: initialQty,
                    unitPrice,
                    totalPrice: unitPrice * initialQty,
                }
            ];
        });

        showToast(`${quantity}x ${item.nama} ditambahkan ke pesanan! ☕`, 'success');
    };

    const updateQuantity = (cartItemId, delta) => {
        setCartItems(prev =>
            prev
                .map(item => {
                    if (item.cartItemId === cartItemId) {
                        const maxStock = item.menuItem.stok ?? 99;
                        const newQty = item.quantity + delta;

                        if (newQty > maxStock) {
                            showToast(`Stok maksimal untuk menu ini adalah ${maxStock}`, 'warning');
                            return item;
                        }

                        return newQty > 0
                            ? {
                                ...item,
                                quantity: newQty,
                                totalPrice: newQty * item.unitPrice,
                            }
                            : null;
                    }
                    return item;
                })
                .filter(Boolean)
        );
    };

    const removeFromCart = (cartItemId) => {
        setCartItems(prev => prev.filter(item => item.cartItemId !== cartItemId));
        showToast('Menu dihapus dari pesanan', 'info');
    };

    const clearCart = () => {
        setCartItems([]);
    };

    const subtotal = cartItems.reduce((sum, item) => sum + (item.totalPrice || 0), 0);
    const taxPB1 = Math.round(subtotal * 0.1);
    const grandTotal = subtotal + taxPB1;
    const totalItemCount = cartItems.reduce((sum, item) => sum + item.quantity, 0);

    const submitOrder = async (orderNotes = '') => {
        if (cartItems.length === 0) {
            showToast('Keranjang pesanan masih kosong', 'warning');
            return null;
        }

        const qrToken = tableInfo?.token || getQrTokenFromUrl();

        if (!qrToken) {
            showToast('QR meja belum terdeteksi. Silakan pilih meja terlebih dahulu.', 'warning');
            setIsTableModalOpen(true);
            return null;
        }

        // Aggregate quantities per product_id strictly following backend contract
        const productMap = {};
        cartItems.forEach(item => {
            const productId = parseInt(item.menuItem.backend_id || item.menuItem.id, 10);
            if (!Number.isNaN(productId)) {
                productMap[productId] = (productMap[productId] || 0) + item.quantity;
            }
        });

        // Construct exact backend payload
        const payloadBackend = {
            qr_token: qrToken,
            catatan: orderNotes || (tableInfo?.number ? `Pesanan dari Meja ${tableInfo.number}` : ''),
            produk: Object.entries(productMap).map(([productId, quantity]) => ({
                produk_id: parseInt(productId, 10),
                jumlah: quantity,
            })),
        };

        try {
            const response = await axios.post(`${API_URL}/pesanan`, payloadBackend);
            const data = response.data?.data;

            if (!data) {
                throw new Error('Response dari server tidak valid.');
            }

            const createdOrder = {
                qrToken: qrToken,
                orderNumber: data.nomor_pesanan,
                tableNumber: String(data.meja ?? tableInfo?.number),
                tableName: tableInfo?.name || `Meja ${data.meja ?? tableInfo?.number}`,
                status: data.status || 'menunggu_pembayaran',
                items: [...cartItems],
                subtotal,
                tax: taxPB1,
                grandTotal: data.total_harga ?? grandTotal,
                notes: orderNotes,
                timestamp: new Date().toISOString(),
                detail: data.detail || [],
            };

            setOrders(prev => [createdOrder, ...prev]);
            setActiveOrder(createdOrder);
            clearCart();
            setIsCartOpen(false);
            setIsOrderStatusOpen(true);
            showToast(`Pesanan #${createdOrder.orderNumber} berhasil dibuat! 🎉`, 'success');

            return createdOrder;
        } catch (error) {
            console.error('Failed to submit order to backend:', error);
            const message = error?.response?.data?.message || 'Pesanan gagal diproses oleh server. Silakan coba lagi.';
            showToast(message, 'error');
            return null;
        }
    };

    const callWaiter = (action, note = '') => {
        setIsWaiterModalOpen(false);
        showToast(`Permintaan "${action.label}" telah dicatat untuk Meja ${tableInfo.number}`, 'info');
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
                orders: tableOrders,
                allOrders: orders,
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
                availableTables,
                isLoadingMenu,
                menuError,
                refreshOrders,
            }}
        >
            {children}
        </CartContext.Provider>
    );
};

export const useCart = () => useContext(CartContext);

