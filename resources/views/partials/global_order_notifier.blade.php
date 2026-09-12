{{-- Global Table Order Notifier & Voice Announcer for all Admin/POS pages --}}
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

    <!-- Floating Notification Banner saat Pembayaran Meja Masuk di Halaman Lain (Laporan, Stok, dll) -->
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
        // Jangan jalankan polling duplikat jika sedang berada di halaman kasir POS utama
        if (window.posSystemActive || window.location.pathname.includes('/admin/pos')) {
            return;
        }

        let globalAudioCtx = null;
        let globalUserInteracted = false;
        let globalPendingVoiceOrder = null;

        function unlockGlobalAudio() {
            globalUserInteracted = true;
            if (globalAudioCtx && globalAudioCtx.state === 'suspended') {
                globalAudioCtx.resume();
            }
            if ('speechSynthesis' in window) {
                window.speechSynthesis.resume();
            }
            if (globalPendingVoiceOrder) {
                const order = globalPendingVoiceOrder;
                globalPendingVoiceOrder = null;
                speakGlobalOrder(order);
            }
        }
        ['click', 'touchstart', 'keydown'].forEach(function(evt) {
            window.addEventListener(evt, unlockGlobalAudio, { passive: true });
        });

        function playGlobalChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                if (!globalAudioCtx) globalAudioCtx = new AudioCtx();
                if (globalAudioCtx.state === 'suspended') globalAudioCtx.resume();
                const now = globalAudioCtx.currentTime;

                const osc1 = globalAudioCtx.createOscillator();
                const gain1 = globalAudioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(globalAudioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);

                const osc2 = globalAudioCtx.createOscillator();
                const gain2 = globalAudioCtx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.15); // A5
                gain2.gain.setValueAtTime(0.35, now + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc2.connect(gain2);
                gain2.connect(globalAudioCtx.destination);
                osc2.start(now + 0.15);
                osc2.stop(now + 0.6);
            } catch (e) {
                console.log('Global chime error:', e);
            }
        }

        function speakGlobalOrder(order) {
            if (!order) return;
            playGlobalChime();

            if (!('speechSynthesis' in window)) return;

            const meja = order.meja_nomor || order.meja_id || '';
            const nama = order.nama_pelanggan || 'Pelanggan';

            let itemsText = '';
            if (order.items && order.items.length > 0) {
                itemsText = order.items.map(function(item) {
                    return item.jumlah + ' ' + item.nama_produk;
                }).join(', ');
            }

            const isPaid = (order.status_pembayaran === 'dibayar' || order.status === 'diproses');
            let speechText = isPaid
                ? `Pesanan sudah dibayar dari Meja ${meja}, atas nama ${nama}`
                : `Pesanan baru dari Meja ${meja}, atas nama ${nama}`;

            if (itemsText) {
                speechText += `, memesan: ${itemsText}.`;
            }

            setTimeout(function() {
                try {
                    window.speechSynthesis.resume();
                    window.speechSynthesis.cancel();

                    const utterance = new SpeechSynthesisUtterance(speechText);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.95;
                    utterance.pitch = 1.05;

                    const voices = window.speechSynthesis.getVoices();
                    const idVoice = voices.find(function(v) {
                        return (v.lang === 'id-ID' || v.lang.startsWith('id') || v.lang.toLowerCase().includes('indonesia'));
                    });
                    if (idVoice) {
                        utterance.voice = idVoice;
                    }

                    utterance.onerror = function(e) {
                        if (e.error === 'not-allowed') {
                            globalPendingVoiceOrder = order;
                        }
                    };

                    window.speechSynthesis.speak(utterance);
                } catch (err) {
                    console.log('Global Speech synthesis error:', err);
                }
            }, 400);
        }

        function getAnnouncedOrders() {
            try {
                const s = sessionStorage.getItem('pos_announced_orders');
                return s ? new Set(JSON.parse(s)) : new Set();
            } catch (e) {
                return new Set();
            }
        }

        function markOrderAnnounced(orderId) {
            try {
                const s = getAnnouncedOrders();
                s.add(orderId);
                sessionStorage.setItem('pos_announced_orders', JSON.stringify(Array.from(s)));
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

                    const announced = getAnnouncedOrders();
                    const unannouncedPaid = orders.filter(function(o) {
                        const isPaid = (o.status_pembayaran === 'dibayar' || o.status === 'diproses');
                        return isPaid && !announced.has(o.id);
                    });

                    if (unannouncedPaid.length > 0) {
                        const targetOrder = unannouncedPaid[0];
                        unannouncedPaid.forEach(function(o) {
                            markOrderAnnounced(o.id);
                        });

                        showGlobalToast(targetOrder);

                        if (globalUserInteracted) {
                            speakGlobalOrder(targetOrder);
                        } else {
                            globalPendingVoiceOrder = targetOrder;
                            speakGlobalOrder(targetOrder);
                        }
                    }
                }
            } catch (e) {
                // Background poll silent fallback
            }
        }

        // Jalankan saat halaman siap & polling berkala tiap 4 detik
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                pollGlobalTableOrders();
                setInterval(pollGlobalTableOrders, 4000);
            });
        } else {
            pollGlobalTableOrders();
            setInterval(pollGlobalTableOrders, 4000);
        }
    })();
    </script>
    @endif
@endauth
