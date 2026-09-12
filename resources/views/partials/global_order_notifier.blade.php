{{-- Global Table Order Notifier for all Admin/POS pages (Silent Notification Banner & Badges) --}}
@auth
    @if(!request()->routeIs('admin.pos.*') && !request()->routeIs('admin.pos.index'))
    <style>
        @keyframes slideDownOrderAlert {
            0% { transform: translate(-50%, -100px); opacity: 0; }
            60% { transform: translate(-50%, 10px); opacity: 1; }
            100% { transform: translate(-50%, 0); opacity: 1; }
        }
        .global-order-toast-anim {
            animation: slideDownOrderAlert 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
    </style>

    <!-- Floating Notification Banner saat Pembayaran Meja Masuk di Halaman Lain (Laporan, Stok, dll) Tanpa Suara -->
    <div id="globalOrderToast" class="position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg rounded-4 p-3 d-none border border-2 border-white global-order-toast-anim" style="z-index: 1090; background: #8b211e; color: white; min-width: 320px; max-width: 520px; box-shadow: 0 12px 30px rgba(0,0,0,0.35);">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                <div class="rounded-circle bg-white text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-bell-fill fs-5"></i>
                </div>
                <div class="text-truncate">
                    <div class="fw-bold fs-6 d-flex align-items-center gap-1.5" id="globalToastTitle"><i class="bi bi-credit-card"></i> <span>Pesanan Meja Lunas!</span></div>
                    <div class="small opacity-90 text-truncate" id="globalToastBody">Memuat rincian menu...</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-light text-danger fw-bold rounded-pill px-3 shadow-xs" title="Buka Halaman Kasir">
                    Buka Kasir
                </a>
                <button type="button" class="btn-close btn-close-white" style="font-size: 0.75rem;" onclick="document.getElementById('globalOrderToast').classList.add('d-none')" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // Jangan jalankan polling jika sedang berada di halaman kasir POS utama
        if (window.posSystemActive || window.location.pathname.includes('/admin/pos')) {
            return;
        }

        function getNotifiedOrders() {
            try {
                const s = sessionStorage.getItem('global_notified_orders');
                return s ? new Set(JSON.parse(s)) : new Set();
            } catch (e) {
                return new Set();
            }
        }

        function markOrderNotified(orderId) {
            try {
                const s = getNotifiedOrders();
                s.add(orderId);
                sessionStorage.setItem('global_notified_orders', JSON.stringify(Array.from(s)));
            } catch (e) {}
        }

        function showGlobalToast(order) {
            const toastEl = document.getElementById('globalOrderToast');
            const titleEl = document.getElementById('globalToastTitle');
            const bodyEl = document.getElementById('globalToastBody');
            if (!toastEl) return;

            let itemsText = '';
            if (order.items && order.items.length > 0) {
                itemsText = order.items.map(function(item) {
                    return item.jumlah + 'x ' + item.nama_produk;
                }).join(', ');
            }

            if (titleEl) titleEl.innerHTML = `<i class="bi bi-credit-card me-1"></i> Meja ${order.meja_nomor} (${order.nama_pelanggan || 'Pelanggan'}) Lunas!`;
            if (bodyEl) bodyEl.textContent = itemsText || 'Pesanan siap dimasak di dapur';

            toastEl.classList.remove('d-none');
        }

        function updateSidebarBadges(count) {
            const badges = document.querySelectorAll('.global-table-order-badge');
            badges.forEach(function(b) {
                if (count > 0) {
                    b.textContent = count;
                    b.classList.remove('d-none');
                } else {
                    b.classList.add('d-none');
                }
            });
        }

        async function pollGlobalTableOrders() {
            try {
                const res = await fetch('{{ route("admin.pos.pesanan_aktif") }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    const json = await res.json();
                    const orders = json.data || [];
                    updateSidebarBadges(orders.length);

                    const notified = getNotifiedOrders();
                    const unannouncedPaid = orders.filter(function(o) {
                        const isPaid = (o.status_pembayaran === 'dibayar' || o.status === 'diproses');
                        return isPaid && !notified.has(o.id);
                    });

                    if (unannouncedPaid.length > 0) {
                        const targetOrder = unannouncedPaid[0];
                        unannouncedPaid.forEach(function(o) {
                            markOrderNotified(o.id);
                        });

                        // Tampilkan notifikasi visual toast tanpa mengeluarkan suara saat berada di halaman selain kasir
                        showGlobalToast(targetOrder);
                    }
                }
            } catch (e) {
                // Background poll silent fallback
            }
        }

        // Jalankan saat halaman siap & polling berkala tiap 5 detik
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                pollGlobalTableOrders();
                setInterval(pollGlobalTableOrders, 5000);
            });
        } else {
            pollGlobalTableOrders();
            setInterval(pollGlobalTableOrders, 5000);
        }
    })();
    </script>
    @endif
@endauth
