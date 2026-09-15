/**
 * Dynasty POS - SPA Navigation Engine
 * Memberikan pengalaman Single Page Application (SPA) instan tanpa reload browser,
 * persis seperti React Router pada proyek Caffesence.
 */

(function () {
    'use strict';

    // State & Cache
    window.__spaPageIntervals = window.__spaPageIntervals || [];
    let isNavigating = false;

    // 1. SMART POLYFILL: document.addEventListener & window.setInterval
    // Memastikan skrip halaman dinamis yang menunggu 'alpine:init' atau 'DOMContentLoaded'
    // tetap langsung tereksekusi tanpa menunggu reload halaman.
    const originalAddEventListener = document.addEventListener;
    document.addEventListener = function (type, listener, options) {
        if (type === 'alpine:init' && window.Alpine && window.Alpine.version) {
            try {
                listener();
            } catch (e) {
                console.error('[SPA] Alpine init callback error:', e);
            }
            return;
        }
        if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
            try {
                listener();
            } catch (e) {
                console.error('[SPA] DOMContentLoaded callback error:', e);
            }
            return;
        }
        return originalAddEventListener.call(document, type, listener, options);
    };

    // Tracking interval halaman agar tidak bocor / menumpuk saat navigasi
    const originalSetInterval = window.setInterval;
    window.setInterval = function (fn, delay, ...args) {
        const id = originalSetInterval(fn, delay, ...args);
        // Jangan hapus interval global pesanan masuk
        const fnStr = fn ? fn.toString() : '';
        if (!fnStr.includes('pollGlobalTableOrders')) {
            window.__spaPageIntervals.push(id);
        }
        return id;
    };

    // 2. MICRO PROGRESS BAR (YouTube / GitHub Style)
    function getProgressBar() {
        let bar = document.getElementById('pos-spa-progress-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'pos-spa-progress-bar';
            bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0%;background:linear-gradient(90deg,#f59e0b,#ef4444);z-index:99999;opacity:0;pointer-events:none;transition:width 0.25s ease,opacity 0.2s ease;box-shadow:0 0 10px rgba(245,158,11,0.7);';
            document.body.appendChild(bar);
        }
        return bar;
    }

    let progressTimer = null;
    function startProgressBar() {
        const bar = getProgressBar();
        clearTimeout(progressTimer);
        bar.style.transition = 'width 0.2s ease, opacity 0.1s ease';
        bar.style.opacity = '1';
        bar.style.width = '25%';

        progressTimer = setTimeout(() => {
            bar.style.width = '70%';
        }, 120);
    }

    function finishProgressBar() {
        const bar = getProgressBar();
        clearTimeout(progressTimer);
        bar.style.transition = 'width 0.15s ease, opacity 0.2s ease';
        bar.style.width = '100%';

        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => {
                bar.style.width = '0%';
            }, 200);
        }, 150);
    }

    // 3. LINK FILTER
    function isSpaLink(link) {
        if (!link) return false;
        const rawHref = link.getAttribute('href');
        if (!rawHref || rawHref.startsWith('#') || rawHref.startsWith('javascript:') || rawHref === '') return false;
        if (link.hasAttribute('data-bs-toggle')) return false;
        if (link.target === '_blank' || link.hasAttribute('download')) return false;
        if (link.getAttribute('data-no-spa') !== null) return false;
        if (link.closest('form')) return false;

        let url;
        try {
            url = new URL(link.href, window.location.origin);
        } catch (e) {
            return false;
        }

        // Hanya link dengan origin yang sama
        if (url.origin !== window.location.origin) return false;

        const path = url.pathname;

        // Kecualikan auth, logout, print, download, export
        if (path.includes('/logout') || path.includes('/login')) return false;
        if (path.includes('/print') || path.includes('/export') || path.includes('/download')) return false;

        // Hanya intercept rute admin POS
        if (path.startsWith('/admin')) {
            return true;
        }

        return false;
    }

    // 4. CLEANUP SAAT PINDAH HALAMAN
    function cleanupCurrentPage(container) {
        // Hentikan semua interval timer halaman sebelumnya (jam dinding, polling kasir lokal)
        if (window.__spaPageIntervals && window.__spaPageIntervals.length) {
            window.__spaPageIntervals.forEach(id => clearInterval(id));
            window.__spaPageIntervals = [];
        }

        // Nonaktifkan flag kasir aktif
        window.posSystemActive = false;

        // Hancurkan Alpine tree lama jika ada
        if (container && window.Alpine && typeof window.Alpine.destroyTree === 'function') {
            try {
                window.Alpine.destroyTree(container);
            } catch (e) {
                console.warn('[SPA] Alpine destroyTree notice:', e);
            }
        }

        // Hapus sisa-sisa backdrop modal Bootstrap jika ada yang tertinggal
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // 5. SINKRONISASI AKTIF SIDEBAR & SUBMENU
    function updateActiveSidebar(targetUrl, doc) {
        const newSidebar = doc.querySelector('.pos-sidebar');
        const currentSidebar = document.querySelector('.pos-sidebar');
        if (!newSidebar || !currentSidebar) return;

        // Sync semua link navigasi
        const newLinks = newSidebar.querySelectorAll('a[href]');
        const currentLinks = currentSidebar.querySelectorAll('a[href]');

        currentLinks.forEach(curr => {
            const href = curr.getAttribute('href');
            const matchedNew = Array.from(newLinks).find(n => n.getAttribute('href') === href);
            if (matchedNew) {
                curr.className = matchedNew.className;
            }
        });

        // Buka submenu collapse jika rute baru ada di dalamnya
        const collapses = ['collapseBarang', 'collapsePengaturan'];
        collapses.forEach(id => {
            const newCol = newSidebar.querySelector('#' + id);
            const currCol = currentSidebar.querySelector('#' + id);
            if (newCol && currCol) {
                if (newCol.classList.contains('show')) {
                    currCol.classList.add('show');
                } else if (!newSidebar.querySelector('#' + id + ' .fw-bold')) {
                    // Tutup jika tidak ada item aktif di dalamnya
                    currCol.classList.remove('show');
                }
            }
        });
    }

    // 6. UPDATE DYNAMIC STYLES DI <HEAD>
    function updatePageStyles(doc) {
        // Hapus style dinamis lama
        document.querySelectorAll('[data-pos-dynamic-style="true"]').forEach(el => el.remove());

        // Cari style dari #pos-page-styles di dokumen baru
        const pageStyles = doc.querySelectorAll('#pos-page-styles style, #pos-page-styles link[rel="stylesheet"]');
        pageStyles.forEach(s => {
            const clone = s.cloneNode(true);
            clone.setAttribute('data-pos-dynamic-style', 'true');
            document.head.appendChild(clone);
        });

        // Cek juga style unik di <head> baru yang belum ada di dokumen aktif
        doc.head.querySelectorAll('style').forEach(s => {
            const exists = Array.from(document.head.querySelectorAll('style')).some(existing => existing.textContent.trim() === s.textContent.trim());
            if (!exists) {
                const clone = s.cloneNode(true);
                clone.setAttribute('data-pos-dynamic-style', 'true');
                document.head.appendChild(clone);
            }
        });
    }

    // 7. EKSEKUSI SKRIP HALAMAN BARU
    async function executePageScripts(doc, container) {
        const scriptsToExecute = [];

        // 1. Cek skrip eksternal (CDN/Asset) yang belum dimuat
        const docScripts = doc.querySelectorAll('script');
        for (const s of docScripts) {
            if (s.src) {
                const alreadyLoaded = Array.from(document.querySelectorAll('script')).some(existing => existing.src === s.src);
                if (!alreadyLoaded) {
                    scriptsToExecute.push({ type: 'external', src: s.src });
                }
            }
        }

        // 2. Kumpulkan skrip inline dari dalam viewport baru
        const containerScripts = container.querySelectorAll('script');
        containerScripts.forEach(s => {
            if (s.src) {
                const alreadyLoaded = Array.from(document.querySelectorAll('script')).some(existing => existing.src === s.src);
                if (!alreadyLoaded) {
                    scriptsToExecute.push({ type: 'external', src: s.src });
                }
            } else if (s.textContent.trim()) {
                scriptsToExecute.push({ type: 'inline', content: s.textContent });
            }
        });

        // 3. Kumpulkan skrip dari #pos-page-scripts
        const pageScriptsContainer = doc.getElementById('pos-page-scripts');
        if (pageScriptsContainer) {
            pageScriptsContainer.querySelectorAll('script').forEach(s => {
                if (s.src) {
                    const alreadyLoaded = Array.from(document.querySelectorAll('script')).some(existing => existing.src === s.src);
                    if (!alreadyLoaded) {
                        scriptsToExecute.push({ type: 'external', src: s.src });
                    }
                } else if (s.textContent.trim()) {
                    scriptsToExecute.push({ type: 'inline', content: s.textContent });
                }
            });
        }

        // Jalankan skrip secara sekuensial
        for (const item of scriptsToExecute) {
            if (item.type === 'external') {
                await new Promise((resolve) => {
                    const scriptEl = document.createElement('script');
                    scriptEl.src = item.src;
                    scriptEl.onload = resolve;
                    scriptEl.onerror = resolve; // Jangan blokir jika error
                    document.head.appendChild(scriptEl);
                });
            } else if (item.type === 'inline') {
                try {
                    const scriptEl = document.createElement('script');
                    scriptEl.textContent = item.content;
                    document.body.appendChild(scriptEl);
                    document.body.removeChild(scriptEl);
                } catch (e) {
                    console.error('[SPA] Error executing inline script:', e);
                }
            }
        }
    }

    // 8. REHIDRASI KOMPONEN & UI
    function rehydratePage(container) {
        // Re-inisialisasi pohon Alpine.js pada viewport baru
        if (window.Alpine) {
            try {
                window.Alpine.initTree(container);
            } catch (e) {
                console.warn('[SPA] Alpine initTree warning:', e);
            }
        }

        // Pastikan tombol hamburger sidebar mobile/tablet tetap ada di header
        if (typeof window.ensureSidebarToggleBtn === 'function') {
            window.ensureSidebarToggleBtn();
        }

        // Scroll konten ke atas
        const scrollable = container.querySelector('.pos-main') || container;
        if (scrollable) scrollable.scrollTop = 0;
    }

    // 9. CORE NAVIGATOR FUNCTION
    async function navigateTo(url, pushState = true) {
        if (isNavigating) return;
        isNavigating = true;
        startProgressBar();

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-SPA-Request': 'true'
                }
            });

            // Jika diarahkan ke login atau response bukan 200, fallback ke reload biasa
            if (!response.ok || (response.redirected && response.url.includes('/login'))) {
                window.location.href = response.url || url;
                return;
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newViewport = doc.getElementById('pos-page-viewport');
            const currentViewport = document.getElementById('pos-page-viewport');

            if (!newViewport || !currentViewport) {
                // Dokumen tidak memiliki viewport SPA, fallback ke full page load
                window.location.href = url;
                return;
            }

            // Update title tab browser
            if (doc.title) {
                document.title = doc.title;
            }

            // Update styles halaman
            updatePageStyles(doc);

            // Update status link aktif di sidebar
            updateActiveSidebar(url, doc);

            // History URL update
            if (pushState) {
                window.history.pushState({ spa: true, url: url }, doc.title, url);
            }

            // Cleanup komponen halaman lama
            cleanupCurrentPage(currentViewport);

            // Transisi halus: fade out sedikit (60ms) lalu swap konten
            currentViewport.style.transition = 'opacity 0.08s ease-out';
            currentViewport.style.opacity = '0';

            setTimeout(async () => {
                currentViewport.innerHTML = newViewport.innerHTML;
                currentViewport.style.opacity = '1';

                // Eksekusi skrip halaman baru
                await executePageScripts(doc, currentViewport);

                // Rehidrasi Alpine.js & UI
                rehydratePage(currentViewport);

                // Tutup sidebar mobile jika sedang terbuka
                const sidebar = document.querySelector('.pos-sidebar');
                const backdrop = document.getElementById('posSidebarBackdrop');
                if (sidebar && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    if (backdrop) backdrop.classList.remove('show');
                }

                finishProgressBar();
                isNavigating = false;
            }, 70);

        } catch (err) {
            console.error('[SPA] Navigation failed, falling back:', err);
            finishProgressBar();
            isNavigating = false;
            window.location.href = url;
        }
    }

    // 10. GLOBAL EVENT LISTENERS
    // Intercept semua klik pada tag <a>
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link) return;

        if (isSpaLink(link)) {
            e.preventDefault();
            try {
                const targetUrl = new URL(link.href, window.location.origin);
                if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search) {
                    // Sudah di halaman yang sama, tidak perlu reload
                    return;
                }
            } catch (err) {}
            navigateTo(link.href, true);
        }
    });

    // Browser Back & Forward button handler
    window.addEventListener('popstate', function (e) {
        navigateTo(window.location.href, false);
    });

    // Expose navigator ke window jika dibutuhkan secara manual
    window.posSpaNavigate = navigateTo;

    console.log('[SPA] Dynasty POS Seamless Navigation Engine Ready.');
})();
