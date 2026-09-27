/**
 * NegocioManager PWA Helper & Service Worker Registration
 */

(function () {
    'use strict';

    if (!('serviceWorker' in navigator)) {
        return;
    }

    // Evento de instalación pendiente
    window.deferredPWAInstallPrompt = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        // Evita que el navegador muestre automáticamente su aviso
        e.preventDefault();

        // Guardamos el evento para utilizarlo posteriormente
        window.deferredPWAInstallPrompt = e;

        // Mostrar cualquier botón de instalación existente
        const btn = document.getElementById('pwa-install-btn');

        if (btn) {
            btn.classList.remove('hidden');

            if (window.lucide) {
                lucide.createIcons();
            }
        }

        // Avisar a otros componentes de que la PWA está disponible
        window.dispatchEvent(
            new CustomEvent('pwa-installable', {
                detail: e
            })
        );

        console.log('[PWA] App is ready for installation.');
    });

    // Cuando la aplicación se instala
    window.addEventListener('appinstalled', () => {

        window.deferredPWAInstallPrompt = null;

        const btn = document.getElementById('pwa-install-btn');

        if (btn) {
            btn.classList.add('hidden');
        }

        // Ocultar también la tarjeta del login
        const installCard = document.getElementById('pwa-install-card');

        if (installCard) {
            installCard.classList.add('hidden');
        }

        console.log('[PWA] App successfully installed!');
    });

    // Registrar Service Worker
    window.addEventListener('load', () => {

        navigator.serviceWorker.register('/sw.js', {
            scope: '/'
        })
        .then((registration) => {

            console.log(
                '[PWA] ServiceWorker registered with scope:',
                registration.scope
            );

            // Buscar actualizaciones
            registration.addEventListener('updatefound', () => {

                const newWorker = registration.installing;

                if (!newWorker) {
                    return;
                }

                newWorker.addEventListener('statechange', () => {

                    if (
                        newWorker.state === 'installed' &&
                        navigator.serviceWorker.controller
                    ) {
                        console.log(
                            '[PWA] New version detected and ready.'
                        );

                        newWorker.postMessage({
                            action: 'skipWaiting'
                        });
                    }
                });
            });

        })
        .catch((error) => {

            console.warn(
                '[PWA] ServiceWorker registration failed:',
                error
            );

        });

        // Controlador del Service Worker
        let refreshing = false;

        navigator.serviceWorker.addEventListener(
            'controllerchange',
            () => {

                if (!refreshing) {

                    refreshing = true;

                    console.log(
                        '[PWA] ServiceWorker controller changed.'
                    );

                    // Si quieres recargar automáticamente:
                    // window.location.reload();
                }
            }
        );
    });

    // Función global para instalar la PWA
    window.triggerPWAInstall = function () {

        if (!window.deferredPWAInstallPrompt) {

            alert(
                'La aplicación ya está instalada o tu navegador no soporta instalación directa en este momento.'
            );

            return;
        }

        window.deferredPWAInstallPrompt.prompt();

        window.deferredPWAInstallPrompt.userChoice
            .then((choiceResult) => {

                if (choiceResult.outcome === 'accepted') {

                    console.log(
                        '[PWA] User accepted installation prompt'
                    );

                } else {

                    console.log(
                        '[PWA] User dismissed installation prompt'
                    );
                }

                window.deferredPWAInstallPrompt = null;
            });
    };

})();