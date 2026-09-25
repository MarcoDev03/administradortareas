<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestor de Proyectos & Tablero Kanban')</title>

    <!-- PWA Settings & Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NegocioManager">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- FullCalendar CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.21/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.21/locales/es.global.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Hide scrollbar utility */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* FullCalendar styling */
        .fc {
            --fc-border-color: #e2e8f0;
            --fc-today-bg-color: #eef2ff;
            --fc-button-bg-color: #4f46e5;
            --fc-button-border-color: #4f46e5;
            --fc-button-hover-bg-color: #4338ca;
            --fc-button-hover-border-color: #4338ca;
            --fc-button-active-bg-color: #4338ca;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
        }
        .fc .fc-toolbar-title { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
        .fc .fc-button { text-transform: capitalize; box-shadow: none !important; }
        .fc .fc-daygrid-day-number, .fc .fc-col-header-cell-cushion { color: #475569; }
        .fc-event { cursor: pointer; border: none !important; }

        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.5rem;
                align-items: center;
            }
            .fc .fc-toolbar-chunk {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.25rem;
            }
            .fc .fc-toolbar-title {
                font-size: 0.95rem;
                text-align: center;
            }
            .fc .fc-button {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
            }
            .fc .fc-col-header-cell-cushion {
                font-size: 0.75rem;
            }
            .fc .fc-daygrid-day-number {
                font-size: 0.75rem;
                padding: 2px !important;
            }
            .fc .fc-daygrid-day-frame {
                min-height: 48px !important;
            }
        }
    </style>
</head>
<body class="h-full min-h-screen md:h-full font-sans antialiased text-slate-800 flex flex-col md:flex-row overflow-hidden bg-slate-50">

    <!-- Flash Notifications -->
    <div id="toast-container" class="fixed top-4 left-4 sm:left-auto right-4 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-600 text-white shadow-lg flex items-center justify-between gap-3 text-sm animate-bounce-short pointer-events-auto">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-200 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-600 text-white shadow-lg flex items-center justify-between gap-3 text-sm pointer-events-auto">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-200 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif
    </div>

    @yield('content')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) { window.lucide.createIcons(); }
        });
        
        // Auto-dismiss toast after 4 seconds
        setTimeout(() => {
            const container = document.getElementById('toast-container');
            if (container) {
                const toasts = container.children;
                for (let i = 0; i < toasts.length; i++) {
                    toasts[i].style.transition = 'opacity 0.5s ease';
                    toasts[i].style.opacity = '0';
                    setTimeout(() => toasts[i].remove(), 500);
                }
            }
        }, 4000);
    </script>
    <!-- PWA Helper Registration -->
    <script src="{{ asset('js/pwa.js') }}"></script>
    @stack('scripts')
</body>
</html>
