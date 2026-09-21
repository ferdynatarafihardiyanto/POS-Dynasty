const DEFAULT_POLL_INTERVAL_MS = 45000;

function normalizeAssetFilename(urlOrPath) {
    if (!urlOrPath || typeof urlOrPath !== 'string') return null;

    try {
        // Hapus query parameters dan hash
        const clean = urlOrPath.split('?')[0].split('#')[0];
        const parts = clean.split('/');
        const filename = parts[parts.length - 1];

        if (filename && (filename.endsWith('.js') || filename.endsWith('.css'))) {
            return filename;
        }
    } catch {
        // ignore
    }
    return null;
}

function getCurrentBrowserSignature() {
    const assets = new Set();

    // 1. Ekstrak dari elemen script di DOM — hanya app-*.js entry point
    try {
        const scripts = document.querySelectorAll('script[src]');
        scripts.forEach((script) => {
            const src = script.getAttribute('src');
            if (src && src.includes('/build/assets/')) {
                const norm = normalizeAssetFilename(src);
                if (norm && norm.startsWith('app-')) {
                    assets.add(norm);
                }
            }
        });
    } catch {
        // ignore
    }

    // 2. Ekstrak dari elemen link stylesheet di DOM — hanya app-*.css entry point
    try {
        const links = document.querySelectorAll('link[rel="stylesheet"][href]');
        links.forEach((link) => {
            const href = link.getAttribute('href');
            if (href && href.includes('/build/assets/')) {
                const norm = normalizeAssetFilename(href);
                if (norm && norm.startsWith('app-')) {
                    assets.add(norm);
                }
            }
        });
    } catch {
        // ignore
    }

    // 3. Fallback: gunakan import.meta.url jika DOM belum menemukan script entry
    if (assets.size === 0) {
        try {
            if (typeof import.meta !== 'undefined' && import.meta.url) {
                const currentJs = normalizeAssetFilename(import.meta.url);
                if (currentJs && currentJs.startsWith('app-')) {
                    assets.add(currentJs);
                }
            }
        } catch {
            // ignore
        }
    }

    if (assets.size === 0) return null;
    return Array.from(assets).sort().join('|');
}


function extractServerManifestSignature(manifest) {
    if (!manifest || typeof manifest !== 'object') return null;

    // Kunci entry-point customer yang digunakan di vite.config.js dan app.blade.php
    const ENTRY_KEYS = ['resources/js/app.jsx', 'resources/css/app.css'];

    try {
        const assets = new Set();

        for (const key of ENTRY_KEYS) {
            const val = manifest[key];
            if (val && val.file) {
                const norm = normalizeAssetFilename(val.file);
                if (norm) {
                    assets.add(norm);
                }
            }
        }

        if (assets.size === 0) return null;
        return Array.from(assets).sort().join('|');
    } catch {
        return null;
    }
}


