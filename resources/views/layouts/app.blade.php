<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestor de Proyectos & Tablero Kanban')</title>

    <!-- Theme initialization (Prevent flash of unstyled theme) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- PWA Settings & Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/iconoround.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('icons/icon-96x96.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    },
                    boxShadow: {
                        'soft': '0 2px 10px -2px rgba(15, 23, 42, 0.05), 0 1px 3px 0 rgba(15, 23, 42, 0.03)',
                        'card': '0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04)',
                        'card-dark': '0 4px 20px -2px rgba(0, 0, 0, 0.35), 0 2px 6px -1px rgba(0, 0, 0, 0.2)',
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- FullCalendar CDN -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.21/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.21/locales/es.global.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Hide scrollbar utility */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Safe area helpers */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 0px); }

        /* Collapsible Sidebar Styles & Permanent Dark Theme */
        #sidebar {
            background-color: #0b0f19 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #cbd5e1 !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, margin-left;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        #sidebar .border-b,
        #sidebar .border-t,
        #sidebar .border-r {
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        @media (min-width: 768px) {
            #sidebar.sidebar-collapsed {
                margin-left: -18rem !important;
            }
        }

        /* FullCalendar styling */
        .fc {
            --fc-border-color: #e2e8f0;
            --fc-today-bg-color: #eff6ff;
            --fc-button-bg-color: #1e40af;
            --fc-button-border-color: #1e40af;
            --fc-button-hover-bg-color: #1d4ed8;
            --fc-button-hover-border-color: #1d4ed8;
            --fc-button-active-bg-color: #1d4ed8;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.85rem;
        }
        .fc .fc-toolbar-title { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
        .fc .fc-button { text-transform: capitalize; box-shadow: none !important; border-radius: 0.5rem; font-weight: 600; }
        .fc .fc-daygrid-day-number, .fc .fc-col-header-cell-cushion { color: #475569; }
        .fc-event { cursor: pointer; border: none !important; border-radius: 4px; padding: 2px 4px; font-weight: 500; }

        /* FullCalendar Dark Mode */
        .dark .fc {
            --fc-border-color: #334155;
            --fc-today-bg-color: rgba(30, 64, 175, 0.18);
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #1e293b;
            --fc-list-event-hover-bg-color: #1e293b;
        }
        .dark .fc .fc-toolbar-title { color: #f8fafc; }
        .dark .fc .fc-daygrid-day-number,
        .dark .fc .fc-col-header-cell-cushion { color: #94a3b8; }
        .dark .fc .fc-day-other .fc-daygrid-day-number { color: #475569; }
        .dark .fc-theme-standard td, 
        .dark .fc-theme-standard th { border-color: #334155 !important; }
        .dark .fc .fc-day-other { background-color: rgba(15, 23, 42, 0.35); }
        .dark .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .dark .fc .fc-button-primary:not(:disabled):active {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

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

        /* =========================================================================
           THEME UNIFICATION: modoclaro.png & modooscuro.jpeg
           ========================================================================= */
        /* Canvas workspace backgrounds */
        body {
            background-color: #B3B3B3;
        }
        .dark body {
            background-color: #0b0f19;
        }

        /* Dark Mode: elevated dark slate surfaces and crisp, clearly noticeable borders */
        .dark .bg-slate-900,
        .dark .dark\:bg-slate-900 {
            background-color: #121826 !important;
        }
        .dark .bg-slate-950,
        .dark .dark\:bg-slate-950 {
            background-color: #0b0f19 !important;
        }
        .dark .border-slate-800,
        .dark [class*="dark:border-slate-800"],
        .dark [class*="dark:border-slate-700"] {
            border-color: rgba(255, 255, 255, 0.28) !important;
        }

        @media (min-width: 1024px) {
            .dark #kpi-stats-grid > div,
            .dark .lg\:col-span-8 > div,
            .dark .lg\:col-span-4 > div,
            .dark [data-kanban-col],
            .dark .kanban-card,
            .dark .dashboard-card,
            .dark .stats-card {
                border-color: rgba(255, 255, 255, 0.28) !important;
            }
        }

        /* Header / View counter badges: white circle with dark numbers in both modes */
        .tab-counter-badge,
        .header-counter-circle {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid rgba(15, 23, 42, 0.12) !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08) !important;
        }
        .dark .tab-counter-badge,
        .dark .header-counter-circle {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35) !important;
        }
    </style>
</head>
<body class="h-full min-h-screen md:h-full font-sans antialiased text-slate-800 dark:text-slate-100 flex flex-col md:flex-row overflow-hidden bg-[#B3B3B3] dark:bg-[#0b0f19] transition-colors duration-200">

    <!-- Flash Notifications -->
    <div id="toast-container" class="fixed top-4 left-4 sm:left-auto right-4 z-50 flex flex-col gap-2 max-w-sm pointer-events-none">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-600 text-white shadow-xl flex items-center justify-between gap-3 text-sm animate-bounce-short pointer-events-auto border border-emerald-500/30">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-200 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-600 text-white shadow-xl flex items-center justify-between gap-3 text-sm pointer-events-auto border border-red-500/30">
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
        // Dark / Light Theme Toggle Function
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcons();
            // Re-render FullCalendar if present
            window.dispatchEvent(new Event('theme-toggled'));
        }

        function updateThemeIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('.theme-icon-sun').forEach(el => {
                el.classList.toggle('hidden', !isDark);
            });
            document.querySelectorAll('.theme-icon-moon').forEach(el => {
                el.classList.toggle('hidden', isDark);
            });
            if (window.lucide) { window.lucide.createIcons(); }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcons();
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
