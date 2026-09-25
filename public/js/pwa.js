/**
 * NegocioManager PWA Helper & Service Worker Registration
 */
(function () {
    'use strict';

    if (!('serviceWorker' in navigator)) {
        return;
    }

    // Deferred install prompt event holder
    window.deferredPWAInstallPrompt = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent immediate default mini-infobar on some mobile browsers
        e.preventDefault();
        window.deferredPWAInstallPrompt = e;
        
        // Show install button if present in UI
        const btn = document.getElementById('pwa-install-btn');
        if (btn) {
            btn.classList.remove('hidden');
            if (window.lucide) { lucide.createIcons(); }
        }

        // Dispatch custom event in case other UI components want to react
        window.dispatchEvent(new CustomEvent('pwa-installable', { detail: e }));
        console.log('[PWA] App is ready for installation.');
    });

    window.addEventListener('appinstalled', () => {
        window.deferredPWAInstallPrompt = null;
        const btn = document.getElementById('pwa-install-btn');
        if (btn) {
            btn.classList.add('hidden');
        }
        console.log('[PWA] App successfully installed!');
    });

    // Register Service Worker on window load
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((registration) => {
                console.log('[PWA] ServiceWorker registered with scope:', registration.scope);

                // Check for updates on every page load
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('[PWA] New version detected and ready.');
                            // New version is installed and waiting
                            newWorker.postMessage({ action: 'skipWaiting' });
                        }
                    });
                });
            })
            .catch((error) => {
                console.warn('[PWA] ServiceWorker registration failed:', error);
            });

        // Ensure page reloads cleanly when new service worker takes over
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (!refreshing) {
                refreshing = true;
                console.log('[PWA] ServiceWorker controller changed. Refreshing for latest assets...');
                // Optional: window.location.reload();
            }
        });
    });

    // Helper global function to trigger PWA installation programmatically
    window.triggerPWAInstall = function () {
        if (!window.deferredPWAInstallPrompt) {
            alert('La aplicación ya está instalada o tu navegador no soporta instalación directa en este momento.');
            return;
        }

        window.deferredPWAInstallPrompt.prompt();
        window.deferredPWAInstallPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('[PWA] User accepted installation prompt');
            } else {
                console.log('[PWA] User dismissed installation prompt');
            }
            window.deferredPWAInstallPrompt = null;
        });
    };
})();
