<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Descargar e Instalar Administrador de Proyectos - NegocioManager</title>

    <!-- PWA Settings & Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NegocioManager">
    <link rel="icon" type="image/webp" href="{{ asset('icons/icono-agenda.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icono-agenda.webp') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.95; transform: scale(1.02); }
        }
        .animate-pulse-subtle {
            animation: pulse-subtle 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-full bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white flex flex-col">

    <!-- Top Navigation Bar -->
    <header class="w-full border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <a href="{{ route('login') }}" class="flex items-center gap-3 group transition">
                <div class="w-9 h-9 rounded-xl overflow-hidden shadow-md shadow-indigo-600/20 border border-slate-700/80 bg-slate-800 p-0.5 group-hover:border-indigo-500 transition">
                    <img src="{{ asset('icons/icono-agenda.webp') }}" alt="NegocioManager Logo" class="w-full h-full object-cover rounded-lg" onerror="this.src='{{ asset('icons/icono-agenda.jpg') }}'">
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm sm:text-base text-white tracking-tight group-hover:text-indigo-400 transition">Gestor de Proyectos</span>
                    <span class="text-[10px] text-slate-400 hidden sm:inline">Gestor de Proyectos & Kanban</span>
                </div>
            </a>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('login') }}" class="py-2 px-3.5 sm:px-4 text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <span>Ir a la aplicación</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 py-8 sm:py-12 space-y-10 sm:space-y-14">

        <!-- Header Section -->
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-sm">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-400"></i>
                <span> Gestor de Proyectos (PWA) Oficial</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                Descargar e Instalar Administrador de Proyectos
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                Instala <strong class="text-slate-200">Gestor de Proyectos</strong> directamente en tu dispositivo sin descargas pesadas desde tiendas de apps. Disfruta de una experiencia en pantalla completa, acceso rápido y máxima fluidez.
            </p>
        </div>

        <!-- Central Main Card -->
        <div class="bg-slate-800/70 backdrop-blur-2xl border border-slate-700/80 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-black/50 relative overflow-hidden">
            <!-- Background Glow -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6 sm:gap-8">
                <!-- App Icon -->
                <div class="shrink-0 flex flex-col items-center">
                    <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl overflow-hidden shadow-2xl shadow-indigo-600/30 border-2 border-indigo-500/40 bg-slate-900 p-1">
                        <img src="{{ asset('icons/icono-agenda.webp') }}" alt="NegocioManager Icon" class="w-full h-full object-cover rounded-2xl" onerror="this.src='{{ asset('icons/icono-agenda.jpg') }}'">
                    </div>
                    <span class="mt-2 text-[11px] font-medium text-slate-400 flex items-center gap-1">
                        <i data-lucide="check-circle" class="w-3 h-3 text-emerald-400"></i>
                        <span>Versión Oficial</span>
                    </span>
                </div>

                <!-- App Details -->
                <div class="flex-1 text-center md:text-left space-y-4 min-w-0">
                    <div>
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                            <h2 class="text-xl sm:text-3xl font-bold text-white tracking-tight">Gestor de Proyectos</h2>
                            <span class="text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2 py-0.5 rounded-full uppercase tracking-wider">PWA</span>
                        </div>
                        <p class="text-xs sm:text-sm text-indigo-300/80 font-medium mt-0.5">Gestor de Proyectos, Módulos, Tareas y Tablero Kanban</p>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                        Accede a tus proyectos, asigna tareas a tu equipo, realiza seguimiento de incidencias y visualiza tus calendarios de trabajo en una ventana independiente, sin barras del navegador.
                    </p>

                    <!-- Features Chips (Real features only) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1 text-left">
                        <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-700/60 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-slate-200 truncate">Instalable</p>
                                <p class="text-[9px] text-slate-400 truncate">Android, iOS, PC, Mac</p>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-700/60 flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                                <i data-lucide="zap" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-slate-200 truncate">Ultraligera</p>
                                <p class="text-[9px] text-slate-400 truncate">Carga instantánea</p>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-slate-900/60 border border-slate-700/60 flex items-center gap-2 col-span-2 sm:col-span-1">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="wifi-off" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-slate-200 truncate">Modo Offline</p>
                                <p class="text-[9px] text-slate-400 truncate">Aviso de contingencia</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Install CTA Button -->
            <div class="mt-8 pt-6 border-t border-slate-700/60 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button id="main-install-btn" type="button" onclick="triggerPWAInstall()"
                    class="flex-1 py-3.5 sm:py-4 px-6 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-sm sm:text-base rounded-2xl shadow-xl shadow-indigo-600/30 transition transform active:scale-[0.98] flex items-center justify-center gap-2.5 cursor-pointer">
                    <i data-lucide="download" class="w-5 h-5"></i>
                    <span>Instalar aplicación en este dispositivo</span>
                </button>

                <a href="{{ route('login') }}" class="py-3.5 sm:py-4 px-5 bg-slate-900/80 hover:bg-slate-900 border border-slate-700 hover:border-slate-600 text-slate-300 hover:text-white font-semibold text-xs sm:text-sm rounded-2xl transition flex items-center justify-center gap-2 text-center shrink-0">
                    <i data-lucide="globe" class="w-4 h-4 text-indigo-400"></i>
                    <span>Usar versión web</span>
                </a>
            </div>

            <!-- Notice below button -->
            <div id="install-hint" class="mt-3 text-center text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
                <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-400 shrink-0"></i>
                <span>Compatible con Google Chrome, Microsoft Edge, Safari iOS y navegadores modernos.</span>
            </div>
        </div>

        <!-- Section: Guía Paso a Paso -->
        <div class="space-y-6">
            <div class="text-center space-y-1">
                <h3 class="text-lg sm:text-2xl font-bold text-white tracking-tight flex items-center justify-center gap-2">
                    <i data-lucide="list-ordered" class="w-5 h-5 text-indigo-400"></i>
                    <span>Guía paso a paso para instalar</span>
                </h3>
                <p class="text-xs text-slate-400">Selecciona tu dispositivo para ver las instrucciones específicas de instalación:</p>
            </div>

            <!-- Platform Switcher Tabs -->
            <div class="flex justify-center">
                <div class="inline-flex p-1 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-inner gap-1 max-w-full overflow-x-auto">
                    <button type="button" onclick="switchGuide('android')" id="tab-android" class="guide-tab px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md">
                        <i data-lucide="smartphone" class="w-4 h-4"></i>
                        <span>Android / Chrome</span>
                    </button>
                    <button type="button" onclick="switchGuide('ios')" id="tab-ios" class="guide-tab px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 text-slate-400 hover:text-slate-200">
                        <i data-lucide="apple" class="w-4 h-4"></i>
                        <span>iPhone / Safari</span>
                    </button>
                    <button type="button" onclick="switchGuide('desktop')" id="tab-desktop" class="guide-tab px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 text-slate-400 hover:text-slate-200">
                        <i data-lucide="laptop" class="w-4 h-4"></i>
                        <span>PC / Mac / Edge</span>
                    </button>
                </div>
            </div>

            <!-- Content: Android -->
            <div id="guide-android" class="guide-content grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">1</span>
                    <h4 class="text-sm font-bold text-white">Abre el menú de Chrome</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Toca los <strong>tres puntos verticales (⋮)</strong> ubicados en la esquina superior derecha del navegador Chrome.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">2</span>
                    <h4 class="text-sm font-bold text-white">Selecciona Instalar</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Busca y presiona la opción <strong class="text-slate-200">"Instalar aplicación"</strong> o <strong class="text-slate-200">"Agregar a la pantalla principal"</strong>.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">3</span>
                    <h4 class="text-sm font-bold text-white">¡Listo para usar!</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Confirma tocando <strong>"Instalar"</strong>. El icono de NegocioManager se agregará a tu pantalla de inicio y aplicaciones.
                    </p>
                </div>
            </div>

            <!-- Content: iOS -->
            <div id="guide-ios" class="guide-content hidden grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">1</span>
                    <h4 class="text-sm font-bold text-white">Toca Compartir en Safari</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        En la barra inferior de Safari, pulsa el botón <strong>Compartir</strong> (<i data-lucide="share" class="inline w-3 h-3 text-indigo-400"></i> icono de cuadrado con flecha hacia arriba).
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">2</span>
                    <h4 class="text-sm font-bold text-white">Agregar al inicio</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Desplázate hacia abajo en el menú desplegable y toca la opción <strong class="text-slate-200">"Agregar al inicio"</strong> (<i data-lucide="plus-square" class="inline w-3 h-3 text-indigo-400"></i>).
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">3</span>
                    <h4 class="text-sm font-bold text-white">Confirmar y Abrir</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Pulsa <strong>"Agregar"</strong> en la esquina superior derecha. NegocioManager aparecerá en tu iPhone como una app nativa.
                    </p>
                </div>
            </div>

            <!-- Content: Desktop -->
            <div id="guide-desktop" class="guide-content hidden grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">1</span>
                    <h4 class="text-sm font-bold text-white">Haz clic en Instalar</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Haz clic en el botón principal <strong class="text-slate-200">"Instalar aplicación"</strong> de esta página.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">2</span>
                    <h4 class="text-sm font-bold text-white">O usa la barra superior</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        También puedes hacer clic en el icono de instalación (<i data-lucide="monitor-down" class="inline w-3 h-3 text-indigo-400"></i>) en la barra de direcciones de Chrome o Edge.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-3 relative overflow-hidden">
                    <span class="w-7 h-7 rounded-full bg-indigo-600/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30">3</span>
                    <h4 class="text-sm font-bold text-white">Ventana Independiente</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Haz clic en <strong>"Instalar"</strong>. La aplicación se abrirá en su propia ventana sin marcos y con acceso directo en tu escritorio.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section: Experiencia en Móvil -->
        <div class="bg-slate-900/50 border border-slate-800/80 rounded-3xl p-6 sm:p-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <!-- Left Details -->
                <div class="lg:col-span-7 space-y-5">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-500/10 text-violet-400 border border-violet-500/20">
                        <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                        <span>Experiencia en Móvil</span>
                    </div>

                    <h3 class="text-xl sm:text-3xl font-bold text-white tracking-tight">
                        La misma potencia del sistema, adaptada a la palma de tu mano
                    </h3>

                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Al instalar la PWA, NegocioManager se ejecuta a pantalla completa, eliminando distracciones de pestañas del navegador y permitiéndote gestionar tus proyectos, tareas y tableros Kanban en cualquier momento y lugar.
                    </p>

                    <div class="space-y-3 pt-2">
                        <div class="flex items-start gap-3">
                            <div class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 mt-0.5 shrink-0">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-200">Navegación Táctil Optimizada</h4>
                                <p class="text-[11px] sm:text-xs text-slate-400">Pestañas horizontales con deslizamiento suave, menús laterales colapsables y tarjetas adaptativas.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 mt-0.5 shrink-0">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-200">Sincronización en Tiempo Real</h4>
                                <p class="text-[11px] sm:text-xs text-slate-400">Tus cambios, estados de tareas y comentarios se guardan directamente en el servidor de Laravel.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 mt-0.5 shrink-0">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-200">Sin Ocupar Espacio</h4>
                                <p class="text-[11px] sm:text-xs text-slate-400">No satura la memoria de tu teléfono con gigabytes de almacenamiento.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Smartphone Mockup -->
                <div class="lg:col-span-5 flex justify-center">
                    <div class="w-64 sm:w-72 bg-slate-950 border-4 border-slate-700/80 rounded-[2.5rem] p-3 shadow-2xl shadow-indigo-950/60 relative">
                        <!-- Top Speaker / Camera Notch -->
                        <div class="w-24 h-4 bg-slate-800 rounded-full mx-auto mb-2.5 flex items-center justify-center">
                            <div class="w-2.5 h-2.5 rounded-full bg-slate-900 border border-slate-700"></div>
                        </div>

                        <!-- Screen Content (Simulated Mockup) -->
                        <div class="bg-slate-900 rounded-[1.75rem] border border-slate-800 p-3 space-y-3 overflow-hidden text-left">
                            <!-- Mini App Header -->
                            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg overflow-hidden border border-indigo-500/40">
                                        <img src="{{ asset('icons/icono-agenda.webp') }}" class="w-full h-full object-cover" alt="icon" onerror="this.src='{{ asset('icons/icono-agenda.jpg') }}'">
                                    </div>
                                    <span class="text-[11px] font-bold text-white">Gestor de Proyectos</span>
                                </div>
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            </div>

                            <!-- Mini Tab selector -->
                            <div class="flex gap-1 overflow-x-hidden text-[9px] font-medium">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white font-semibold">Calendario</span>
                                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-400">Kanban</span>
                                <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-400">Enlaces</span>
                            </div>

                            <!-- Mini Task Cards -->
                            <div class="space-y-2">
                                <div class="p-2 rounded-lg bg-slate-800/80 border border-slate-700/60 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-200">Revisión de requerimientos</span>
                                        <span class="text-[8px] bg-emerald-500/20 text-emerald-400 px-1 py-0.2 rounded">Finalizado</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-1">
                                        <div class="bg-emerald-400 h-1 rounded-full w-full"></div>
                                    </div>
                                </div>

                                <div class="p-2 rounded-lg bg-slate-800/80 border border-slate-700/60 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-200">Diseño de arquitectura</span>
                                        <span class="text-[8px] bg-amber-500/20 text-amber-400 px-1 py-0.2 rounded">En proceso</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-1">
                                        <div class="bg-amber-400 h-1 rounded-full w-3/4"></div>
                                    </div>
                                </div>

                                <div class="p-2 rounded-lg bg-slate-800/80 border border-slate-700/60 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-200">Implementación de módulos</span>
                                        <span class="text-[8px] bg-slate-700 text-slate-300 px-1 py-0.2 rounded">Pendiente</span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-1">
                                        <div class="bg-indigo-500 h-1 rounded-full w-1/4"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini Bottom status bar -->
                            <div class="pt-1 text-center">
                                <span class="text-[9px] text-slate-500">PWA activa en modo standalone</span>
                            </div>
                        </div>

                        <!-- Home Bar Indicator -->
                        <div class="w-20 h-1 bg-slate-700 rounded-full mx-auto mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting & Help Section -->
        <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-4">
            <div class="flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-indigo-400"></i>
                <h4 class="text-sm font-bold text-white">¿Problemas o dudas para instalar?</h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-400">
                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800/80 space-y-1">
                    <p class="font-semibold text-slate-200">¿El botón de instalación no responde?</p>
                    <p class="leading-relaxed">En iPhone/iPad la instalación siempre se realiza desde el menú <strong class="text-slate-300">Compartir → Agregar al inicio</strong> de Safari. En Android, utiliza Google Chrome o Microsoft Edge.</p>
                </div>

                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800/80 space-y-1">
                    <p class="font-semibold text-slate-200">¿Cómo desinstalar la app si es necesario?</p>
                    <p class="leading-relaxed">Mantén presionado el icono de la aplicación en tu pantalla de inicio y selecciona <strong class="text-slate-300">Eliminar / Desinstalar</strong>, exactamente igual que cualquier otra app.</p>
                </div>
            </div>
        </div>

        <!-- Footer Navigation Links -->
        <div class="pt-6 border-t border-slate-800 text-center space-y-4">
            <div class="flex flex-wrap items-center justify-center gap-4 text-xs font-medium">
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Continuar usando la versión web</span>
                </a>
            </div>
            <p class="text-[11px] text-slate-500">
                &copy; {{ date('Y') }} Gestor de Proyectos &bull; Progressive Web App
            </p>
        </div>

    </main>

    <!-- PWA Helper Script & Dynamic Listeners -->
    <script src="{{ asset('js/pwa.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }

            // Detect if already installed / running in standalone mode
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                const installBtn = document.getElementById('main-install-btn');
                const hint = document.getElementById('install-hint');
                if (installBtn) {
                    installBtn.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 text-emerald-300"></i><span>Aplicación instalada &mdash; Abrir NegocioManager</span>';
                    installBtn.className = installBtn.className.replace('from-indigo-600 via-indigo-500 to-violet-600', 'from-emerald-600 to-teal-600');
                    installBtn.onclick = () => { window.location.href = "{{ route('login') }}"; };
                }
                if (hint) {
                    hint.innerHTML = '<span class="text-emerald-400 font-medium">Estás ejecutando la aplicación en modo PWA instalada.</span>';
                }
                if (window.lucide) { lucide.createIcons(); }
            }
        });

        // Tab switcher function for installation guides
        function switchGuide(platform) {
            document.querySelectorAll('.guide-tab').forEach(tab => {
                tab.className = 'guide-tab px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 text-slate-400 hover:text-slate-200';
            });
            document.querySelectorAll('.guide-content').forEach(content => {
                content.classList.add('hidden');
            });

            const activeTab = document.getElementById('tab-' + platform);
            const activeContent = document.getElementById('guide-' + platform);

            if (activeTab) {
                activeTab.className = 'guide-tab px-4 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md';
            }
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
            if (window.lucide) {
                lucide.createIcons();
            }
        }
    </script>
</body>
</html>
