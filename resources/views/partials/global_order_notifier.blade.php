{{-- Global Table Order & Payment Proof Notifier for all Admin/POS pages --}}
@auth
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

    <!-- Floating Notification Banner saat Bukti Pembayaran / Pesanan Masuk saat di Halaman Admin Lain -->
    <div id="globalOrderToast" class="position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg rounded-4 p-3 d-none border border-2 border-white global-order-toast-anim" style="z-index: 1090; background: #8b211e; color: white; min-width: 340px; max-width: 560px; box-shadow: 0 12px 30px rgba(0,0,0,0.35);">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                <div id="globalToastThumbWrap" class="d-none flex-shrink-0">
                    <img id="globalToastThumb" src="" alt="Bukti" class="rounded-3 border border-white bg-white object-fit-cover" style="width: 44px; height: 44px;">
                </div>
                <div id="globalToastIconWrap" class="rounded-circle bg-white text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-bell-fill fs-5"></i>
                </div>
                <div class="text-truncate">
                    <div class="fw-bold fs-6 d-flex align-items-center gap-1.5" id="globalToastTitle"><i class="bi bi-credit-card"></i> <span>Pesanan Meja Masuk!</span></div>
                    <div class="small opacity-90 text-truncate" id="globalToastBody">Memuat rincian menu...</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                <a id="globalToastActionBtn" href="{{ route('admin.pos.index') }}" onclick="document.getElementById('globalOrderToast').classList.add('d-none')" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-xs text-nowrap" title="Buka Halaman Kasir">
                    Buka Kasir
                </a>
                <button type="button" class="btn-close btn-close-white" style="font-size: 0.75rem;" onclick="document.getElementById('globalOrderToast').classList.add('d-none')" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        if (window.__globalOrderNotifierStarted) return;
        window.__globalOrderNotifierStarted = true;

        function isOnPosPage() {
            return Boolean(window.posSystemActive) || window.location.pathname.includes('/admin/pos');
        }

        function getStoredSet(key) {
            try {
                const s = sessionStorage.getItem(key);
                return s ? new Set(JSON.parse(s)) : new Set();
            } catch (e) {
                return new Set();
            }
        }

        function addToStoredSet(key, value) {
            try {
                const s = getStoredSet(key);
                s.add(value);
                sessionStorage.setItem(key, JSON.stringify(Array.from(s)));
            } catch (e) {}
        }

        function playGlobalChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume().catch(function() {});
                }
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(784, now);
                gain.gain.setValueAtTime(0.001, now);
                gain.gain.linearRampToValueAtTime(0.35, now + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.45);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.45);
            } catch (e) {}
        }

        function showGlobalToast(order, mode) {
            const toastEl = document.getElementById('globalOrderToast');
            const titleEl = document.getElementById('globalToastTitle');
            const bodyEl = document.getElementById('globalToastBody');
            const actionBtn = document.getElementById('globalToastActionBtn');
            const thumbWrap = document.getElementById('globalToastThumbWrap');
            const thumbImg = document.getElementById('globalToastThumb');
            const iconWrap = document.getElementById('globalToastIconWrap');
            if (!toastEl) return;

            let itemsText = '';
            if (order.items && order.items.length > 0) {
                itemsText = order.items.map(function(item) {
                    return item.jumlah + 'x ' + item.nama_produk;
                }).join(', ');
            }

            if (mode === 'proof') {
                const totalFormatted = 'Rp ' + Math.round(order.total_harga || 0).toLocaleString('id-ID');
                if (titleEl) titleEl.innerHTML = `<i class="bi bi-qr-code-scan text-warning me-1"></i> Bukti Bayar Meja ${order.meja_nomor} Masuk!`;
                if (bodyEl) bodyEl.textContent = `${order.nama_pelanggan || 'Pelanggan'} • ${totalFormatted}${itemsText ? ' • ' + itemsText : ''}`;
                if (actionBtn) {
                    actionBtn.href = '{{ route("admin.pos.index") }}?konfirmasi=' + order.id;
                    actionBtn.textContent = 'Cek Bukti & Konfirmasi';
                }
                const proofUrl = order.admin_bukti_url || order.bukti_pembayaran_url;
                if (proofUrl && thumbImg && thumbWrap && iconWrap) {
                    thumbImg.src = proofUrl;
                    thumbWrap.classList.remove('d-none');
                    iconWrap.classList.add('d-none');
                }
                playGlobalChime();
            } else {
                if (titleEl) titleEl.innerHTML = `<i class="bi bi-credit-card me-1"></i> Meja ${order.meja_nomor} (${order.nama_pelanggan || 'Pelanggan'}) Lunas!`;
                if (bodyEl) bodyEl.textContent = itemsText || 'Pesanan siap dimasak di dapur';
                if (actionBtn) {
                    actionBtn.href = '{{ route("admin.pos.index") }}';
                    actionBtn.textContent = 'Buka Kasir';
                }
                if (thumbWrap && iconWrap) {
                    thumbWrap.classList.add('d-none');
                    iconWrap.classList.remove('d-none');
                }
            }

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
            const toastEl = document.getElementById('globalOrderToast');
            if (isOnPosPage()) {
                if (toastEl) toastEl.classList.add('d-none');
                return;
            }

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

                    // 1. Cek bukti transfer/QRIS baru masuk (menunggu_konfirmasi)
                    const notifiedProofs = getStoredSet('global_notified_proofs');
                    const pendingProofs = orders.filter(function(o) {
                        if (o.status !== 'menunggu_konfirmasi' || o.status_pembayaran === 'dibayar') return false;
                        const key = o.id + ':' + (o.waktu_upload_bukti_iso || o.bukti_pembayaran || 'pending');
                        return !notifiedProofs.has(key);
                    });

                    if (pendingProofs.length > 0) {
                        const targetProof = pendingProofs[0];
                        pendingProofs.forEach(function(o) {
                            const key = o.id + ':' + (o.waktu_upload_bukti_iso || o.bukti_pembayaran || 'pending');
                            addToStoredSet('global_notified_proofs', key);
                        });
                        showGlobalToast(targetProof, 'proof');
                        return;
                    }

                    // 2. Cek pesanan baru lunas
                    const notified = getStoredSet('global_notified_orders');
                    const unannouncedPaid = orders.filter(function(o) {
                        const isPaid = (o.status_pembayaran === 'dibayar' || o.status === 'diproses');
                        return isPaid && !notified.has(o.id);
                    });

                    if (unannouncedPaid.length > 0) {
                        const targetOrder = unannouncedPaid[0];
                        unannouncedPaid.forEach(function(o) {
                            addToStoredSet('global_notified_orders', o.id);
                        });
                        showGlobalToast(targetOrder, 'paid');
                    }
                }
            } catch (e) {
                // Background poll silent fallback
            }
        }

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
@endauth