function extractHtmlAssetSignature(htmlText) {
    if (!htmlText || typeof htmlText !== 'string') return null;

    try {
        const assets = new Set();
        // Hanya cocokkan asset entry-point: /build/assets/app-[hash].js atau .css
        const regex = /\/build\/assets\/app-[^"'\s>]+\.(?:js|css)/g;
        let match;
        while ((match = regex.exec(htmlText)) !== null) {
            const norm = normalizeAssetFilename(match[0]);
            if (norm) {
                assets.add(norm);
            }
        }

        if (assets.size === 0) return null;
        return Array.from(assets).sort().join('|');
    } catch {
        return null;
    }
}


function isPaymentOrCheckoutActive() {
    try {
        // 1. Cek apakah ada iframe atau kontainer Midtrans Snap aktif
        const snapIframe = document.querySelector(
            '#snap-midtrans, #snap-container, iframe[src*="midtrans"], iframe[id*="snap"], .snap-popup, [id*="snap-"]'
        );
        if (snapIframe) {
            return true;
        }

        // 2. Cek apakah object window.snap sedang aktif / terbuka
        if (window.snap && (window.snap._is_open || window.snap.isOpen || window.snap.active)) {
            return true;
        }

        // 3. Cek apakah Modal Pembayaran CustomerPayModal aktif di layar (z-60 atau judul pembayaran)
        const paymentModalActive = document.querySelector('[class*="z-60"]');
        if (paymentModalActive) {
            return true;
        }

        // 4. Cek teks konfirmasi pembayaran / teks khas modal pembayaran
        if (document.body && document.body.innerText) {
            const bodyText = document.body.innerText;
            if (bodyText.includes('Pembayaran Midtrans Snap') || bodyText.includes('Metode Resmi Midtrans')) {
                return true;
            }
        }

        // 5. Cek apakah user sedang mengetik di input / textarea (mencegah reload saat mengisi data meja/nama/catatan)
        const activeElem = document.activeElement;
        if (activeElem && (activeElem.tagName === 'INPUT' || activeElem.tagName === 'TEXTAREA' || activeElem.isContentEditable)) {
            return true;
        }

        return false;
    } catch (e) {
        // Jika terjadi error saat inspeksi DOM, anggap aman untuk tidak memblokir selamanya
        return false;
    }
}


export function initAutoUpdater() {
    // 1. Jangan jalankan di development mode agar tidak mengganggu Vite HMR
    if (!import.meta.env.PROD) {
        return;
    }

    // 2. Tentukan CURRENT SIGNATURE langsung dari asset yang sedang aktif di browser
    let currentSignature = getCurrentBrowserSignature();
    let isChecking = false;

    // Fungsi untuk mengambil signature versi terbaru dari server
    async function fetchServerSignature() {
        try {
            // Coba ambil dari /build/manifest.json terlebih dahulu (sangat ringan, ~600 byte)
            const manifestUrl = `/build/manifest.json?_t=${Date.now()}`;
            const res = await fetch(manifestUrl, {
                cache: 'no-store',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                }
            });

            if (res.ok) {
                const manifest = await res.json();
                const signature = extractServerManifestSignature(manifest);
                if (signature) return signature;
            }
        } catch {
            // Abaikan dan lanjut ke fallback
        }

        // Fallback: Ambil dari HTML dokumen halaman saat ini jika manifest langsung tidak merespon
        try {
            const pageUrl = `${window.location.pathname}?_update_check=${Date.now()}`;
            const pageRes = await fetch(pageUrl, {
                cache: 'no-store',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                }
            });

            if (pageRes.ok) {
                const htmlText = await pageRes.text();
                const signature = extractHtmlAssetSignature(htmlText);
                if (signature) return signature;
            }
        } catch {
            // Jaringan mungkin offline sementara
        }

        return null;
    }

    // Fungsi eksekusi pengecekan berkala
    async function checkVersion() {
        if (isChecking) return;
        if (typeof navigator !== 'undefined' && !navigator.onLine) return;

        isChecking = true;
        try {
            // Pastikan currentSignature sudah terisi dari DOM browser
            if (!currentSignature) {
                currentSignature = getCurrentBrowserSignature();
                if (!currentSignature) {
                    return;
                }
            }

            const latestSignature = await fetchServerSignature();

            // Jika latestSignature berhasil didapatkan dan BERBEDA dari yang dipakai browser saat ini
            if (latestSignature && latestSignature !== currentSignature) {
                // Pastikan user tidak sedang melakukan pembayaran / checkout / mengisi form
                if (isPaymentOrCheckoutActive()) {
                    // Tunda reload ke siklus berikutnya saat pembayaran selesai
                    return;
                }

                // Proteksi Infinite Loop: Jika browser baru saja reload untuk signature ini tapi HTML tetap lama
                const lastReloadAttempt = sessionStorage.getItem('pos_last_auto_reload_sig');
                if (lastReloadAttempt === latestSignature) {
                    return;
                }

                // Catat signature yang memicu reload untuk mencegah loop
                sessionStorage.setItem('pos_last_auto_reload_sig', latestSignature);

                // Lakukan reload otomatis
                window.location.reload();
            } else if (latestSignature && latestSignature === currentSignature) {
                // Versi browser sudah sesuai dengan server, bersihkan session marker jika ada
                const lastReload = sessionStorage.getItem('pos_last_auto_reload_sig');
                if (lastReload) {
                    sessionStorage.removeItem('pos_last_auto_reload_sig');
                }
            }
        } catch {
            // Tangani error diam-diam tanpa memunculkan popup
        } finally {
            isChecking = false;
        }
    }

    // Jalankan pengecekan awal setelah halaman selesai dimuat (delay 3 detik)
    setTimeout(() => {
        checkVersion();
    }, 3000);

    // Pasang interval polling berkala dengan random jitter (40-50 detik)
    const jitter = Math.floor(Math.random() * 10000) - 5000;
    const intervalTime = Math.max(30000, DEFAULT_POLL_INTERVAL_MS + jitter);

    const intervalId = setInterval(() => {
        checkVersion();
    }, intervalTime);

    // Cek juga saat tab browser kembali aktif / focus
    window.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            checkVersion();
        }
    });

    return () => clearInterval(intervalId);
}
