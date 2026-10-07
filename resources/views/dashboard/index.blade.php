@extends('layouts.app')

@section('title', 'Gestor de Proyectos & Kanban - ' . ($activeProject ? $activeProject->name : 'Inicio'))

@section('content')
@php
    $overdueTasks = $overdueTasks ?? ($tasks ? $tasks->filter(fn($t) => $t->isOverdue()) : collect());
    $overdueCount = $overdueCount ?? $overdueTasks->count();
@endphp

<!-- Mobile Sidebar Backdrop -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-30 hidden md:hidden transition-opacity"></div>

<!-- Sidebar Navigation -->
<aside id="sidebar" class="sidebar-collapsed fixed md:static inset-y-0 left-0 z-40 w-72 max-w-[85vw] bg-white dark:bg-slate-950 text-slate-600 dark:text-slate-300 border-r border-slate-200 dark:border-slate-800/80 flex flex-col transition-all duration-300 transform -translate-x-full md:translate-x-0 shrink-0">
    <!-- Inicialización inmediata para evitar parpadeo (Flicker) -->
    <script>
        (function() {
            try {
                var saved = localStorage.getItem('sidebar_collapsed') ?? localStorage.getItem('sidebarCollapsed');
                // Estado por defecto: CERRADO (sidebar-collapsed ya está aplicado por defecto en el HTML).
                // Solo si el usuario lo abrió explícitamente y está en escritorio, removemos la clase de colapso:
                if (saved === 'false' && window.innerWidth >= 768) {
                    var sb = document.getElementById('sidebar');
                    if (sb) sb.classList.remove('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    <!-- Header App Logo -->
    <div class="p-4 sm:p-5 border-b border-slate-200/80 dark:border-slate-800/90 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-[#0f172a] border border-slate-700/60 flex items-center justify-center shrink-0 shadow-md p-1">
                <img src="{{ asset('icons/iconoround.png') }}" alt="NegocioManager" class="w-8 h-8 object-contain">
            </div>
            <div>
                <h1 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white tracking-tight leading-tight">NegocioManager</h1>
                <!--<span class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Panel Central</span> -->
            </div>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition" aria-label="Cerrar menú">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Quick Theme Toggle in Sidebar (Moved below NegocioManager / Panel Central) -->
    <div class="px-3 sm:px-4 py-2.5 border-b border-slate-200/70 dark:border-slate-800/80">
        <button type="button" onclick="toggleTheme()" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-900/90 dark:hover:bg-slate-800/90 border border-slate-200/90 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-amber-400 transition text-xs flex items-center justify-between shadow-2xs">
            <span class="font-medium">Cambiar Tema</span>
            <div class="flex items-center">
                <span class="theme-icon-moon"><i data-lucide="moon" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i></span>
                <span class="theme-icon-sun hidden"><i data-lucide="sun" class="w-4 h-4 text-amber-500 dark:text-amber-400"></i></span>
            </div>
            <!-- Texto 'Claro / Oscuro' ocultado para evitar redundancia -->
            <!-- <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Claro / Oscuro</span> -->
        </button>
    </div>

    <!-- New Project Button -->
    @if($user->hasFullAccess())
    <div class="p-3 sm:p-4 pb-2">
        <button onclick="openProjectModal()"
            class="w-full py-2.5 px-4 bg-[#0f172a] hover:bg-slate-800 text-white font-medium rounded-xl flex items-center justify-center space-x-2 transition-all shadow-md shadow-slate-900/20 active:scale-95 text-xs sm:text-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Nuevo Proyecto</span>
        </button>
    </div>
    @endif

    <!-- Current User Info Card -->
    <div class="px-3 sm:px-4 py-2">
        <div class="flex items-center justify-between bg-slate-100/80 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700/60 rounded-xl px-3 py-2.5">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/60 text-blue-600 dark:text-blue-300 font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                    {{ $user->initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate leading-tight">{{ $user->name }}</p>
                    <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400 block truncate">{{ $user->role }}</span>
                </div>
            </div>
            <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 shadow-xs" title="En línea"></span>
        </div>
    </div>

    <!-- Section Buttons: Proyectos / Todo (Global) -->
    <div class="px-3 sm:px-4 py-2 grid grid-cols-2 gap-2">
        <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'view' => $activeView]) }}"
            class="py-2 px-2.5 rounded-xl text-xs font-medium flex items-center justify-center space-x-1.5 transition
            {{ request()->has('project_id') ? 'bg-[#0f172a] text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800/70 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-transparent' }}">
            <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
            <span>Proyectos</span>
        </a>
        <a href="{{ route('dashboard', ['view' => 'resumen']) }}"
            class="py-2 px-2.5 rounded-xl text-xs font-medium flex items-center justify-center space-x-1.5 transition
            {{ !request()->has('project_id') ? 'bg-[#0f172a] text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800/70 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700/80 border border-slate-200/80 dark:border-transparent' }}">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>General</span>
        </a>
    </div>

    <!-- Projects List -->
    <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1.5 no-scrollbar">
        <div class="px-3 py-1 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center justify-between">
            <span>{{ $user->isHelper() ? 'Proyectos Asignados' : 'Tus Proyectos' }}</span>
            <span class="px-1.5 py-0.5 rounded-full bg-slate-200/80 dark:bg-slate-800 text-[10px] text-slate-600 dark:text-slate-400 font-semibold">{{ $visibleProjects->count() }}</span>
        </div>

        @if($visibleProjects->isEmpty())
            <div class="text-center py-8 px-4 text-slate-400 dark:text-slate-500 text-xs">
                {{ $user->isHelper() ? 'No tienes tareas asignadas en ningún proyecto todavía.' : 'No tienes proyectos guardados. ¡Crea uno para empezar!' }}
            </div>
        @else
            @foreach($visibleProjects as $proj)
                @php $isActive = $activeProject && $activeProject->id == $proj->id; @endphp
                <div class="rounded-xl {{ $isActive ? 'bg-blue-50/80 dark:bg-slate-800/90 border border-blue-200 dark:border-blue-700/50 shadow-xs' : 'bg-slate-100/60 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800/70 hover:bg-slate-100 dark:hover:bg-slate-800/60' }} overflow-hidden transition">
                    <div class="group flex items-center justify-between px-3 py-2.5 text-xs sm:text-sm font-medium
                        {{ $isActive ? 'text-blue-700 dark:text-blue-300 font-semibold border-l-4 border-blue-600 dark:border-blue-500 pl-2' : 'text-slate-700 dark:text-slate-300' }}">
                        <a href="{{ route('dashboard', ['project_id' => $proj->id, 'view' => $activeView]) }}" class="flex items-center space-x-2.5 truncate flex-1 min-w-0">
                            <i data-lucide="folder" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}"></i>
                            <span class="truncate">{{ $proj->name }}</span>
                        </a>
                        <div class="flex items-center space-x-1 shrink-0">
                            @if($user->hasFullAccess())
                                <button title="Agregar Módulo" onclick="openModuleModal({{ $proj->id }})" class="p-1 text-slate-400 hover:text-blue-600 dark:hover:text-blue-300 hover:bg-slate-200/70 dark:hover:bg-slate-700 rounded-lg transition">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                </button>
                                <button title="Editar proyecto" onclick='openProjectModal(@json($proj))' class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-700 rounded-lg transition">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                            @endif
                            <button onclick="toggleProjectAccordion({{ $proj->id }})" class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg transition" aria-label="Expandir módulos">
                                <i data-lucide="chevron-down" id="acc-icon-{{ $proj->id }}" class="w-4 h-4 transition-transform duration-200 {{ $isActive ? '' : '-rotate-90' }}"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modules Accordion -->
                    <div id="acc-content-{{ $proj->id }}" class="{{ $isActive ? '' : 'hidden' }} pl-5 pr-2 py-1.5 space-y-1 bg-slate-100/80 dark:bg-slate-950/40 border-t border-slate-200/80 dark:border-slate-800/70 text-xs">
                        <a href="{{ route('dashboard', ['project_id' => $proj->id, 'module_id' => 'all', 'view' => $activeView]) }}"
                            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg cursor-pointer transition
                            {{ $isActive && $activeModuleId === 'all' ? 'bg-blue-100/80 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800/60' }}">
                            <div class="flex items-center space-x-2">
                                <i data-lucide="boxes" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>Todos los módulos</span>
                            </div>
                            <span class="text-[10px] bg-slate-200 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 px-1.5 py-0.5 rounded-full font-medium">{{ $proj->tasks->count() }}</span>
                        </a>

                        @foreach($proj->modules as $mod)
                            <div class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg cursor-pointer transition
                                {{ $isActive && $activeModuleId == $mod->id ? 'bg-blue-100/80 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800/60' }}">
                                <a href="{{ route('dashboard', ['project_id' => $proj->id, 'module_id' => $mod->id, 'view' => $activeView]) }}" class="flex items-center space-x-2 truncate flex-1">
                                    <i data-lucide="box" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span class="truncate">{{ $mod->name }}</span>
                                </a>
                                <div class="flex items-center space-x-1 shrink-0">
                                    <span class="text-[10px] bg-slate-200 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 px-1.5 py-0.5 rounded-full font-medium">{{ $mod->tasks->count() }}</span>
                                    @if($user->hasFullAccess())
                                        <button title="Editar módulo" onclick="openModuleModal({{ $proj->id }}, {{ json_encode($mod) }})" class="p-1 opacity-0 group-hover:opacity-100 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-700 rounded transition">
                                            <i data-lucide="pencil" class="w-3 h-3"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Sidebar Footer -->
    <div class="p-3.5 border-t border-slate-200/80 dark:border-slate-800/90 text-xs text-slate-500 dark:text-slate-400 flex flex-col items-center space-y-2 shrink-0">
        <!-- PWA Install Button -->
        <!-- <button id="pwa-install-btn" onclick="triggerPWAInstall()" class="hidden text-indigo-600 dark:text-indigo-300 hover:text-indigo-800 dark:hover:text-white transition text-xs font-semibold flex items-center space-x-2 py-2 px-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 hover:bg-indigo-100 dark:hover:bg-indigo-900/80 border border-indigo-200 dark:border-indigo-500/40 w-full justify-center shadow-xs">
            <i data-lucide="download" class="w-4 h-4 text-indigo-500 dark:text-indigo-400"></i>
            <span>Instalar Aplicación PWA</span>
        </button> -->

        <a href="{{ route('config.index') }}" class="w-full py-1.5 px-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition text-xs flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                <span>Configuración</span>
            </div>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        </a>

        @if($user->hasFullAccess())
            <button onclick="openUsersListModal()" class="w-full py-1.5 px-3 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition text-xs flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                    <span>Gestión de Usuarios</span>
                </div>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-200/90 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300">{{ $allUsers->count() }}</span>
            </button>
        @endif
    </div>
</aside>

<!-- Main Content Workspace -->
<main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50 dark:bg-slate-950 transition-colors duration-200">

    <!-- Top Workspace Navbar -->
    <header class="bg-[#0f172a] lg:bg-white/95 lg:dark:bg-slate-900/95 backdrop-blur-md border-b-0 lg:border-b lg:border-slate-200/80 lg:dark:border-slate-800/80 px-3 sm:px-6 flex flex-col lg:flex-row lg:items-stretch shadow-soft shrink-0 z-20">

        <!-- Top row on mobile / Left column on desktop -->
        <div class="flex items-center justify-between lg:justify-start lg:flex-none py-3.5 sm:py-4 lg:py-3.5 min-h-[58px] lg:min-h-0 gap-2 min-w-0">
            <!-- Mobile hamburger + Project title -->
            <div class="flex items-center space-x-2.5 sm:space-x-4 min-w-0">
                <button id="sidebar-toggle-btn" onclick="toggleSidebar()" class="text-white hover:text-slate-200 lg:text-slate-600 lg:dark:text-slate-300 lg:hover:text-slate-900 lg:dark:hover:text-white p-1.5 -ml-1 rounded-xl hover:bg-white/10 lg:hover:bg-slate-100 lg:dark:hover:bg-slate-800 focus:outline-none transition shrink-0" aria-label="Alternar menú lateral" title="Alternar menú lateral">
                    <i data-lucide="menu" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                </button>
                <div class="min-w-0">
                    <div class="flex items-center space-x-2 flex-wrap sm:flex-nowrap gap-y-1">
                        <h2 class="text-sm font-bold text-white truncate max-w-[200px] tracking-tight lg:hidden">
                            {{ $activeProject ? $activeProject->name : 'Selecciona un Proyecto' }}
                        </h2>
                        <h2 class="hidden lg:block text-sm sm:text-lg font-bold text-slate-900 dark:text-white truncate max-w-[200px] sm:max-w-xs md:max-w-md tracking-tight">
                            {{ $activeProject ? $activeProject->name : 'Selecciona un Proyecto' }}
                        </h2>
                        @if($activeProject && $activeModuleId && $activeModuleId !== 'all')
                            @php $activeMod = $activeProject->modules->firstWhere('id', $activeModuleId); @endphp
                            @if($activeMod)
                                <span class="hidden lg:flex text-[11px] bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/70 font-semibold px-2.5 py-0.5 rounded-full items-center space-x-1 shrink-0">
                                    <i data-lucide="box" class="w-3 h-3"></i>
                                    <span class="truncate max-w-[120px]">{{ $activeMod->name }}</span>
                                </span>
                            @endif
                        @endif
                    </div>
                    @if($activeProject)
                        <!--<p class="hidden lg:flex text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>{{ $todayTasks->count() }} tarea(s) para hoy &bull; {{ $tasks->count() }} en total</span>
                        </p>-->
                    @endif
                </div>
            </div>

            <!-- Right actions on mobile/tablet (< lg) -->
            <div class="flex items-center space-x-1.5 sm:space-x-2 lg:hidden shrink-0">
                <!-- Theme toggle button on mobile -->
                <button type="button" onclick="toggleTheme()" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition" title="Cambiar modo claro / oscuro">
                    <span class="theme-icon-moon"><i data-lucide="moon" class="w-4 h-4"></i></span>
                    <span class="theme-icon-sun hidden"><i data-lucide="sun" class="w-4 h-4 text-amber-400"></i></span>
                </button>

                <!-- Botón móvil Papelera (icono cuadrado con fondo rojo) -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'papelera']) }}"
                    class="p-2 rounded-xl bg-red-600 hover:bg-red-700 text-white transition flex items-center justify-center shadow-xs active:scale-95"
                    title="Papelera">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </a>

                @if($activeProject)
                    @if($activeView === 'resumen' || $activeView === 'kanban' || $activeView === 'calendario')
                        @if(!$user->isHelper())
                            <button onclick="openTaskModal()"
                                class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs flex items-center space-x-1.5 transition shadow-xs active:scale-95" title="Agregar Tarea">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Tarea</span>
                            </button>
                        @endif
                    @elseif($activeView === 'incidencias')
                        <button onclick="openIncidentModal()"
                            class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-red-600 hover:bg-red-500 text-white font-medium rounded-xl text-xs flex items-center space-x-1.5 transition shadow-xs active:scale-95" title="Reportar Incidencia">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Reportar</span>
                        </button>
                    @elseif($activeView === 'periodicas')
                        @if(!$user->isHelper())
                            <button onclick="openRecurringTaskModal()"
                                class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs flex items-center space-x-1.5 transition shadow-xs active:scale-95" title="Nueva Tarea Periódica">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Nueva</span>
                            </button>
                        @endif
                    @elseif($activeView === 'enlaces' && !$user->isHelper())
                        <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))'
                            class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs flex items-center space-x-1.5 transition shadow-xs active:scale-95" title="Gestionar Enlaces">
                            <i data-lucide="link" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Enlaces</span>
                        </button>
                    @endif
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Cerrar Sesión" class="flex items-center space-x-1 p-1.5 sm:p-2 rounded-xl bg-white/10 hover:bg-red-500/20 text-red-300 hover:text-red-200 text-xs transition border border-white/10">
                        <i data-lucide="log-out" class="w-4 h-4"></i> 
                    </button>
                </form>
            </div>
        </div>

        <!-- Desktop Center: Tab Navigation (lg+) -->
        @if($activeProject)
        <nav class="hidden lg:flex items-stretch overflow-x-auto no-scrollbar lg:border-t-0 lg:mx-0 lg:px-0 flex-nowrap shrink-0 gap-1 sm:gap-2 lg:ml-4">
            <!-- 1. Tab Resumen / Dashboard -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'resumen']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'resumen' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Resumen</span>
            </a>

            <!-- 2. Tab Calendario -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'calendario']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'calendario' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                <span>Calendario</span>
            </a>

            <!-- 3. Tab Kanban -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'kanban']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'kanban' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="kanban" class="w-4 h-4"></i>
                <span>Tablero Kanban</span>
            </a>

            <!-- 4. Tab Enlaces -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'enlaces']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'enlaces' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="link" class="w-4 h-4"></i>
                <span>Enlaces</span>
                @php
                    $userLinkCount = $user->isHelper()
                        ? $activeProject->links->filter(fn($l) => $l->assignedUsers->contains('id', $user->id))->count()
                        : $activeProject->links->count();
                @endphp
                @if($userLinkCount > 0)
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1.5 py-0.5 rounded-full font-bold">
                        {{ $userLinkCount }}
                    </span>
                @endif
            </a>

            <!-- 5. Tab Incidencias -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'incidencias']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'incidencias' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Incidencias</span>
                @if($incidents->count() > 0)
                    <span class="text-[10px] bg-red-100 dark:bg-red-950/70 text-red-700 dark:text-red-400 px-1.5 py-0.5 rounded-full font-bold">
                        {{ $incidents->count() }}
                    </span>
                @endif
            </a>

            <!-- 6. Tab Tareas Periódicas (Admin/Dev) -->
            @if($user->isAdmin())
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'periodicas']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'periodicas' ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="repeat" class="w-4 h-4"></i>
                <span>Periódicas</span>
                @if($recurringTasks->count() > 0)
                    <span class="text-[10px] bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-400 px-1.5 py-0.5 rounded-full font-bold">
                        {{ $recurringTasks->count() }}
                    </span>
                @endif
            </a>
            @endif

            <!-- 7. Tab Papelera -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'papelera']) }}"
                class="flex items-center space-x-1.5 px-3 py-2.5 lg:py-0 text-xs sm:text-sm font-semibold border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'papelera' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Papelera</span>
                @if($trashedTasksCount > 0)
                    <span class="text-[10px] bg-red-100 dark:bg-red-950/70 text-red-700 dark:text-red-400 px-1.5 py-0.5 rounded-full font-bold">
                        {{ $trashedTasksCount }}
                    </span>
                @endif
            </a>
        </nav>

        <!-- Mobile Contextual Sub-navigation (Reemplaza accesos repetidos por subapartados de la sección activa) -->
        <nav class="lg:hidden flex items-stretch overflow-x-auto no-scrollbar border-t border-slate-100 dark:border-slate-800/80 -mx-3 px-3 sm:-mx-6 sm:px-6 flex-nowrap shrink-0 gap-1 sm:gap-2 py-1 bg-white dark:bg-slate-950">
            @if($activeView === 'resumen')
                <!-- Subnavegación Contextual: Resumen -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'resumen', 'task_filter' => 'hoy']) }}"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0
                    {{ $taskFilter === 'hoy' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Tareas de hoy</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold {{ $taskFilter === 'hoy' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $todayTasks->count() }}</span>
                </a>

                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'resumen', 'task_filter' => 'mes']) }}"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0
                    {{ $taskFilter === 'mes' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                    <span>Tareas del mes</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold {{ $taskFilter === 'mes' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $monthTasks->count() }}</span>
                </a>

                <button type="button" onclick="document.getElementById('kpi-stats-grid')?.scrollIntoView({behavior: 'smooth'})"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 whitespace-nowrap transition shrink-0">
                    <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i>
                    <span>Estadísticas</span>
                </button>

                @if($overdueCount > 0)
                <button type="button" onclick="document.getElementById('widget-urgencias')?.scrollIntoView({behavior: 'smooth'})"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 border-transparent text-red-600 dark:text-red-400 whitespace-nowrap transition shrink-0">
                    <i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i>
                    <span>Urgencias</span>
                    <span class="text-[10px] bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 px-1.5 py-0.2 rounded-full font-bold">{{ $overdueCount }}</span>
                </button>
                @endif

            @elseif($activeView === 'calendario')
                <!-- Subnavegación Contextual: Calendario -->
                <button type="button" id="subnav-cal-btn-calendar" onclick="selectMobileCalView('calendar')"
                    class="cal-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Calendario</span>
                </button>

                <button type="button" id="subnav-cal-btn-list" onclick="selectMobileCalView('list')"
                    class="cal-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                    <i data-lucide="list-checks" class="w-3.5 h-3.5"></i>
                    <span>Lista de tareas del proyecto</span>
                </button>

                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'calendario', 'task_filter' => 'hoy', 'cal_subview' => 'list']) }}"
                    id="subnav-cal-btn-hoy"
                    class="cal-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                    <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                    <span>Tareas de hoy</span>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1.5 py-0.2 rounded-full font-bold {{ $taskFilter === 'hoy' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : '' }}">{{ $todayTasks->count() }}</span>
                </a>

                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'calendario', 'task_filter' => 'mes', 'cal_subview' => 'list']) }}"
                    id="subnav-cal-btn-mes"
                    class="cal-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                    <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                    <span>Tareas del mes</span>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1.5 py-0.2 rounded-full font-bold {{ $taskFilter === 'mes' ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : '' }}">{{ $monthTasks->count() }}</span>
                </a>

            @elseif($activeView === 'kanban')
                <!-- Subnavegación Contextual: Tablero Kanban (Estados reales) -->
                @php
                    $mobileKanbanCols = [
                        ['id' => 'no_iniciado', 'title' => 'No Iniciado', 'icon' => 'circle', 'badgeBg' => 'bg-slate-200 dark:bg-slate-700', 'badgeText' => 'text-slate-700 dark:text-slate-300'],
                        ['id' => 'en_proceso', 'title' => 'En Proceso', 'icon' => 'clock', 'badgeBg' => 'bg-amber-100 dark:bg-amber-950/70', 'badgeText' => 'text-amber-800 dark:text-amber-300'],
                        ['id' => 'en_revision', 'title' => 'En Revisión', 'icon' => 'search', 'badgeBg' => 'bg-purple-100 dark:bg-purple-950/70', 'badgeText' => 'text-purple-800 dark:text-purple-300'],
                        ['id' => 'rechazado', 'title' => 'Rechazado', 'icon' => 'x-circle', 'badgeBg' => 'bg-red-100 dark:bg-red-950/70', 'badgeText' => 'text-red-800 dark:text-red-300'],
                        ['id' => 'finalizado', 'title' => 'Finalizado', 'icon' => 'check-circle', 'badgeBg' => 'bg-emerald-100 dark:bg-emerald-950/70', 'badgeText' => 'text-emerald-800 dark:text-emerald-300'],
                    ];
                @endphp

                <button type="button" id="subnav-kanban-btn-todos" onclick="filterMobileKanban('todos')"
                    class="kanban-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>Todos</span>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1.5 py-0.2 rounded-full font-bold">{{ $filteredTasks->count() }}</span>
                </button>

                @foreach($mobileKanbanCols as $col)
                    @php $colTasksCount = $filteredTasks->where('status', $col['id'])->count(); @endphp
                    <button type="button" id="subnav-kanban-btn-{{ $col['id'] }}" onclick="filterMobileKanban('{{ $col['id'] }}')"
                        class="kanban-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                        <i data-lucide="{{ $col['icon'] }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $col['title'] }}</span>
                        <span class="text-[10px] {{ $col['badgeBg'] }} {{ $col['badgeText'] }} px-1.5 py-0.2 rounded-full font-bold">{{ $colTasksCount }}</span>
                    </button>
                @endforeach

            @elseif($activeView === 'incidencias')
                <!-- Subnavegación Contextual: Incidencias -->
                <button type="button" id="subnav-inc-btn-todas" onclick="filterMobileIncidents('todas')"
                    class="inc-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-red-600 text-red-600 dark:text-red-400 dark:border-red-500">
                    <span>Todas</span>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1.5 py-0.2 rounded-full font-bold">{{ $incidents->count() }}</span>
                </button>
                <button type="button" id="subnav-inc-btn-abierta" onclick="filterMobileIncidents('abierta')"
                    class="inc-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                    <span>Abiertas</span>
                    <span class="text-[10px] bg-red-100 dark:bg-red-950/70 text-red-700 dark:text-red-300 px-1.5 py-0.2 rounded-full font-bold">{{ $incidents->where('estado', 'abierta')->count() }}</span>
                </button>
                <button type="button" id="subnav-inc-btn-en_proceso" onclick="filterMobileIncidents('en_proceso')"
                    class="inc-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                    <span>En Proceso</span>
                    <span class="text-[10px] bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 px-1.5 py-0.2 rounded-full font-bold">{{ $incidents->where('estado', 'en_proceso')->count() }}</span>
                </button>
                <button type="button" id="subnav-inc-btn-resuelta" onclick="filterMobileIncidents('resuelta')"
                    class="inc-subnav-btn flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                    <span>Resueltas</span>
                    <span class="text-[10px] bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 px-1.5 py-0.2 rounded-full font-bold">{{ $incidents->where('estado', 'resuelta')->count() }}</span>
                </button>

            @elseif($activeView === 'enlaces')
                <!-- Subnavegación Contextual: Enlaces -->
                @php
                    $mUserLinkCount = $user->isHelper()
                        ? $activeProject->links->filter(fn($l) => $l->assignedUsers->contains('id', $user->id))->count()
                        : $activeProject->links->count();
                @endphp
                <div class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500 whitespace-nowrap">
                    <i data-lucide="link" class="w-3.5 h-3.5"></i>
                    <span>Todos los Enlaces</span>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-1.5 py-0.2 rounded-full font-bold">{{ $mUserLinkCount }}</span>
                </div>

            @elseif($activeView === 'periodicas')
                <!-- Subnavegación Contextual: Tareas Periódicas -->
                <div class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-500 whitespace-nowrap">
                    <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                    <span>Todas las Reglas</span>
                    <span class="text-[10px] bg-indigo-100 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-400 px-1.5 py-0.2 rounded-full font-bold">{{ $recurringTasks->count() }}</span>
                </div>
                <div class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 border-transparent text-slate-500 dark:text-slate-400 whitespace-nowrap">
                    <span>Activas: {{ $recurringTasks->where('active', true)->count() }}</span>
                </div>

            @elseif($activeView === 'papelera')
                <!-- Subnavegación Contextual: Papelera -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'todas']) }}"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0
                    {{ $trashFilter === 'todas' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Todas</span>
                    <span class="text-[10px] bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 px-1.5 py-0.2 rounded-full font-bold">{{ $allTrashCount }}</span>
                </a>

                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'dia']) }}"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0
                    {{ $trashFilter === 'dia' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>Hoy</span>
                </a>

                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'semana']) }}"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0
                    {{ $trashFilter === 'semana' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>Esta Semana</span>
                </a>

                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'mes']) }}"
                    class="flex items-center space-x-1 px-2.5 py-1.5 text-xs font-semibold border-b-2 whitespace-nowrap transition shrink-0
                    {{ $trashFilter === 'mes' ? 'border-red-600 text-red-600 dark:text-red-400 dark:border-red-500' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>Este Mes</span>
                </a>
            @endif
        </nav>
        @endif

        <!-- Right (Desktop lg+): Theme toggle, Action Button & Logout -->
        <div class="hidden lg:flex lg:flex-none items-center lg:justify-start py-3.5 space-x-3 lg:ml-auto lg:pl-4 lg:border-l lg:border-slate-200/80 lg:dark:border-slate-800/80">
            @if($activeProject)
                @if($activeView === 'resumen' || $activeView === 'kanban' || $activeView === 'calendario')
                    @if(!$user->isHelper())
                        <button onclick="openTaskModal()"
                            class="py-2 px-3.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition shadow-soft active:scale-95">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Agregar Tarea</span>
                        </button>
                    @endif
                @elseif($activeView === 'incidencias')
                    <button onclick="openIncidentModal()"
                        class="py-2 px-3.5 bg-red-600 hover:bg-red-500 text-white font-medium rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition shadow-soft active:scale-95">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>Reportar Incidencia</span>
                    </button>
                @elseif($activeView === 'periodicas')
                    @if(!$user->isHelper())
                        <button onclick="openRecurringTaskModal()"
                            class="py-2 px-3.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition shadow-soft active:scale-95">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Nueva Tarea Periódica</span>
                        </button>
                    @endif
                @elseif($activeView === 'enlaces' && !$user->isHelper())
                    <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))'
                        class="py-2 px-3.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs sm:text-sm flex items-center space-x-2 transition shadow-soft active:scale-95">
                        <i data-lucide="link" class="w-4 h-4"></i>
                        <span>Gestionar Enlaces</span>
                    </button>
                @endif
            @endif

            <!-- Theme toggle button on desktop -->
            <button type="button" onclick="toggleTheme()" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-amber-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition" title="Cambiar modo claro / oscuro">
                <span class="theme-icon-moon"><i data-lucide="moon" class="w-4 h-4"></i></span>
                <span class="theme-icon-sun hidden"><i data-lucide="sun" class="w-4 h-4"></i></span>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Cerrar Sesión" class="flex items-center space-x-1.5 py-2 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-red-50 dark:hover:bg-red-950/50 hover:text-red-600 dark:hover:text-red-400 text-xs font-semibold transition border border-transparent hover:border-red-200 dark:hover:border-red-900/40">
                    <span>Salir</span>
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> 
                </button>
            </form>
        </div>
    </header>

    <!-- Workspace Body Area -->
    <div class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-6 pb-24 md:pb-8">
        @if(!$activeProject)
            <div class="h-full flex flex-col items-center justify-center text-center py-16 px-4">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl flex items-center justify-center text-[#0f172a] dark:text-slate-200 mb-4 shadow-soft">
                    <i data-lucide="folder-open" class="w-10 h-10"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Sin Proyecto Seleccionado</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mt-1.5 leading-relaxed">Selecciona un proyecto en la barra lateral para ver su tablero y tareas, o crea uno nuevo para comenzar.</p>
                @if($user->hasFullAccess())
                    <button onclick="openProjectModal()" class="mt-5 py-2.5 px-5 bg-[#0f172a] hover:bg-slate-800 text-white font-medium rounded-xl text-sm flex items-center space-x-2 shadow-soft transition active:scale-95">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Crear Primer Proyecto</span>
                    </button>
                @endif
            </div>
        @else

            <!-- ========================================================================= -->
            <!-- VIEW 0: RESUMEN / DASHBOARD GENERAL (NEW MODERN EXECUTIVE VIEW)           -->
            <!-- ========================================================================= -->
            @if($activeView === 'resumen')
                @php
                    $totalTasksCount = $tasks->count();
                    $completedTasksCount = $tasks->where('status', 'finalizado')->count();
                    $inProgressTasksCount = $tasks->where('status', 'en_proceso')->count();
                    $notStartedTasksCount = $tasks->where('status', 'no_iniciado')->count();
                    $inReviewTasksCount = $tasks->where('status', 'en_revision')->count();
                    $rejectedTasksCount = $tasks->where('status', 'rechazado')->count();
                    $overdueTasks = $overdueTasks ?? $tasks->filter(fn($t) => $t->isOverdue());
                    $overdueCount = $overdueCount ?? $overdueTasks->count();
                    $completionRate = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : 0;
                    $openIncidentsCount = $incidents->where('estado', '!=', 'resuelta')->count();
                @endphp

                <div class="space-y-5 sm:space-y-6 max-w-[1600px] mx-auto">
                    <!-- Top Welcome & Status Banner -->
                    <!-- <div class="rounded-2xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 text-white p-4 sm:p-6 shadow-card relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
                       <div class="relative z-10 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/30 border border-indigo-400/30 text-indigo-200 text-[10px] font-bold uppercase tracking-wider">
                                    Panel Administrativo
                                </span>
                                <span class="text-xs text-indigo-200 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Activo</span>
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
                                <span>{{ $activeProject->name }}</span>
                                @if($activeModuleId && $activeModuleId !== 'all')
                                    @php $activeModName = $activeProject->modules->firstWhere('id', $activeModuleId)?->name; @endphp
                                    @if($activeModName)
                                        <span class="text-xs font-normal text-indigo-200 bg-white/10 px-2 py-0.5 rounded-lg border border-white/10">
                                            Módulo: {{ $activeModName }}
                                        </span>
                                    @endif
                                @endif
                            </h2>
                            
                            <p class="text-xs sm:text-sm text-indigo-200/90 max-w-xl">
                             Monitoreo y administración visual en tiempo real de tareas, avances, equipo e incidencias del proyecto.
                            </p>
                            
                        </div>-->

                        <!-- Right Quick Action Shortcuts 
                        <div class="relative z-10 flex items-center gap-2 shrink-0 flex-wrap">
                            @if(!$user->isHelper())
                                <button onclick="openTaskModal()" class="py-2 px-3.5 rounded-xl bg-white text-indigo-900 hover:bg-indigo-50 font-bold text-xs flex items-center gap-1.5 shadow-sm transition active:scale-95">
                                    <i data-lucide="plus" class="w-4 h-4 text-indigo-600"></i>
                                    <span>Nueva Tarea</span>
                                </button>
                            @endif
                            <button onclick="openIncidentModal()" class="py-2 px-3 rounded-xl bg-red-500/20 hover:bg-red-500/30 border border-red-400/40 text-red-100 font-semibold text-xs flex items-center gap-1.5 transition">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-300"></i>
                                <span>Reportar Incidencia</span>
                            </button>
                        </div>-->

                        <!-- Subtle background visual ornament -->
                        <!-- <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute right-1/3 -top-10 w-40 h-40 bg-violet-500/10 rounded-full blur-2xl pointer-events-none"></div>
                        -->
                    <!--</div>-->

                    <!-- 5 Core Metric KPI Cards (Inspired by diseñosreferencia) -->
                    <div id="kpi-stats-grid" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">
                        <!-- KPI 1: Total de tareas -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-card dark:hover:shadow-card-dark transition relative overflow-hidden flex flex-col justify-between">
                            <!--
                            Futuro: aquí podría agregarse un acceso "Ver todas".
                            Actualmente no mostrar porque las secciones ya están disponibles desde el header.
                            -->
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="list-checks" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white leading-none block tracking-tight">{{ $totalTasksCount }}</span>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1 block truncate">Total de tareas</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: En curso -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-card dark:hover:shadow-card-dark transition relative overflow-hidden flex flex-col justify-between">
                            <!--
                            Futuro: aquí podría agregarse un acceso "Ver todas".
                            Actualmente no mostrar porque las secciones ya están disponibles desde el header.
                            -->
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="clock" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white leading-none block tracking-tight">{{ $inProgressTasksCount }}</span>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1 block truncate">En curso</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Atención / vencidas -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-card dark:hover:shadow-card-dark transition relative overflow-hidden flex flex-col justify-between">
                            <!--
                            Futuro: aquí podría agregarse un acceso "Ver todas".
                            Actualmente no mostrar porque las secciones ya están disponibles desde el header.
                            -->
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl {{ $overdueCount > 0 ? 'bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center shrink-0">
                                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-2xl font-black {{ $overdueCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }} leading-none block tracking-tight">{{ $overdueCount }}</span>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1 block truncate">Atención / vencidas</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Incidencias -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-card dark:hover:shadow-card-dark transition relative overflow-hidden flex flex-col justify-between">
                            <!--
                            Futuro: aquí podría agregarse un acceso "Ver todas".
                            Actualmente no mostrar porque las secciones ya están disponibles desde el header.
                            -->
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white leading-none block tracking-tight">{{ $openIncidentsCount }}</span>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1 block truncate">Incidencias</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 5: Total tareas periódicas -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-card dark:hover:shadow-card-dark transition relative overflow-hidden flex flex-col justify-between col-span-2 sm:col-span-2 lg:col-span-1 xl:col-span-1">
                            <!--
                            Futuro: aquí podría agregarse un acceso "Ver todas".
                            Actualmente no mostrar porque las secciones ya están disponibles desde el header.
                            -->
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="repeat" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-2xl font-black text-slate-900 dark:text-white leading-none block tracking-tight">{{ $recurringTasks->count() }}</span>
                                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1 block truncate">Total periódicas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Split Content Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
                        
                        <!-- LEFT COLUMN: Tasks Today & Upcoming (8 cols on lg) -->
                        <div class="lg:col-span-8 space-y-5">

                            <!-- Card: Tareas de Hoy y del Proyecto -->
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 gap-2.5 mb-4">
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <i data-lucide="check-square" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                            <span>Tareas del Proyecto</span>
                                        </h3>
                                        <!--<p class="text-xs text-slate-500 dark:text-slate-400">Listado interactivo con actualización rápida de estados.</p>-->
                                    </div>

                                    <!-- Filter tabs: Hoy vs Mes -->
                                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs shrink-0 self-start sm:self-auto">
                                        <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'resumen', 'task_filter' => 'hoy']) }}"
                                            class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $taskFilter === 'hoy' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                            <span>Hoy</span>
                                            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $taskFilter === 'hoy' ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $todayTasks->count() }}</span>
                                        </a>
                                        <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'resumen', 'task_filter' => 'mes']) }}"
                                            class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $taskFilter === 'mes' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                            <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                                            <span>Este Mes</span>
                                            <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $taskFilter === 'mes' ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $monthTasks->count() }}</span>
                                        </a>
                                    </div>
                                </div>

                                @if($filteredTasks->isEmpty())
                                    <div class="text-center py-12 text-slate-400 dark:text-slate-500 text-xs flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="sparkles" class="w-8 h-8 text-slate-300 dark:text-slate-600"></i>
                                        <span>{{ $taskFilter === 'mes' ? 'No hay tareas registradas para este mes en este módulo/proyecto.' : '¡Todo despejado! No hay tareas pendientes para hoy.' }}</span>
                                    </div>
                                @else
                                    <div class="space-y-3 max-h-[560px] overflow-y-auto pr-1">
                                        @foreach($filteredTasks as $t)
                                            @php
                                                $isTodayTask = $t->status !== 'finalizado' && !$t->isOverdue() && (
                                                    ($t->start_date && \Carbon\Carbon::parse($t->start_date)->isToday()) ||
                                                    ($t->end_date && \Carbon\Carbon::parse($t->end_date)->isToday()) ||
                                                    (!$t->start_date && !$t->end_date && $t->created_at && $t->created_at->isToday())
                                                );
                                                $dueDateStr = null;
                                                if ($t->end_date) {
                                                    $dueDateStr = \Carbon\Carbon::parse($t->end_date)->format('d/m/Y') . ($t->end_time ? ' a las ' . substr($t->end_time, 0, 5) : '');
                                                } elseif ($t->start_date) {
                                                    $dueDateStr = \Carbon\Carbon::parse($t->start_date)->format('d/m/Y') . ($t->start_time ? ' a las ' . substr($t->start_time, 0, 5) : '');
                                                }
                                            @endphp

                                            <div class="p-3 sm:p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-800 transition shadow-xs space-y-2">
                                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5">
                                                    <!-- Checkbox & Title -->
                                                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                                        <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="mt-0.5 shrink-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="{{ $t->status === 'finalizado' ? 'no_iniciado' : ($t->needs_review && !$user->hasFullAccess() ? 'en_revision' : 'finalizado') }}">
                                                            <input type="checkbox" onchange="this.form.submit()" {{ $t->status === 'finalizado' ? 'checked' : '' }}
                                                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer">
                                                        </form>

                                                        <div class="min-w-0 flex-1">
                                                            <h4 class="text-xs sm:text-sm font-semibold break-words {{ $t->status === 'finalizado' ? 'line-through text-slate-400 dark:text-slate-500' : ($t->isOverdue() ? 'text-red-600 dark:text-red-400 font-bold' : ($isTodayTask ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-800 dark:text-slate-100')) }}">
                                                                {{ $t->title }}
                                                            </h4>

                                                            @if($t->description)
                                                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">{{ $t->description }}</p>
                                                            @endif

                                                            <!-- Badges row -->
                                                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                                                @if($t->isOverdue())
                                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 flex items-center gap-1">
                                                                        <i data-lucide="alert-circle" class="w-3 h-3 text-red-600"></i>
                                                                        <span>Vencida: {{ $dueDateStr }}</span>
                                                                    </span>
                                                                @elseif($dueDateStr)
                                                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                                                        <i data-lucide="calendar" class="w-3 h-3 text-indigo-500"></i>
                                                                        <span>{{ $dueDateStr }}</span>
                                                                    </span>
                                                                @endif

                                                                @if($t->module)
                                                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 flex items-center gap-1">
                                                                        <i data-lucide="box" class="w-3 h-3"></i>
                                                                        <span>{{ $t->module->name }}</span>
                                                                    </span>
                                                                @endif

                                                                <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full
                                                                    {{ $t->priority === 'urgente' || $t->priority === 'alta' ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-200/50 dark:border-red-900/40' : ($t->priority === 'media' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/50 dark:border-amber-900/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300') }}">
                                                                    {{ $t->priority }}
                                                                </span>

                                                                @if($t->needs_review)
                                                                    <span class="text-[10px] bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full font-medium flex items-center gap-1">
                                                                        <i data-lucide="search" class="w-3 h-3"></i>
                                                                        <span>Revisión</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Right actions on task item -->
                                                    <div class="flex items-center gap-2 self-end sm:self-start shrink-0">
                                                        <!-- Member Avatars -->
                                                        <div class="flex items-center -space-x-1.5">
                                                            @foreach($t->assignedUsers as $u)
                                                                <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-[9px] flex items-center justify-center border-2 border-white dark:border-slate-800" title="{{ $u->name }}">
                                                                    {{ $u->initials }}
                                                                </span>
                                                            @endforeach
                                                        </div>

                                                        <!-- Status badge -->
                                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md 
                                                            {{ $t->status === 'finalizado' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : ($t->status === 'en_proceso' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' : ($t->status === 'en_revision' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300' : ($t->status === 'rechazado' ? 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'))) }}">
                                                            {{ str_replace('_', ' ', ucfirst($t->status)) }}
                                                        </span>

                                                        <!-- Comments Button -->
                                                        <button type="button" onclick="openCommentsModal({{ $t->id }}, this.getAttribute('data-title'))" data-title="{{ $t->title }}" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-1 transition" title="Comentarios">
                                                            <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                                            <span id="task-comment-count-{{ $t->id }}" class="text-[11px] font-semibold {{ $t->comments_count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">{{ $t->comments_count }}</span>
                                                        </button>

                                                        @if(!$user->isHelper())
                                                            <button onclick='openTaskModal(@json($t->load(["assignedUsers", "subtasks"])))' class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Editar tarea">
                                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Subtasks preview -->
                                                @if($t->subtasks->count() > 0)
                                                    @php
                                                        $stDone = $t->subtasks->where('status', 'finalizado')->count();
                                                        $stTotal = $t->subtasks->count();
                                                        $stPct = round(($stDone / $stTotal) * 100);
                                                    @endphp
                                                    <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center gap-3">
                                                        <div class="flex-1 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                                            <div class="bg-indigo-600 dark:bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $stPct }}%"></div>
                                                        </div>
                                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold shrink-0">
                                                            Subtareas: {{ $stDone }}/{{ $stTotal }} ({{ $stPct }}%)
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Card: Desglose y Distribución de Estados -->
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft">
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                                    <span>Distribución de Estados</span>
                                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ $totalTasksCount }} tareas totales</span>
                                </h3>

                                <!-- Multi-segment visual progress bar -->
                                @php
                                    $pNotStarted = $totalTasksCount > 0 ? round(($notStartedTasksCount / $totalTasksCount) * 100) : 0;
                                    $pInProgress = $totalTasksCount > 0 ? round(($inProgressTasksCount / $totalTasksCount) * 100) : 0;
                                    $pInReview = $totalTasksCount > 0 ? round(($inReviewTasksCount / $totalTasksCount) * 100) : 0;
                                    $pCompleted = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : 0;
                                    $pRejected = $totalTasksCount > 0 ? round(($rejectedTasksCount / $totalTasksCount) * 100) : 0;
                                @endphp

                                <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 flex overflow-hidden mb-3">
                                    <div class="bg-emerald-500 h-full transition-all" style="width: {{ $pCompleted }}%" title="Finalizadas: {{ $completedTasksCount }} ({{ $pCompleted }}%)"></div>
                                    <div class="bg-amber-500 h-full transition-all" style="width: {{ $pInProgress }}%" title="En Proceso: {{ $inProgressTasksCount }} ({{ $pInProgress }}%)"></div>
                                    <div class="bg-purple-500 h-full transition-all" style="width: {{ $pInReview }}%" title="En Revisión: {{ $inReviewTasksCount }} ({{ $pInReview }}%)"></div>
                                    <div class="bg-slate-400 h-full transition-all" style="width: {{ $pNotStarted }}%" title="No Iniciadas: {{ $notStartedTasksCount }} ({{ $pNotStarted }}%)"></div>
                                    <div class="bg-red-500 h-full transition-all" style="width: {{ $pRejected }}%" title="Rechazadas: {{ $rejectedTasksCount }} ({{ $pRejected }}%)"></div>
                                </div>

                                <!-- Legend -->
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>Finalizadas</span>
                                        </div>
                                        <p class="font-extrabold text-sm text-slate-900 dark:text-white mt-0.5">{{ $completedTasksCount }} <span class="text-[10px] font-normal text-slate-500">({{ $pCompleted }}%)</span></p>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>En Proceso</span>
                                        </div>
                                        <p class="font-extrabold text-sm text-slate-900 dark:text-white mt-0.5">{{ $inProgressTasksCount }} <span class="text-[10px] font-normal text-slate-500">({{ $pInProgress }}%)</span></p>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            <span>En Revisión</span>
                                        </div>
                                        <p class="font-extrabold text-sm text-slate-900 dark:text-white mt-0.5">{{ $inReviewTasksCount }} <span class="text-[10px] font-normal text-slate-500">({{ $pInReview }}%)</span></p>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                            <span>No Iniciadas</span>
                                        </div>
                                        <p class="font-extrabold text-sm text-slate-900 dark:text-white mt-0.5">{{ $notStartedTasksCount }} <span class="text-[10px] font-normal text-slate-500">({{ $pNotStarted }}%)</span></p>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                            <span>Rechazadas</span>
                                        </div>
                                        <p class="font-extrabold text-sm text-slate-900 dark:text-white mt-0.5">{{ $rejectedTasksCount }} <span class="text-[10px] font-normal text-slate-500">({{ $pRejected }}%)</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Side Widgets (4 cols on lg) -->
                        <div class="lg:col-span-4 space-y-5">

                            <!-- Widget: Tareas Vencidas o de Atención Urgente -->
                            <div id="widget-urgencias" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft">
                                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="alert-octagon" class="w-4 h-4 {{ $overdueCount > 0 ? 'text-red-500' : 'text-emerald-500' }}"></i>
                                        <span>Bandeja de Urgencias</span>
                                    </h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $overdueCount > 0 ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' }}">
                                        {{ $overdueCount }} Vencida(s)
                                    </span>
                                </div>

                                @if($overdueCount === 0)
                                    <div class="py-6 text-center text-slate-400 dark:text-slate-500 text-xs flex flex-col items-center gap-1.5">
                                        <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                            <i data-lucide="check" class="w-5 h-5"></i>
                                        </div>
                                        <p class="font-bold text-slate-700 dark:text-slate-300 text-xs">¡Excelente! Todo al día</p>
                                        <span class="text-[11px]">No hay tareas vencidas pendientes en este proyecto.</span>
                                    </div>
                                @else
                                    <div class="space-y-2 max-h-48 overflow-y-auto pr-0.5">
                                        @foreach($overdueTasks->take(4) as $ot)
                                            <div class="p-2.5 rounded-xl border border-red-200/60 dark:border-red-900/40 bg-red-50/50 dark:bg-red-950/20 flex items-start justify-between gap-2">
                                                <div class="min-w-0 flex-1">
                                                    <h5 class="text-xs font-bold text-red-900 dark:text-red-200 truncate">{{ $ot->title }}</h5>
                                                    <span class="text-[10px] text-red-600 dark:text-red-400 block mt-0.5">
                                                        Venció: {{ $ot->end_date ?? $ot->start_date }}
                                                    </span>
                                                </div>
                                                <button onclick='openTaskModal(@json($ot->load(["assignedUsers", "subtasks"])))' class="p-1 text-red-600 hover:text-red-800 rounded bg-white dark:bg-slate-900 shrink-0 shadow-xs" title="Ver tarea">
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Widget: Incidencias Recientes -->
                            <div id="widget-incidencias" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft">
                                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500"></i>
                                        <span>Incidencias Activas</span>
                                    </h4>
                                    <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'incidencias']) }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                                        Ver todas
                                    </a>
                                </div>

                                @php $activeIncidents = $incidents->where('estado', '!=', 'resuelta')->take(3); @endphp
                                @if($activeIncidents->isEmpty())
                                    <p class="text-center py-5 text-slate-400 dark:text-slate-500 text-xs">No hay incidencias abiertas reportadas.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($activeIncidents as $inc)
                                            <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 space-y-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate flex-1">{{ $inc->titulo }}</h5>
                                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded uppercase shrink-0
                                                        {{ $inc->prioridad === 'urgente' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                                        {{ $inc->prioridad }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between text-[10px] text-slate-400">
                                                    <span>Por: {{ $inc->reporter ? $inc->reporter->name : 'Anon' }}</span>
                                                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ ucfirst($inc->estado) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Widget: Enlaces Rápidos del Proyecto -->
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft">
                                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-3">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="link" class="w-4 h-4 text-indigo-500"></i>
                                        <span>Enlaces del Proyecto</span>
                                    </h4>
                                    <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'enlaces']) }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">
                                        Ver todos
                                    </a>
                                </div>

                                @php $quickLinks = $activeProject->links->take(3); @endphp
                                @if($quickLinks->isEmpty())
                                    <p class="text-center py-4 text-slate-400 dark:text-slate-500 text-xs">No hay enlaces guardados.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($quickLinks as $ql)
                                            <a href="{{ $ql->url }}" target="_blank" class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-indigo-50/50 dark:hover:bg-slate-800 transition flex items-center justify-between gap-2 group">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate">{{ $ql->name }}</p>
                                                    <span class="text-[10px] text-slate-400 truncate block">{{ $ql->url }}</span>
                                                </div>
                                                <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 shrink-0"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Widget: Equipo del Proyecto -->
                            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 mb-3">
                                    <i data-lucide="users-2" class="w-4 h-4 text-emerald-500"></i>
                                    <span>Equipo del Proyecto ({{ $activeProject->members->count() }})</span>
                                </h4>

                                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($activeProject->members->take(5) as $mem)
                                        <div class="py-2 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0">
                                                    {{ $mem->initials }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $mem->name }}</p>
                                                    <span class="text-[10px] text-slate-400 truncate block">{{ $mem->role }}</span>
                                                </div>
                                            </div>
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            <!-- ========================================================================= -->
            <!-- VIEW 1: CALENDARIO                                                        -->
            <!-- ========================================================================= -->
            @elseif($activeView === 'calendario')
                {{-- 
                =========================================================================
                BLOQUE ORIGINAL COMENTADO (Distribución anterior: Lista a la Izquierda 7/12, Calendario a la Derecha 5/12)
                =========================================================================
                <div class="flex flex-col lg:flex-row gap-5 sm:gap-6">
                    <!-- Left List: Tasks list by date (anteriormente 7/12 a la izquierda) -->
                    <div class="lg:w-7/12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft space-y-4">
                        ...
                    </div>

                    <!-- Right FullCalendar Render (anteriormente 5/12 a la derecha) -->
                    <div class="lg:w-5/12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-3 sm:p-5 shadow-soft overflow-hidden">
                        <div id="calendar-container" class="w-full overflow-x-auto"></div>
                    </div>
                </div>
                =========================================================================
                --}}

                <div id="cal-main-container" class="flex flex-col lg:flex-row gap-5 sm:gap-6 lg:items-start">
                    <!-- Script de inicialización inmediata para aplicar estado de localStorage sin parpadeo -->
                    <script>
                        (function() {
                            try {
                                var isSplit = (localStorage.getItem('calendarCollapsed') ?? localStorage.getItem('calendar_collapsed')) === 'true';
                                if (isSplit) {
                                    document.documentElement.classList.add('cal-split-active');
                                }
                            } catch(e) {}
                        })();
                    </script>

                    <!-- IZQUIERDA: Calendario con soporte de Ancho Completo o Dividido con Lista de Tareas -->
                    <div id="cal-view-calendar" class="w-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft overflow-hidden flex flex-col transition-colors duration-150">
                        <!-- Cabecera del Calendario con botones [ Contraer ] y [ Expandir ] -->
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4 gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm sm:text-base font-bold text-slate-800 dark:text-white truncate">
                                        Calendario
                                    </h3>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <!-- Botón Contraer / Expandir vista de Calendario principal (conmutador con Lista de Tareas) -->
                                <button type="button" id="btn-toggle-calendar-split" onclick="toggleCalendarSplitView()"
                                    class="hidden lg:flex px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-semibold items-center gap-1.5 transition shadow-2xs active:scale-95 shrink-0"
                                    title="Contraer calendario para mostrar lista de tareas">
                                    <i id="btn-toggle-calendar-icon" data-lucide="columns-2" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                                    <span id="btn-toggle-calendar-text">Contraer</span>
                                </button>

                                {{-- 
                                <!-- Botón Expandir a ventana flotante / modal en pantalla completa (comentado para mantener un único botón Expandir visible) -->
                                <button type="button" onclick="expandCalendar()"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs active:scale-95 shrink-0"
                                    title="Expandir calendario a pantalla completa">
                                    <i data-lucide="maximize-2" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                                    <span>Expandir</span>
                                </button>
                                --}}
                            </div>
                        </div>

                        <!-- Contenedor del calendario en vista normal -->
                        <div id="calendar-normal-slot" class="w-full">
                            <div id="calendar-container" class="w-full overflow-x-auto"></div>
                        </div>
                    </div>

                    <!-- DERECHA: Lista de Tareas del Proyecto (5/12 en lg+) manteniendo exactamente su contenido y funcionamiento -->
                    <div id="cal-view-list" class="w-full lg:w-5/12 lg:hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 shadow-soft flex flex-col overflow-hidden">
                        <!-- Task List Header & Tabs -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4 gap-2 shrink-0">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                <span>Lista de Tareas del Proyecto</span>
                            </h3>

                            <!-- Tabs de Filtro: Tareas de Hoy vs Tareas del Mes -->
                            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs overflow-x-auto no-scrollbar max-w-full">
                                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => $activeView, 'task_filter' => 'hoy']) }}"
                                    class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $taskFilter === 'hoy' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>Tareas de Hoy</span>
                                    <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $taskFilter === 'hoy' ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $todayTasks->count() }}</span>
                                </a>
                                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => $activeView, 'task_filter' => 'mes']) }}"
                                    class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $taskFilter === 'mes' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                    <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                                    <span>Tareas del Mes</span>
                                    <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $taskFilter === 'mes' ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $monthTasks->count() }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Scrollable Tasks Container -->
                        <div class="flex-1 min-h-0 overflow-y-auto pr-1.5 space-y-4 max-h-[640px] lg:max-h-none">
                        @if($filteredTasks->isEmpty())
                            <div class="text-center py-12 text-slate-400 dark:text-slate-500 text-xs">
                                {{ $taskFilter === 'mes' ? 'No hay tareas registradas para este mes.' : 'No hay tareas pendientes para hoy.' }}
                            </div>
                        @else
                            @php
                                if ($taskFilter === 'mes') {
                                    $groupedTasks = $filteredTasks->sortBy(function($t) {
                                        if ($t->start_date) return \Carbon\Carbon::parse($t->start_date)->timestamp;
                                        if ($t->end_date) return \Carbon\Carbon::parse($t->end_date)->timestamp;
                                        return $t->created_at ? $t->created_at->timestamp : 0;
                                    })->groupBy(function($t) {
                                        if ($t->start_date) return \Carbon\Carbon::parse($t->start_date)->format('Y-m-d');
                                        if ($t->end_date) return \Carbon\Carbon::parse($t->end_date)->format('Y-m-d');
                                        return $t->created_at ? $t->created_at->format('Y-m-d') : 'sin_fecha';
                                    });
                                } else {
                                    $groupedTasks = collect(['todas' => $filteredTasks]);
                                }
                            @endphp

                            <div class="space-y-6">
                                @foreach($groupedTasks as $dateKey => $groupTasks)
                                    <div>
                                        @if($taskFilter === 'mes')
                                            <div class="flex items-center gap-2 pb-2 mb-3 border-b border-slate-200 dark:border-slate-800">
                                                <i data-lucide="calendar" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                                    @if($dateKey === 'sin_fecha')
                                                        Sin Fecha Definida
                                                    @else
                                                        {{ ucfirst(\Carbon\Carbon::parse($dateKey)->locale('es')->isoFormat('dddd D \d\e MMMM, YYYY')) }}
                                                        @if(\Carbon\Carbon::parse($dateKey)->isToday())
                                                            <span class="text-[9px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold px-1.5 py-0.2 rounded ml-1">Hoy</span>
                                                        @endif
                                                    @endif
                                                </h4>
                                                <span class="text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full ml-auto">
                                                    {{ $groupTasks->count() }} tarea(s)
                                                </span>
                                            </div>
                                        @endif

                                        <div class="space-y-3">
                                            @foreach($groupTasks as $t)
                                                <div class="group p-3 sm:p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-800 transition shadow-xs space-y-2.5">
                                                    <!-- Fila Superior: Checkbox + Título + Descripción + Badges (Ancho Completo) -->
                                                    <div class="flex items-start gap-2.5 min-w-0">
                                                        <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="mt-0.5 shrink-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="{{ $t->status === 'finalizado' ? 'no_iniciado' : ($t->needs_review && !$user->hasFullAccess() ? 'en_revision' : 'finalizado') }}">
                                                            <input type="checkbox" onchange="this.form.submit()" {{ $t->status === 'finalizado' ? 'checked' : '' }}
                                                                class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer">
                                                        </form>

                                                        <div class="min-w-0 flex-1">
                                                            @php
                                                                $isTodayTask = $t->status !== 'finalizado' && !$t->isOverdue() && (
                                                                    ($t->start_date && \Carbon\Carbon::parse($t->start_date)->isToday()) ||
                                                                    ($t->end_date && \Carbon\Carbon::parse($t->end_date)->isToday()) ||
                                                                    (!$t->start_date && !$t->end_date && $t->created_at && $t->created_at->isToday())
                                                                );
                                                                $dueDateStr = null;
                                                                if ($t->end_date) {
                                                                    $dueDateStr = \Carbon\Carbon::parse($t->end_date)->format('d/m/Y') . ($t->end_time ? ' a las ' . substr($t->end_time, 0, 5) : '');
                                                                } elseif ($t->start_date) {
                                                                    $dueDateStr = \Carbon\Carbon::parse($t->start_date)->format('d/m/Y') . ($t->start_time ? ' a las ' . substr($t->start_time, 0, 5) : '');
                                                                }
                                                            @endphp
                                                            <h4 class="text-xs sm:text-sm font-semibold break-normal [overflow-wrap:anywhere] leading-snug {{ $t->status === 'finalizado' ? 'line-through text-slate-400 dark:text-slate-500' : ($t->isOverdue() ? 'text-red-600 dark:text-red-400 font-bold' : ($isTodayTask ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-800 dark:text-slate-100')) }}">
                                                                {{ $t->title }}
                                                            </h4>

                                                            @if($t->description)
                                                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mt-1 break-normal [overflow-wrap:anywhere]">{{ $t->description }}</p>
                                                            @endif

                                                            <!-- Badges row -->
                                                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                                                @if($t->isOverdue())
                                                                    <span class="text-[10px] bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                                                        <i data-lucide="alert-circle" class="w-3 h-3 text-red-600"></i>
                                                                        <span>Vencida: {{ $dueDateStr }}</span>
                                                                    </span>
                                                                @elseif($dueDateStr)
                                                                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                                                        <i data-lucide="calendar" class="w-3 h-3 text-indigo-500"></i>
                                                                        <span>{{ $dueDateStr }}</span>
                                                                    </span>
                                                                @endif

                                                                @if($t->module)
                                                                    <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                                                        <i data-lucide="box" class="w-3 h-3"></i>
                                                                        <span>{{ $t->module->name }}</span>
                                                                    </span>
                                                                @endif

                                                                <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full
                                                                    {{ $t->priority === 'urgente' || $t->priority === 'alta' ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-200/50 dark:border-red-900/40' : ($t->priority === 'media' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200/50 dark:border-amber-900/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300') }}">
                                                                    {{ $t->priority }}
                                                                </span>

                                                                @if($t->needs_review)
                                                                    <span class="text-[10px] bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                                                        <i data-lucide="search" class="w-3 h-3"></i>
                                                                        <span>Revisión</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Fila Inferior: Acciones Rápidas (Distribución limpia y equilibrada) -->
                                                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-2 task-bottom-row-expandable">
                                                        <!-- Selector de estado rápido y badge de estado -->
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            @if($user->hasFullAccess())
                                                                <div class="flex items-center space-x-1 bg-slate-100 dark:bg-slate-800 p-0.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                                                    @foreach([
                                                                        ['id' => 'no_iniciado', 'title' => 'No Iniciado', 'icon' => 'circle', 'active' => 'bg-slate-300 text-slate-800 dark:bg-slate-600 dark:text-white', 'inactive' => 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'],
                                                                        ['id' => 'en_proceso', 'title' => 'En Proceso', 'icon' => 'clock', 'active' => 'bg-amber-500 text-white', 'inactive' => 'text-slate-400 hover:text-amber-500'],
                                                                        ['id' => 'finalizado', 'title' => 'Finalizado', 'icon' => 'check-circle', 'active' => 'bg-emerald-500 text-white', 'inactive' => 'text-slate-400 hover:text-emerald-500'],
                                                                    ] as $stBtn)
                                                                        <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="inline">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <input type="hidden" name="status" value="{{ $stBtn['id'] }}">
                                                                            <button type="submit" title="{{ $stBtn['title'] }}"
                                                                                class="w-6 h-6 rounded-md flex items-center justify-center transition {{ $t->status === $stBtn['id'] ? $stBtn['active'] : $stBtn['inactive'] }}">
                                                                                <i data-lucide="{{ $stBtn['icon'] }}" class="w-3.5 h-3.5"></i>
                                                                            </button>
                                                                        </form>
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md 
                                                                {{ $t->status === 'finalizado' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300' : ($t->status === 'en_proceso' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' : ($t->status === 'en_revision' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300' : ($t->status === 'rechazado' ? 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'))) }}">
                                                                {{ str_replace('_', ' ', ucfirst($t->status)) }}
                                                            </span>
                                                        </div>

                                                        <!-- Acciones: Comentarios, Editar, Desactivar y Eliminar -->
                                                        <div class="flex items-center gap-1 ml-auto shrink-0">
                                                            <button type="button" onclick="openCommentsModal({{ $t->id }}, this.getAttribute('data-title'))" data-title="{{ $t->title }}" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-1 transition" title="Comentarios">
                                                                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                                                <span id="task-comment-count-{{ $t->id }}" class="text-[11px] font-semibold {{ $t->comments_count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">{{ $t->comments_count }}</span>
                                                            </button>

                                                            @if(!$user->isHelper())
                                                                <button onclick='openTaskModal(@json($t->load(["assignedUsers", "subtasks"])))' class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Editar tarea">
                                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                                <form method="POST" action="{{ route('tasks.destroy', $t->id) }}" class="inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Eliminar tarea">
                                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Subtasks List inline -->
                                                    @if($t->subtasks->count() > 0)
                                                        <div class="pl-7 pt-1 space-y-1">
                                                            @foreach($t->subtasks as $sub)
                                                                <div class="flex items-center gap-2 text-[11px]">
                                                                    <form method="POST" action="{{ route('subtasks.toggle', $sub->id) }}">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="checkbox" onchange="this.form.submit()" {{ $sub->status === 'finalizado' ? 'checked' : '' }}
                                                                            class="w-3.5 h-3.5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer">
                                                                    </form>
                                                                    <span class="{{ $sub->status === 'finalizado' ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-600 dark:text-slate-300' }}">
                                                                        {{ $sub->title }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        </div>
                    </div>
                </div>

            <!-- ========================================================================= -->
            <!-- VIEW 2: KANBAN                                                            -->
            <!-- ========================================================================= -->
            @elseif($activeView === 'kanban')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-start max-w-[1600px] mx-auto pb-6">
                    @php
                        $columns = [
                            ['id' => 'no_iniciado', 'title' => 'No Iniciado', 'border' => 'border-slate-400 dark:border-slate-600', 'badgeBg' => 'bg-slate-200 dark:bg-slate-700', 'badgeText' => 'text-slate-700 dark:text-slate-300', 'icon' => 'circle'],
                            ['id' => 'en_proceso', 'title' => 'En Proceso', 'border' => 'border-amber-500', 'badgeBg' => 'bg-amber-100 dark:bg-amber-950/70', 'badgeText' => 'text-amber-800 dark:text-amber-300', 'icon' => 'clock'],
                            ['id' => 'en_revision', 'title' => 'En Revisión', 'border' => 'border-purple-500', 'badgeBg' => 'bg-purple-100 dark:bg-purple-950/70', 'badgeText' => 'text-purple-800 dark:text-purple-300', 'icon' => 'search'],
                            ['id' => 'rechazado', 'title' => 'Rechazado', 'border' => 'border-red-500', 'badgeBg' => 'bg-red-100 dark:bg-red-950/70', 'badgeText' => 'text-red-800 dark:text-red-300', 'icon' => 'x-circle'],
                            ['id' => 'finalizado', 'title' => 'Finalizado', 'border' => 'border-emerald-500', 'badgeBg' => 'bg-emerald-100 dark:bg-emerald-950/70', 'badgeText' => 'text-emerald-800 dark:text-emerald-300', 'icon' => 'check-circle'],
                        ];
                    @endphp

                    @foreach($columns as $col)
                        @php $colTasks = $filteredTasks->where('status', $col['id']); @endphp
                        <div id="kanban-col-{{ $col['id'] }}" data-kanban-col="{{ $col['id'] }}" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, '{{ $col['id'] }}')"
                            class="bg-slate-100/90 dark:bg-slate-900/80 rounded-2xl border-t-4 {{ $col['border'] }} border-x border-b border-slate-200/80 dark:border-slate-800 flex flex-col max-h-[75vh] transition-all duration-200 shadow-soft kanban-column">
                            
                            <!-- Column Header -->
                            <div class="p-3 sm:p-3.5 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0 bg-white/40 dark:bg-slate-900/40 rounded-t-xl">
                                <div class="flex items-center space-x-2">
                                    <i data-lucide="{{ $col['icon'] }}" class="w-4 h-4 {{ $col['badgeText'] }}"></i>
                                    <h3 class="font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-200">{{ $col['title'] }}</h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $col['badgeBg'] }} {{ $col['badgeText'] }}">
                                        {{ $colTasks->count() }}
                                    </span>
                                </div>
                                @if(!$user->isHelper())
                                    <button onclick="openTaskModal(null, '{{ $col['id'] }}')"
                                        class="p-1 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white dark:hover:bg-slate-800 transition" title="Agregar tarea en {{ $col['title'] }}">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Cards Scrollable Area -->
                            <div class="p-2 sm:p-2.5 space-y-2.5 overflow-y-auto flex-1 no-scrollbar">
                                @if($colTasks->isEmpty())
                                    <div class="text-center py-8 text-slate-400 dark:text-slate-500 text-xs italic">
                                        Sin tareas
                                    </div>
                                @else
                                    @foreach($colTasks as $t)
                                        <div draggable="true" ondragstart="handleDragStart(event, '{{ $t->id }}')"
                                            class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200/90 dark:border-slate-700/80 p-3 sm:p-3.5 space-y-2.5 shadow-soft hover:shadow-card dark:hover:shadow-card-dark transition cursor-grab active:cursor-grabbing group">
                                            
                                            <div class="flex items-start justify-between gap-1.5">
                                                <h4 class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 flex-1 leading-snug">
                                                    {{ $t->title }}
                                                    @if($t->isOverdue())
                                                        <span class="text-[9px] bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 font-bold px-1.5 py-0.5 rounded ml-1 uppercase inline-block">Vencida</span>
                                                    @elseif($t->start_date && \Carbon\Carbon::parse($t->start_date)->isToday())
                                                        <span class="text-[9px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold px-1.5 py-0.5 rounded ml-1 uppercase inline-block">Para Hoy</span>
                                                    @endif
                                                </h4>
                                                <div class="flex items-center space-x-1 shrink-0 task-actions-hover">
                                                    <button type="button" onclick="openCommentsModal({{ $t->id }}, this.getAttribute('data-title'))" data-title="{{ $t->title }}" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-0.5" title="Comentarios">
                                                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                                        <span id="task-comment-count-kanban-{{ $t->id }}" class="text-[10px] font-semibold {{ $t->comments_count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}">{{ $t->comments_count }}</span>
                                                    </button>
                                                    @if(!$user->isHelper())
                                                        <button onclick='openTaskModal(@json($t->load(["assignedUsers", "subtasks"])))' class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded hover:bg-slate-100 dark:hover:bg-slate-700" title="Editar tarea">
                                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('tasks.destroy', $t->id) }}" class="inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100 dark:hover:bg-slate-700" title="Eliminar tarea">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($t->description)
                                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ $t->description }}</p>
                                            @endif

                                            <!-- Badges: Needs Review, Module, Start Date, End Date -->
                                            @if($t->needs_review || $t->module || $t->start_date || $t->end_date)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if($t->needs_review)
                                                        <span class="text-[10px] bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-800/60 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="search" class="w-3 h-3"></i>
                                                            <span>Requiere revisión</span>
                                                        </span>
                                                    @endif
                                                    @if($t->module)
                                                        <span class="text-[10px] bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="box" class="w-3 h-3 text-indigo-500"></i>
                                                            <span>{{ $t->module->name }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->start_date)
                                                        <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/60 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                                            <span>{{ $t->start_date }}{{ $t->start_time ? ' '.$t->start_time : '' }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->recurrence && $t->recurrence !== 'ninguna')
                                                        <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="repeat" class="w-3 h-3 text-indigo-500"></i>
                                                            <span>{{ ucfirst($t->recurrence) }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->end_date)
                                                        <span class="text-[10px] bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-800/60 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="flag" class="w-3 h-3"></i>
                                                            <span>{{ $t->end_date }}{{ $t->end_time ? ' '.$t->end_time : '' }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->isOverdue())
                                                        @php
                                                            $dueOverdueStr = null;
                                                            if ($t->end_date) {
                                                                $dueOverdueStr = \Carbon\Carbon::parse($t->end_date)->format('d/m/Y') . ($t->end_time ? ' '.$t->end_time : '');
                                                            } elseif ($t->start_date) {
                                                                $dueOverdueStr = \Carbon\Carbon::parse($t->start_date)->format('d/m/Y') . ($t->start_time ? ' '.$t->start_time : '');
                                                            }
                                                        @endphp
                                                        @if($dueOverdueStr)
                                                            <span class="text-[10px] bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-100 dark:border-red-900/60 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                                <i data-lucide="alert-triangle" class="w-3 h-3 text-red-600"></i>
                                                                <span>Debía: {{ $dueOverdueStr }}</span>
                                                            </span>
                                                        @endif
                                                    @endif
                                                    @if($t->status === 'finalizado' && $t->completed_at)
                                                        <span class="text-[10px] bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60 px-2 py-0.5 rounded-full font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                                                            <span>Fin: {{ $t->completed_at->format('d/m/Y H:i') }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- Review Actions -->
                                            @if($t->status === 'en_revision' && $user->hasFullAccess())
                                                <div class="flex items-center gap-1.5 pt-1">
                                                    <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="flex-1">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="finalizado">
                                                        <button type="submit" class="w-full py-1 text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg transition flex items-center justify-center space-x-1">
                                                            <i data-lucide="check" class="w-3 h-3"></i>
                                                            <span>Aprobar</span>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="flex-1">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="rechazado">
                                                        <button type="submit" class="w-full py-1 text-[11px] font-semibold bg-red-50 dark:bg-red-950/60 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-lg transition flex items-center justify-center space-x-1">
                                                            <i data-lucide="x" class="w-3 h-3"></i>
                                                            <span>Rechazar</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            <!-- Subtasks Progress Bar -->
                                            @if($t->subtasks->count() > 0)
                                                @php
                                                    $stTotal = $t->subtasks->count();
                                                    $stDone = $t->subtasks->where('status', 'finalizado')->count();
                                                    $stPct = round(($stDone / $stTotal) * 100);
                                                @endphp
                                                <div class="space-y-1 pt-1">
                                                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                                                        <span class="flex items-center gap-1">
                                                            <i data-lucide="list-todo" class="w-3 h-3 text-indigo-400"></i>
                                                            Subtareas {{ $stDone }}/{{ $stTotal }}
                                                        </span>
                                                        <span>{{ $stPct }}%</span>
                                                    </div>
                                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $stPct }}%"></div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Card Footer: Priority Badge + Member Avatars + Quick Status Select -->
                                            <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-2">
                                                <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-md shrink-0
                                                    {{ $t->priority === 'urgente' || $t->priority === 'alta' ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 border border-red-200/50 dark:border-red-900/40' : ($t->priority === 'media' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/40' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600') }}">
                                                    {{ $t->priority }}
                                                </span>

                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <div class="flex items-center -space-x-1.5 shrink-0">
                                                        @foreach($t->assignedUsers as $u)
                                                            <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-[8px] flex items-center justify-center border border-white dark:border-slate-800" title="{{ $u->name }}">
                                                                {{ $u->initials }}
                                                            </span>
                                                        @endforeach
                                                    </div>

                                                    <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="inline">
                                                        @csrf @method('PATCH')
                                                        <select name="status" onchange="this.form.submit()" class="text-[11px] bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-200 py-0.5 px-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                            <option value="no_iniciado" {{ $t->status === 'no_iniciado' ? 'selected' : '' }}>No Iniciado</option>
                                                            <option value="en_proceso" {{ $t->status === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                                            @if($t->needs_review)
                                                                <option value="en_revision" {{ $t->status === 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                                                            @endif
                                                            @if(!$t->needs_review || $user->hasFullAccess())
                                                                <option value="rechazado" {{ $t->status === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                                                <option value="finalizado" {{ $t->status === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                                            @endif
                                                        </select>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            <!-- ========================================================================= -->
            <!-- VIEW 3: INCIDENCIAS                                                       -->
            <!-- ========================================================================= -->
            @elseif($activeView === 'incidencias')
                <div class="space-y-6 max-w-[1600px] mx-auto">


                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                        @php
                            $incCols = [
                                ['id' => 'abierta', 'title' => 'Abiertas', 'border' => 'border-red-500', 'badge' => 'bg-red-100 dark:bg-red-950/70 text-red-700 dark:text-red-300'],
                                ['id' => 'en_proceso', 'title' => 'En Proceso', 'border' => 'border-amber-500', 'badge' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300'],
                                ['id' => 'resuelta', 'title' => 'Resueltas', 'border' => 'border-emerald-500', 'badge' => 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300'],
                            ];
                        @endphp

                        @foreach($incCols as $icol)
                            @php $incItems = $incidents->where('estado', $icol['id']); @endphp
                            <div id="inc-col-{{ $icol['id'] }}" data-incident-col="{{ $icol['id'] }}" class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 border-t-4 {{ $icol['border'] }} shadow-soft space-y-3.5">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ $icol['title'] }}</h4>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $icol['badge'] }}">{{ $incItems->count() }}</span>
                                </div>

                                <div class="space-y-3">
                                    @if($incItems->isEmpty())
                                        <p class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs italic">Sin incidencias en esta columna</p>
                                    @else
                                        @foreach($incItems as $inc)
                                            <div class="p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 space-y-2">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h5 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white break-words flex-1 min-w-0">{{ $inc->titulo }}</h5>
                                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase shrink-0
                                                        {{ $inc->prioridad === 'urgente' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                                                        {{ $inc->prioridad }}
                                                    </span>
                                                </div>
                                                @if($inc->descripcion)
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 break-words">{{ $inc->descripcion }}</p>
                                                @endif
                                                <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-2 text-[10px] text-slate-400">
                                                    <span class="truncate max-w-[180px]">Reportado: <strong>{{ $inc->reporter ? $inc->reporter->name : 'Anónimo' }}</strong></span>
                                                    <form method="POST" action="{{ route('incidents.updateStatus', $inc->id) }}">
                                                        @csrf @method('PATCH')
                                                        <select name="estado" onchange="this.form.submit()" class="text-[10px] bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded px-1.5 py-0.5 text-slate-700 dark:text-slate-200">
                                                            <option value="abierta" {{ $inc->estado === 'abierta' ? 'selected' : '' }}>Abierta</option>
                                                            <option value="en_proceso" {{ $inc->estado === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                                            <option value="resuelta" {{ $inc->estado === 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                                                        </select>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            <!-- ========================================================================= -->
            <!-- VIEW 4: ENLACES DE PROYECTO                                               -->
            <!-- ========================================================================= -->
            @elseif($activeView === 'enlaces')
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-6 shadow-soft space-y-4 max-w-[1600px] mx-auto">


                    @php
                        $visibleLinks = $user->isHelper()
                            ? $activeProject->links->filter(fn($l) => $l->assignedUsers->contains('id', $user->id))
                            : $activeProject->links;
                    @endphp

                    @if($visibleLinks->isEmpty())
                        <div class="text-center py-12 text-slate-400 dark:text-slate-500 text-xs">
                            {{ $user->isHelper() ? 'No tienes enlaces asignados en este proyecto todavía.' : 'No hay enlaces configurados para este proyecto.' }}
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($visibleLinks as $link)
                                <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-800 transition flex flex-col justify-between gap-3 shadow-xs">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-start gap-1.5 leading-snug min-w-0 flex-1">
                                                <i data-lucide="external-link" class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5"></i>
                                                <span class="break-words">{{ $link->name }}</span>
                                            </h4>
                                            @if($user->hasFullAccess())
                                                <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))' title="Editar enlace" class="p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-200/60 dark:hover:bg-slate-700 rounded-lg transition shrink-0">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <a href="{{ $link->url }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline break-all block mt-2">
                                            {{ $link->url }}
                                        </a>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-2 text-[10px] text-slate-400">
                                        <span>Miembros asignados:</span>
                                        <div class="flex items-center gap-1 flex-wrap">
                                            @foreach($link->assignedUsers as $u)
                                                <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold text-[9px] flex items-center justify-center border border-white dark:border-slate-800" title="{{ $u->name }}">
                                                    {{ $u->initials }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- ========================================================================= -->
            <!-- VIEW 5: TAREAS PERIÓDICAS                                                 -->
            <!-- ========================================================================= -->
            @elseif($activeView === 'periodicas')
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 sm:p-6 shadow-soft space-y-4 max-w-[1600px] mx-auto">


                    @if($recurringTasks->isEmpty())
                        <div class="text-center py-12 text-slate-400 dark:text-slate-500 text-xs">
                            No hay tareas periódicas configuradas en este proyecto o módulo.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($recurringTasks as $rule)
                                <div class="p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-white dark:hover:bg-slate-800 transition flex flex-col justify-between gap-3 shadow-xs">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-snug flex-1">{{ $rule->title }}</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase shrink-0 bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                                {{ $rule->recurrence }}
                                            </span>
                                        </div>

                                        @if($rule->description)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">{{ $rule->description }}</p>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                            <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-md
                                                {{ $rule->priority === 'urgente' || $rule->priority === 'alta' ? 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                                {{ $rule->priority }}
                                            </span>

                                            @if($rule->module)
                                                <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-md">
                                                    {{ $rule->module->name }}
                                                </span>
                                            @endif

                                            @if($rule->needs_review)
                                                <span class="text-[10px] bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md">
                                                    Revisión requerida
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <form method="POST" action="{{ route('recurring-tasks.toggleActive', $rule->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded-full transition {{ $rule->is_active ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400' }}">
                                                    {{ $rule->is_active ? 'Activa' : 'Pausada' }}
                                                </button>
                                            </form>
                                        </div>

                                        @if(!$user->isHelper())
                                            <div class="flex items-center gap-1">
                                                <button onclick='openRecurringTaskModal(@json($rule->load(["assignedUsers", "subtasks"])))' class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition" title="Editar regla">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="{{ route('recurring-tasks.destroy', $rule->id) }}" onsubmit="return confirm('¿Eliminar esta regla periódica?');" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Eliminar regla">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            <!-- VIEW 6: PAPELERA DE TAREAS ELIMINADAS (30 DÍAS DE RETENCIÓN) -->
            @elseif($activeView === 'papelera')
                <div class="space-y-4 max-w-[1600px] mx-auto">
                    <!-- Cabecera de la Papelera con Información de Retención y Filtros -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="p-2 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 shrink-0">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </span>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                                        <span>Papelera de Tareas</span>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-950/70 text-red-700 dark:text-red-300">
                                            {{ $trashedTasks->count() }}
                                        </span>
                                    </h2>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros de Tiempo: Todas, Día, Semana, Mes -->
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl overflow-x-auto no-scrollbar shrink-0 text-xs">
                            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'todas']) }}"
                                class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $trashFilter === 'todas' ? 'bg-white dark:bg-slate-900 text-red-600 dark:text-red-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                <span>Todas</span>
                                <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $trashFilter === 'todas' ? 'bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">{{ $allTrashCount }}</span>
                            </a>
                            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'dia']) }}"
                                class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $trashFilter === 'dia' ? 'bg-white dark:bg-slate-900 text-red-600 dark:text-red-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span>Día</span>
                            </a>
                            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'semana']) }}"
                                class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $trashFilter === 'semana' ? 'bg-white dark:bg-slate-900 text-red-600 dark:text-red-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                                <span>Semana</span>
                            </a>
                            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'papelera', 'trash_filter' => 'mes']) }}"
                                class="px-3 py-1.5 rounded-lg font-semibold transition flex items-center gap-1.5 whitespace-nowrap {{ $trashFilter === 'mes' ? 'bg-white dark:bg-slate-900 text-red-600 dark:text-red-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                                <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                                <span>Mes</span>
                            </a>
                        </div>
                    </div>

                    <!-- Lista de Tareas en Papelera -->
                    @if($trashedTasks->isEmpty())
                        <div class="p-12 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-500 dark:text-red-400 flex items-center justify-center mb-3">
                                <i data-lucide="trash-2" class="w-7 h-7"></i>
                            </div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">La papelera está vacía</h3>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">
                                No hay tareas eliminadas en el período seleccionado. Las tareas eliminadas se conservarán durante 30 días antes de ser purgadas definitivamente.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                            @foreach($trashedTasks as $t)
                                @php
                                    $daysRemaining = max(0, 30 - intval(now()->diffInDays($t->deleted_at)));
                                @endphp
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft flex flex-col justify-between gap-3 relative transition hover:shadow-card dark:hover:shadow-card-dark">
                                    <div class="space-y-2">
                                        <!-- Top Row: Proyecto + Estado anterior + Días restantes -->
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @if($t->project)
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300">
                                                        {{ $t->project->name }}
                                                    </span>
                                                @endif
                                                @if($t->module)
                                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                                        {{ $t->module->name }}
                                                    </span>
                                                @endif
                                            </div>

                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $daysRemaining <= 5 ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 animate-pulse' : 'bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300' }}" title="Días restantes antes de eliminación definitiva">
                                                {{ $daysRemaining }} días restantes
                                            </span>
                                        </div>

                                        <!-- Título de la tarea -->
                                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 line-clamp-2" title="{{ $t->title }}">
                                            {{ $t->title }}
                                        </h3>

                                        @if($t->description)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                                                {{ $t->description }}
                                            </p>
                                        @endif

                                        <!-- Información adicional: Estado original, prioridad, fecha de eliminación -->
                                        <div class="pt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800/80">
                                            <!-- Badge de estado anterior -->
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold
                                                @if($t->status === 'finalizado') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300
                                                @elseif($t->status === 'en_proceso') bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300
                                                @elseif($t->status === 'en_revision') bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300
                                                @elseif($t->status === 'rechazado') bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300
                                                @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                                @endif">
                                                Estado: {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                            </span>

                                            <!-- Prioridad -->
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold
                                                @if($t->priority === 'urgente') bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300
                                                @elseif($t->priority === 'alta') bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300
                                                @elseif($t->priority === 'media') bg-yellow-50 dark:bg-yellow-950/60 text-yellow-700 dark:text-yellow-300
                                                @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400
                                                @endif">
                                                {{ ucfirst($t->priority) }}
                                            </span>

                                            <!-- Fecha de eliminación -->
                                            <div class="flex items-center gap-1 text-[10px] text-slate-400 dark:text-slate-500 ml-auto">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span>Eliminada {{ $t->deleted_at->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        <!-- Asignados -->
                                        @if($t->assignedUsers->isNotEmpty())
                                            <div class="flex items-center gap-1.5 pt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                                <span class="text-[10px] font-medium text-slate-400">Asignada a:</span>
                                                <div class="flex items-center -space-x-1.5 overflow-hidden">
                                                    @foreach($t->assignedUsers as $au)
                                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[9px] font-bold ring-1 ring-white dark:ring-slate-900" title="{{ $au->name }}">
                                                            {{ strtoupper(substr($au->name, 0, 2)) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Acciones: Restablecer / Eliminar Definitivamente -->
                                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                                        <form method="POST" action="{{ route('tasks.restore', $t->id) }}" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="py-1.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs flex items-center gap-1.5 transition shadow-xs active:scale-95">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                <span>Restablecer</span>
                                            </button>
                                        </form>

                                        @if($user->hasFullAccess())
                                            <form method="POST" action="{{ route('tasks.forceDestroy', $t->id) }}" onsubmit="return confirm('¿Eliminar definitivamente esta tarea? Esta acción no se puede deshacer.');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition" title="Eliminar">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

        @endif
    </div>

    <!-- Mobile Fixed Bottom Navigation Bar (PWA Experience Inspired by diseñosreferencia) -->
    <nav class="fixed md:hidden bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 pb-safe shadow-lg">
        <div class="flex items-center justify-around h-16 px-2">
            <!-- 1. Resumen -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'resumen']) }}"
                class="flex flex-col items-center justify-center flex-1 h-full min-h-[44px] transition py-1
                {{ $activeView === 'resumen' ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Resumen</span>
            </a>

            <!-- 2. Calendario -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'calendario']) }}"
                class="flex flex-col items-center justify-center flex-1 h-full min-h-[44px] transition py-1
                {{ $activeView === 'calendario' ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="calendar-days" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Calendario</span>
            </a>

            <!-- 3. Kanban Tablero -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'kanban']) }}"
                class="flex flex-col items-center justify-center flex-1 h-full min-h-[44px] transition py-1
                {{ $activeView === 'kanban' ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="kanban" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Tablero</span>
            </a>

            <!-- 4. Incidencias -->
            <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'incidencias']) }}"
                class="flex flex-col items-center justify-center flex-1 h-full min-h-[44px] transition py-1 relative
                {{ $activeView === 'incidencias' ? 'text-red-600 dark:text-red-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Incidencias</span>
                @if($incidents->where('estado', '!=', 'resuelta')->count() > 0)
                    <span class="absolute top-2 right-4 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-900"></span>
                @endif
            </a>

            <!-- 5. Menú Más (Opciones del header que no caben en la barra inferior) -->
            <button type="button" onclick="toggleMoreMobileMenu()" id="btn-mobile-more"
                class="flex flex-col items-center justify-center flex-1 h-full min-h-[44px] transition py-1
                {{ in_array($activeView, ['enlaces', 'periodicas', 'papelera']) ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}">
                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                <span class="text-[10px] mt-1">Más</span>
            </button>
        </div>
    </nav>

    <!-- Mobile "Más" Bottom Sheet Modal -->
    <div id="mobile-more-menu" class="fixed inset-0 z-50 hidden" onclick="if(event.target === this) toggleMoreMobileMenu()">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" onclick="toggleMoreMobileMenu()"></div>
        <div class="fixed bottom-0 left-0 right-0 z-51 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-t-2xl p-4 pb-safe shadow-2xl max-h-[85vh] overflow-y-auto">
            <div class="w-12 h-1 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto mb-3"></div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i data-lucide="grid" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                    <span>Más Opciones</span>
                </h3>
                <button type="button" onclick="toggleMoreMobileMenu()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <!-- 1. Opción: Resumen -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'resumen']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'resumen' ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-500 text-blue-700 dark:text-blue-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 shrink-0">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Resumen</div>
                    </div>
                </a>

                <!-- 2. Opción: Calendario -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'calendario']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'calendario' ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-500 text-blue-700 dark:text-blue-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 shrink-0">
                        <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Calendario</div>
                    </div>
                </a>

                <!-- 3. Opción: Tablero Kanban -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'kanban']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'kanban' ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-500 text-blue-700 dark:text-blue-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 shrink-0">
                        <i data-lucide="kanban" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Tablero</div>
                    </div>
                </a>

                <!-- 4. Opción: Incidencias -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'incidencias']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'incidencias' ? 'bg-red-50 dark:bg-red-950/60 border-red-500 text-red-700 dark:text-red-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/60 text-red-600 dark:text-red-400 shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Incidencias</div>
                    </div>
                </a>

                <!-- 5. Opción: Enlaces -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'enlaces']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'enlaces' ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-500 text-blue-700 dark:text-blue-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 shrink-0">
                        <i data-lucide="link" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Enlaces</div>
                    </div>
                </a>

                <!-- 6. Opción: Tareas Periódicas (Admin/Dev) -->
                @if($user->isAdmin())
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'periodicas']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'periodicas' ? 'bg-blue-50 dark:bg-blue-950/60 border-blue-500 text-blue-700 dark:text-blue-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 shrink-0">
                        <i data-lucide="repeat" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Periódicas</div>
                    </div>
                </a>
                @endif

                <!-- 7. Opción: Papelera -->
                <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'module_id' => $activeModuleId, 'view' => 'papelera']) }}"
                    class="flex items-center gap-3 p-3 rounded-xl border transition {{ $activeView === 'papelera' ? 'bg-red-50 dark:bg-red-950/60 border-red-500 text-red-700 dark:text-red-300 font-bold' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/60 text-red-600 dark:text-red-400 shrink-0">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Papelera</div>
                    </div>
                </a>

                <!-- 8. Opción: Menú Lateral / Proyectos -->
                <button type="button" onclick="toggleMoreMobileMenu(); toggleSidebar();"
                    class="flex items-center gap-3 p-3 rounded-xl border bg-slate-50 dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-left">
                    <div class="p-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 shrink-0">
                        <i data-lucide="folder" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-semibold truncate">Proyectos</div>
                    </div>
                </button>
            </div>
        </div>
    </div>

</main>

<!-- ========================================================================= -->
<!-- MODALS                                                                    -->
<!-- ========================================================================= -->

@if($activeView === 'calendario')
<!-- Modal: Calendario Expandido (Vista Grande Superpuesta) -->
<div id="calendarModal" onclick="if(event.target === this) closeCalendarExpand()" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4 md:p-6 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl w-full max-w-[96vw] xl:max-w-[94vw] h-[92vh] max-h-[94vh] border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden">
        <!-- Cabecera del Modal Expandido -->
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-900/80 shrink-0">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base tracking-tight truncate">
                            Calendario Completo
                        </h3>
                        @if($activeProject)
                            <span class="hidden sm:inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                                <i data-lucide="folder" class="w-3 h-3"></i>
                                <span class="truncate max-w-[150px]">{{ $activeProject->name }}</span>
                            </span>
                        @endif
                    </div>
                    
                </div>
            </div>

            <!-- Botón para Cerrar la vista expandida (solo botón X para cerrar) -->
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="closeCalendarExpand()" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Cerrar">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Área de contenido del Calendario Expandido -->
        <div id="calendar-expanded-slot" class="flex-1 min-h-0 p-3 sm:p-5 overflow-y-auto"></div>
    </div>
</div>
@endif

<!-- 1. Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/60">
            <h3 id="taskModalTitle" class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Nueva Tarea</h3>
            <button onclick="closeModal('taskModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="taskForm" method="POST" action="{{ route('tasks.store') }}" class="px-4 sm:px-6 pb-4 sm:pb-5 max-h-[calc(92vh-60px)] overflow-y-auto">
            @csrf
            <input type="hidden" id="task_method" name="_method" value="POST">
            <input type="hidden" name="project_id" value="{{ $activeProject ? $activeProject->id : '' }}">

            <div class="flex flex-col md:flex-row gap-4 pt-4">
                <!-- Left column -->
                <div class="w-full md:w-8/12 space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Título de la Tarea *</label>
                        <input type="text" id="task_title" name="title" required autofocus
                            placeholder="¿Qué hay que hacer?"
                            class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-900 dark:text-white outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <textarea id="task_description" name="description" rows="3"
                            placeholder="Detalles opcionales de la tarea..."
                            class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-900 dark:text-white outline-none transition resize-none"></textarea>
                    </div>

                    <!-- Needs review checkbox -->
                    <label class="flex items-start gap-2.5 px-3.5 py-2.5 bg-purple-50/60 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 rounded-xl cursor-pointer">
                        <input type="checkbox" id="task_needs_review" name="needs_review" value="1"
                            class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-purple-600 focus:ring-purple-500 accent-purple-600">
                        <span class="text-xs text-slate-700 dark:text-slate-300">
                            <span class="font-bold text-purple-900 dark:text-purple-200">Esta tarea necesita revisión</span><br>
                            <span class="text-slate-500 dark:text-slate-400">Antes de finalizar deberá pasar por "En Revisión" y ser aprobada por un Administrador.</span>
                        </span>
                    </label>

                    <!-- Subtasks section -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2 flex items-center gap-1.5">
                            <i data-lucide="list-todo" class="w-4 h-4 text-indigo-500"></i>
                            Subtareas
                            <span id="subtask-counter" class="text-[10px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full font-bold hidden"></span>
                        </label>

                        <!-- Subtask list -->
                        <ul id="subtask-list" class="space-y-1 mb-2 max-h-36 overflow-y-auto pr-0.5"></ul>
                        <div id="subtask-inputs"></div>

                        <!-- Add subtask input -->
                        <div class="flex gap-2">
                            <input type="text" id="subtask_input"
                                placeholder="Nueva subtarea... (Enter para agregar)"
                                class="flex-1 px-3 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs text-slate-900 dark:text-white outline-none">
                            <button type="button" onclick="addSubtask()"
                                class="shrink-0 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition flex items-center shadow-xs">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right column: status, priority, dates, assignees -->
                <div class="w-full md:w-4/12 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Módulo del Proyecto</label>
                        <select id="task_module_id" name="module_id"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs text-slate-900 dark:text-white outline-none">
                            <option value="">Sin Módulo (General)</option>
                            @if($activeProject)
                                @foreach($activeProject->modules as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
                        <select id="task_status" name="status"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs text-slate-900 dark:text-white outline-none">
                            <option value="no_iniciado">No Iniciado</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="en_revision">En Revisión</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Prioridad</label>
                        <select id="task_priority" name="priority"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl focus:ring-2 focus:ring-indigo-500 text-xs text-slate-900 dark:text-white outline-none">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>

                    <!-- Fecha y Hora de Inicio -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha y Hora de Inicio</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="date" id="task_start_date" name="start_date"
                                class="w-full px-2.5 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white outline-none">
                            <input type="time" id="task_start_time" name="start_time"
                                class="w-full px-2.5 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white outline-none">
                        </div>
                    </div>

                    <!-- End Date toggle -->
                    <div id="end-date-toggle-btn">
                        <button type="button" onclick="showEndDate()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center space-x-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Agregar fecha de fin</span>
                        </button>
                    </div>

                    <div id="end-date-section" class="hidden p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Fecha y Hora de Fin</label>
                            <button type="button" onclick="hideEndDate()" class="text-[11px] font-semibold text-red-500 hover:text-red-600 flex items-center space-x-1">
                                <i data-lucide="x" class="w-3 h-3"></i>
                                <span>Quitar</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="date" id="task_end_date" name="end_date"
                                class="w-full px-2 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg text-xs text-slate-900 dark:text-white outline-none">
                            <input type="time" id="task_end_time" name="end_time"
                                class="w-full px-2 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg text-xs text-slate-900 dark:text-white outline-none">
                        </div>
                    </div>

                    <!-- Asignar Usuarios -->
                    @if($activeProject)
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Asignar a Miembros</label>
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl max-h-32 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-800">
                            @foreach($activeProject->members as $u)
                                <label class="flex items-center space-x-2 px-2.5 py-1.5 text-xs cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/60">
                                    <input type="checkbox" name="assigned_user_ids[]" value="{{ $u->id }}" class="task-user-checkbox rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                                    <span class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-[8px] font-bold flex items-center justify-center shrink-0">
                                        {{ $u->initials }}
                                    </span>
                                    <span class="text-slate-700 dark:text-slate-200 truncate">{{ $u->name }}</span>
                                    <span class="text-slate-400 text-[10px] shrink-0">({{ $u->role }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 mt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeModal('taskModal')" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition text-center">Cancelar</button>
                <button type="submit" id="btnSaveTaskPerUser" name="create_per_user" value="1" title="Crea 1 tarea independiente para cada usuario seleccionado" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl transition shadow-xs inline-flex items-center justify-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Para cada usuario</span>
                </button>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition shadow-xs text-center">Guardar Tarea</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Project Modal -->
<div id="projectModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl max-w-xl w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 id="projectModalTitle" class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Nuevo Proyecto</h3>
            <button onclick="closeModal('projectModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="projectForm" method="POST" action="{{ route('projects.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" id="project_method" name="_method" value="POST">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre del Proyecto *</label>
                <input type="text" id="project_name" name="name" required class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Miembros con Acceso</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs">
                    @foreach($allUsers as $u)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 p-1 rounded-lg">
                            <input type="checkbox" name="member_user_ids[]" value="{{ $u->id }}" onchange="renderProjectLinks()" class="proj-user-checkbox rounded border-slate-300 dark:border-slate-600 text-indigo-600 accent-indigo-600">
                            <span class="truncate text-slate-700 dark:text-slate-200">{{ $u->name }} <span class="text-slate-400 text-[10px]">({{ $u->role }})</span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Enlaces del Proyecto -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="link" class="w-4 h-4 text-[#0f172a] dark:text-slate-300"></i>
                    Enlaces y Recursos del Proyecto
                </label>

                <!-- Formulario agregar enlace -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <input type="text" id="link_name_input" placeholder="Nombre (Ej. Repositorio, Figma...)" class="w-full sm:flex-1 px-3 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    <input type="text" id="link_url_input" placeholder="URL (Ej. https://...)" class="w-full sm:flex-1 px-3 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 focus:outline-none">
                    <button type="button" onclick="addProjectLink()" class="w-full sm:w-auto px-3.5 py-1.5 bg-[#0f172a] hover:bg-slate-800 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1 transition shrink-0">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Agregar</span>
                    </button>
                </div>

                <!-- Lista de enlaces agregados -->
                <div id="project-links-list" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>
                <div id="project-links-hidden-inputs"></div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeModal('projectModal')" class="w-full sm:w-auto py-2 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs text-center transition">Cancelar</button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-[#0f172a] hover:bg-slate-800 text-white font-semibold rounded-xl text-xs text-center shadow-xs transition">Guardar Proyecto</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Module Modal -->
<div id="moduleModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl max-w-sm w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 id="moduleModalTitle" class="text-sm font-bold text-slate-900 dark:text-white">Nuevo Módulo</h3>
            <button onclick="closeModal('moduleModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="moduleForm" method="POST" action="{{ route('modules.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" id="module_method" name="_method" value="POST">
            <input type="hidden" id="module_project_id" name="project_id" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre del Módulo *</label>
                <input type="text" id="module_name" name="name" required class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900 focus:outline-none">
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeModal('moduleModal')" class="w-full sm:w-auto py-2 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs text-center">Cancelar</button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-[#0f172a] hover:bg-slate-800 text-white font-semibold rounded-xl text-xs text-center shadow-xs">Guardar Módulo</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Incident Modal -->
<div id="incidentModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl max-w-md w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500"></i>
                <span>Reportar Incidencia</span>
            </h3>
            <button onclick="closeModal('incidentModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form method="POST" action="{{ route('incidents.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="project_id" value="{{ $activeProject ? $activeProject->id : '' }}">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Título de la Incidencia *</label>
                <input type="text" name="titulo" required class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                <textarea name="descripcion" rows="3" class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Prioridad</label>
                    <select name="prioridad" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="abierta" selected>Abierta</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="resuelta">Resuelta</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeModal('incidentModal')" class="w-full sm:w-auto py-2 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs text-center">Cancelar</button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl text-xs text-center shadow-xs">Guardar Incidencia</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Users List Modal -->
<div id="usersListModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl max-w-lg w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                <span>Gestión de Usuarios del Equipo</span>
            </h3>
            <button onclick="closeModal('usersListModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="userForm" method="POST" action="{{ route('users.store') }}" class="p-3.5 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl space-y-3">
            @csrf
            <input type="hidden" id="user_method" name="_method" value="POST">

            <div class="flex items-center justify-between gap-2">
                <h4 id="userFormTitle" class="text-xs font-bold text-slate-800 dark:text-slate-200">Agregar Nuevo Usuario</h4>
                <button type="button" id="btnCancelUserEdit" onclick="resetUserForm()" class="text-[10px] text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-semibold hidden flex items-center gap-1 shrink-0">
                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                    <span>Cancelar edición</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input type="text" id="user_name" name="name" placeholder="Nombre completo" required class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-lg text-xs text-slate-900 dark:text-white">
                <input type="email" id="user_email" name="email" placeholder="Correo electrónico" required class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-lg text-xs text-slate-900 dark:text-white">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input type="password" id="user_password" name="password" placeholder="Contraseña" required class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-lg text-xs text-slate-900 dark:text-white">
                <select id="user_role" name="role" required class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 rounded-lg text-xs text-slate-900 dark:text-white">
                    <option value="Admin">Admin</option>
                    <option value="Desarrollador">Desarrollador</option>
                    <option value="Ayudante">Ayudante</option>
                </select>
            </div>
            <button type="submit" id="btnSubmitUser" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-xl shadow-xs transition">Crear Usuario</button>
        </form>

        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($allUsers as $u)
                <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0">
                            {{ $u->initials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $u->name }}</p>
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <p class="text-[10px] text-slate-400 truncate">{{ $u->email }}</p>
                                <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" id="user-clave-{{ $u->id }}" data-clave="{{ $u->clave ?? '' }}" data-visible="false">
                                        {{ $u->clave ? '••••••••' : '(sin clave)' }}
                                    </span>
                                    @if($u->clave)
                                        <button type="button" onclick="toggleUserPassword({{ $u->id }})" class="p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition" title="Mostrar/Ocultar contraseña">
                                            <span id="user-clave-icon-{{ $u->id }}"><i data-lucide="eye" class="w-3 h-3"></i></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 self-end sm:self-auto shrink-0">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $u->role }}</span>
                        <button type="button" onclick='editUserInModal(@json($u))' class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Editar usuario">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                        @if(Auth::id() !== $u->id)
                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar a {{ $u->name }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Eliminar usuario">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 6. Recurring Task Modal -->
<div id="recurringTaskModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center hidden p-3 sm:p-4">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-200 dark:border-slate-800 max-h-[92vh] flex flex-col">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/60 shrink-0">
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2" id="recurringTaskModalTitle">
                <i data-lucide="repeat" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 dark:text-indigo-400"></i>
                <span>Nueva Tarea Periódica</span>
            </h3>
            <button onclick="closeModal('recurringTaskModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="recurringTaskForm" method="POST" action="{{ route('recurring-tasks.store') }}" class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
            @csrf
            <input type="hidden" id="recurring_task_method" name="_method" value="POST">
            <input type="hidden" name="project_id" value="{{ $activeProject ? $activeProject->id : '' }}">

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Título de la Tarea Periódica *</label>
                <input type="text" id="rec_title" name="title" required placeholder="Ej: Revisión diaria de servidores..."
                    class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                <textarea id="rec_description" name="description" rows="2" placeholder="Detalles de la tarea..."
                    class="w-full px-3.5 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Periodicidad / Frecuencia *</label>
                    <select id="rec_recurrence" name="recurrence" required
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="diaria">Diaria (Todos los días)</option>
                        <option value="semanal">Semanal (Cada semana)</option>
                        <option value="mensual">Mensual (Una vez al mes)</option>
                        <option value="anual">Anual (Una vez al año)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Prioridad *</label>
                    <select id="rec_priority" name="priority" required
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Módulo del Proyecto</label>
                    <select id="rec_module_id" name="module_id"
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Sin Módulo (General)</option>
                        @if($activeProject)
                            @foreach($activeProject->modules as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Inicio Base *</label>
                    <input type="date" id="rec_start_date" name="start_date" required
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hora Inicio (Opcional)</label>
                    <input type="time" id="rec_start_time" name="start_time"
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Hora Fin (Opcional)</label>
                    <input type="time" id="rec_end_time" name="end_time"
                        class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <!-- Needs review checkbox -->
            <label class="flex items-start gap-2.5 px-3.5 py-2.5 bg-purple-50/60 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 rounded-xl cursor-pointer">
                <input type="checkbox" id="rec_needs_review" name="needs_review" value="1"
                    class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-purple-600 focus:ring-purple-500 accent-purple-600">
                <span class="text-xs text-slate-700 dark:text-slate-300">
                    <span class="font-bold text-purple-900 dark:text-purple-200">Requiere revisión al completarse</span><br>
                    <span class="text-slate-500 dark:text-slate-400">Las tareas generadas deberán pasar por revisión de Admin.</span>
                </span>
            </label>

            <!-- Asignar Usuarios -->
            @if($activeProject)
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Asignar Usuarios</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl max-h-32 overflow-y-auto">
                    @foreach($activeProject->members as $u)
                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 p-1 rounded-lg">
                            <input type="checkbox" name="assigned_user_ids[]" value="{{ $u->id }}" class="rec-user-checkbox rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                            <span class="truncate">{{ $u->name }} <span class="text-[10px] text-slate-400">({{ $u->role }})</span></span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Subtareas Plantilla -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subtareas Plantilla</label>
                <ul id="rec-subtask-list" class="space-y-1 mb-2 max-h-32 overflow-y-auto pr-0.5"></ul>
                <div id="rec-subtask-inputs"></div>
                <div class="flex gap-2">
                    <input type="text" id="rec_subtask_input" placeholder="Nueva subtarea plantilla..."
                        class="flex-1 px-3 py-1.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white outline-none">
                    <button type="button" onclick="addRecSubtask()" class="px-3 py-1.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-500 shrink-0 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                <button type="button" onclick="closeModal('recurringTaskModal')" class="w-full sm:w-auto py-2 px-4 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-center transition">
                    Cancelar
                </button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-xs text-center transition">
                    Guardar Regla Periódica
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 7. Comments Modal -->
<div id="commentsModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[88vh] border border-slate-200 dark:border-slate-800">
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/60 dark:bg-slate-900/60 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                <div class="p-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 shrink-0">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm truncate" id="commentsModalTaskTitle">Comentarios</h3>
                    <p class="text-[11px] text-slate-400 truncate">Colaboración y notas de seguimiento de la tarea.</p>
                </div>
            </div>
            <button onclick="closeModal('commentsModal')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition shrink-0 p-1"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <div id="commentsList" class="p-3.5 sm:p-5 overflow-y-auto flex-1 space-y-3 min-h-[140px]">
            <div class="text-center py-8 text-slate-400 dark:text-slate-500 text-xs">Cargando comentarios...</div>
        </div>

        <form id="commentForm" onsubmit="submitComment(event)" class="p-3 sm:p-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/60 flex flex-col sm:flex-row gap-2 sm:items-end shrink-0">
            <input type="hidden" id="comment_task_id" value="">
            <div class="w-full sm:flex-1">
                <textarea id="comment_input" rows="2" required placeholder="Escribe un comentario..." class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
            </div>
            <button type="submit" id="btnSubmitComment" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1 shrink-0 shadow-xs">
                <span>Comentar</span>
                <i data-lucide="send" class="w-3.5 h-3.5"></i>
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function initSidebarState() {
        if (window.innerWidth >= 768) {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            const saved = localStorage.getItem('sidebar_collapsed') ?? localStorage.getItem('sidebarCollapsed');
            if (saved === 'false') {
                sidebar.classList.remove('sidebar-collapsed');
            } else {
                sidebar.classList.add('sidebar-collapsed');
            }
        }
    }
    initSidebarState();

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) return;

        const isDesktop = window.innerWidth >= 768;

        if (isDesktop) {
            // Modo Escritorio: Colapsar / Desplegar liberando espacio
            const isCurrentlyCollapsed = sidebar.classList.contains('sidebar-collapsed');
            if (isCurrentlyCollapsed) {
                sidebar.classList.remove('sidebar-collapsed');
                try {
                    localStorage.setItem('sidebar_collapsed', 'false');
                    localStorage.setItem('sidebarCollapsed', 'false');
                } catch (e) {}
            } else {
                sidebar.classList.add('sidebar-collapsed');
                try {
                    localStorage.setItem('sidebar_collapsed', 'true');
                    localStorage.setItem('sidebarCollapsed', 'true');
                } catch (e) {}
            }

            // Asegurar que el backdrop no quede visible en escritorio
            if (backdrop && !backdrop.classList.contains('hidden')) {
                backdrop.classList.add('hidden');
            }

            // Notificar redimensionamiento para actualizar componentes (FullCalendar, gráficos)
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 310);
        } else {
            // Modo Celular / Tablet: Menú lateral con backdrop (comportamiento modal sin alterar)
            const isClosed = sidebar.classList.contains('-translate-x-full');
            if (isClosed) {
                sidebar.classList.remove('-translate-x-full');
                if (backdrop) backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                if (backdrop) backdrop.classList.add('hidden');
            }
        }
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            const backdrop = document.getElementById('sidebar-backdrop');
            if (backdrop && !backdrop.classList.contains('hidden')) {
                backdrop.classList.add('hidden');
            }
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full');
                const saved = localStorage.getItem('sidebar_collapsed') ?? localStorage.getItem('sidebarCollapsed');
                if (saved === 'false') {
                    sidebar.classList.remove('sidebar-collapsed');
                } else {
                    sidebar.classList.add('sidebar-collapsed');
                }
            }
        } else {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                const backdrop = document.getElementById('sidebar-backdrop');
                if (!backdrop || backdrop.classList.contains('hidden')) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        }
    });

    function toggleMoreMobileMenu() {
        const menu = document.getElementById('mobile-more-menu');
        if (!menu) return;
        menu.classList.toggle('hidden');
        if (!menu.classList.contains('hidden') && window.lucide) {
            lucide.createIcons();
        }
    }

    function toggleProjectAccordion(projId) {
        const content = document.getElementById('acc-content-' + projId);
        const icon = document.getElementById('acc-icon-' + projId);
        if (content) content.classList.toggle('hidden');
        if (icon) icon.classList.toggle('-rotate-90');
    }

    function openModal(id) {
        if (window.innerWidth < 768) {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
            if (backdrop && !backdrop.classList.contains('hidden')) {
                backdrop.classList.add('hidden');
            }
        }
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }

    // ---- End Date Section ----
    function showEndDate() {
        document.getElementById('end-date-toggle-btn').classList.add('hidden');
        document.getElementById('end-date-section').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function hideEndDate() {
        document.getElementById('end-date-section').classList.add('hidden');
        document.getElementById('end-date-toggle-btn').classList.remove('hidden');
        document.getElementById('task_end_date').value = '';
        document.getElementById('task_end_time').value = '';
    }

    // ---- Subtask Management ----
    let subtasks = [];

    function renderSubtasks() {
        const list = document.getElementById('subtask-list');
        const inputs = document.getElementById('subtask-inputs');
        const counter = document.getElementById('subtask-counter');

        list.innerHTML = '';
        inputs.innerHTML = '';

        if (subtasks.length === 0) {
            counter.classList.add('hidden');
            return;
        }

        const done = subtasks.filter(s => s.status === 'finalizado').length;
        counter.textContent = done + '/' + subtasks.length;
        counter.classList.remove('hidden');

        subtasks.forEach((st, idx) => {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5';
            li.innerHTML = `
                <input type="checkbox" ${st.status === 'finalizado' ? 'checked' : ''}
                    onchange="toggleSubtaskStatus(${idx})"
                    class="h-3.5 w-3.5 shrink-0 rounded border-slate-300 dark:border-slate-600 text-indigo-600 accent-indigo-500">
                <span class="flex-1 text-xs truncate ${st.status === 'finalizado' ? 'line-through text-slate-400 dark:text-slate-500' : 'text-slate-700 dark:text-slate-200'}">${escapeHtml(st.title)}</span>
                <button type="button" onclick="removeSubtask(${idx})"
                    class="shrink-0 p-0.5 text-slate-400 hover:text-red-500 rounded transition" title="Eliminar subtarea">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>`;
            list.appendChild(li);

            // Hidden form inputs
            ['title', 'status'].forEach(key => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = `subtasks[${idx}][${key}]`;
                inp.value = key === 'title' ? st.title : st.status;
                inputs.appendChild(inp);
            });
            if (st.id) {
                const idInp = document.createElement('input');
                idInp.type = 'hidden';
                idInp.name = `subtasks[${idx}][id]`;
                idInp.value = st.id;
                inputs.appendChild(idInp);
            }
        });
    }

    function addSubtask() {
        const input = document.getElementById('subtask_input');
        const title = input.value.trim();
        if (!title) return;
        subtasks.push({ title: title, status: 'no_iniciado' });
        input.value = '';
        renderSubtasks();
    }

    function removeSubtask(idx) {
        subtasks.splice(idx, 1);
        renderSubtasks();
    }

    function toggleSubtaskStatus(idx) {
        subtasks[idx].status = subtasks[idx].status === 'finalizado' ? 'no_iniciado' : 'finalizado';
        renderSubtasks();
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const stInput = document.getElementById('subtask_input');
        if (stInput) {
            stInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); addSubtask(); }
            });
        }
    });

    // ---- Task Modal ----
    function openTaskModal(task = null, initialStatus = 'no_iniciado', defaultStartDate = null) {
        const form = document.getElementById('taskForm');
        const methodInput = document.getElementById('task_method');
        const titleHeading = document.getElementById('taskModalTitle');
        const btnPerUser = document.getElementById('btnSaveTaskPerUser');

        subtasks = [];
        hideEndDate();

        if (task) {
            if (btnPerUser) btnPerUser.classList.add('hidden');
            form.action = '/tasks/' + task.id;
            methodInput.value = 'PUT';
            titleHeading.textContent = 'Editar Tarea';
            document.getElementById('task_title').value = task.title || '';
            document.getElementById('task_description').value = task.description || '';
            document.getElementById('task_module_id').value = task.module_id || '';
            document.getElementById('task_priority').value = task.priority || 'media';
            document.getElementById('task_status').value = task.status || 'no_iniciado';
            document.getElementById('task_start_date').value = task.start_date || '';
            document.getElementById('task_start_time').value = task.start_time || '';
            document.getElementById('task_needs_review').checked = !!task.needs_review;

            if (task.end_date) {
                document.getElementById('task_end_date').value = task.end_date;
                document.getElementById('task_end_time').value = task.end_time || '';
                showEndDate();
            }

            const assignedIds = (task.assigned_users || task.assignedUsers || []).map(u => typeof u === 'object' ? u.id : parseInt(u));
            document.querySelectorAll('.task-user-checkbox').forEach(cb => {
                cb.checked = assignedIds.includes(parseInt(cb.value));
            });

            subtasks = (task.subtasks || []).map(st => ({
                id: st.id,
                title: st.title,
                status: st.status || 'no_iniciado'
            }));
            renderSubtasks();

        } else {
            if (btnPerUser) btnPerUser.classList.remove('hidden');
            form.action = '{{ route('tasks.store') }}';
            methodInput.value = 'POST';
            titleHeading.textContent = 'Nueva Tarea';
            form.reset();
            document.getElementById('task_status').value = initialStatus || 'no_iniciado';
            const todayStr = new Date().toISOString().split('T')[0];
            document.getElementById('task_start_date').value = defaultStartDate || todayStr;
            renderSubtasks();
        }

        openModal('taskModal');
        if (window.lucide) lucide.createIcons();
    }

    // ---- Task Comments Modal & AJAX ----
    let currentCommentTaskId = null;

    function openCommentsModal(taskId, taskTitle) {
        currentCommentTaskId = taskId;
        document.getElementById('comment_task_id').value = taskId;
        document.getElementById('commentsModalTaskTitle').textContent = 'Comentarios: ' + taskTitle;
        document.getElementById('comment_input').value = '';
        
        const listEl = document.getElementById('commentsList');
        listEl.innerHTML = '<div class="text-center py-8 text-slate-400 dark:text-slate-500 text-xs">Cargando comentarios...</div>';
        
        openModal('commentsModal');

        fetch(`/tasks/${taskId}/comments`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderComments(data.comments);
            } else {
                listEl.innerHTML = '<div class="text-center py-6 text-red-500 text-xs">Error al cargar comentarios.</div>';
            }
        })
        .catch(err => {
            console.error(err);
            listEl.innerHTML = '<div class="text-center py-6 text-red-500 text-xs">Error de conexión.</div>';
        });
    }

    function renderComments(comments) {
        const listEl = document.getElementById('commentsList');
        if (!comments || comments.length === 0) {
            listEl.innerHTML = '<div class="text-center py-8 text-slate-400 dark:text-slate-500 text-xs flex flex-col items-center justify-center gap-1.5"><i data-lucide="message-square" class="w-6 h-6 text-slate-300 dark:text-slate-600"></i><span>No hay comentarios aún. ¡Sé el primero en comentar!</span></div>';
            if (window.lucide) lucide.createIcons();
            return;
        }

        listEl.innerHTML = comments.map(c => `
            <div class="pt-3 first:pt-0 border-b border-slate-100 dark:border-slate-800 pb-3 last:border-b-0 flex items-start justify-between gap-2.5 sm:gap-3 group">
                <div class="flex items-start gap-2.5 min-w-0 flex-1">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] flex items-center justify-center shrink-0 border border-indigo-200 dark:border-indigo-800 mt-0.5">
                        ${escapeHtml(c.user_initials)}
                    </div>
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">${escapeHtml(c.user_name)}</span>
                            <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-1.5 py-0.2 rounded font-medium">${escapeHtml(c.user_role)}</span>
                            <span class="text-[10px] text-slate-400" title="${c.created_at_formatted}">${c.created_at}</span>
                        </div>
                        <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed break-words">${escapeHtml(c.comment)}</p>
                    </div>
                </div>
                ${c.can_delete ? `
                    <button onclick="deleteComment(${c.id})" class="text-slate-300 hover:text-red-600 transition p-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 shrink-0" title="Eliminar comentario">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                ` : ''}
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();
    }

    function submitComment(e) {
        e.preventDefault();
        const taskId = document.getElementById('comment_task_id').value;
        const input = document.getElementById('comment_input');
        const comment = input.value.trim();
        if (!comment || !taskId) return;

        const btn = document.getElementById('btnSubmitComment');
        btn.disabled = true;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/tasks/${taskId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ comment })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                input.value = '';
                const taskTitle = document.getElementById('commentsModalTaskTitle').textContent.replace('Comentarios: ', '');
                openCommentsModal(taskId, taskTitle);
                updateCommentBadge(taskId, data.total_comments);
            } else {
                alert(data.error || 'Error al guardar comentario.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            console.error(err);
            alert('Error de red al publicar comentario.');
        });
    }

    function deleteComment(commentId) {
        if (!confirm('¿Eliminar este comentario?')) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const taskTitle = document.getElementById('commentsModalTaskTitle').textContent.replace('Comentarios: ', '');
                openCommentsModal(currentCommentTaskId, taskTitle);
                updateCommentBadge(currentCommentTaskId, data.total_comments);
            } else {
                alert(data.error || 'No se pudo eliminar el comentario.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red al eliminar el comentario.');
        });
    }

    function updateCommentBadge(taskId, count) {
        const listBadge = document.getElementById(`task-comment-count-${taskId}`);
        if (listBadge) {
            listBadge.textContent = count;
            listBadge.className = `text-[11px] font-semibold ${count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'}`;
        }
        const kanbanBadge = document.getElementById(`task-comment-count-kanban-${taskId}`);
        if (kanbanBadge) {
            kanbanBadge.textContent = count;
            kanbanBadge.className = `text-[10px] font-semibold ${count > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400'}`;
        }
    }

    // ---- HTML5 Drag and Drop for Kanban ----
    function handleDragStart(e, taskId) {
        e.dataTransfer.setData('text/plain', taskId);
        e.dataTransfer.effectAllowed = 'move';
        e.target.classList.add('opacity-40');
        setTimeout(() => e.target.classList.remove('opacity-40'), 0);
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const col = e.currentTarget;
        col.classList.add('ring-2', 'ring-indigo-400', 'bg-indigo-50/40', 'dark:bg-indigo-950/40');
    }

    function handleDragLeave(e) {
        const col = e.currentTarget;
        col.classList.remove('ring-2', 'ring-indigo-400', 'bg-indigo-50/40', 'dark:bg-indigo-950/40');
    }

    function handleDrop(e, newStatus) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.remove('ring-2', 'ring-indigo-400', 'bg-indigo-50/40', 'dark:bg-indigo-950/40');

        const taskId = e.dataTransfer.getData('text/plain');
        if (!taskId) return;

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.content : '';

        fetch('/tasks/' + taskId + '/status', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(data => { throw new Error(data.error || 'Error actualizando estado'); });
            }
            window.location.reload();
        })
        .catch(err => {
            alert(err.message || 'No se pudo mover la tarea');
        });
    }

    // ---- Project Link Management ----
    const allUsersList = @json($allUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role]));
    let projectLinks = [];
    let editingLinkIndex = null;

    function renderProjectLinks() {
        const listContainer = document.getElementById('project-links-list');
        const hiddenInputsContainer = document.getElementById('project-links-hidden-inputs');
        if (!listContainer || !hiddenInputsContainer) return;

        listContainer.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';

        if (projectLinks.length === 0) {
            listContainer.innerHTML = '<p class="text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 italic">Aún no hay enlaces agregados.</p>';
            return;
        }

        const selectedMemberIds = Array.from(document.querySelectorAll('.proj-user-checkbox:checked')).map(cb => parseInt(cb.value));

        projectLinks.forEach((link, idx) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 bg-slate-50 dark:bg-slate-800/60 space-y-2';

            if (editingLinkIndex === idx) {
                const editFormDiv = document.createElement('div');
                editFormDiv.className = 'space-y-2';
                editFormDiv.innerHTML = `
                    <div class="flex items-center justify-between pb-1 border-b border-slate-200 dark:border-slate-700">
                        <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                            <i data-lucide="pencil" class="w-3 h-3"></i>
                            Editar Enlace
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">Nombre del enlace</label>
                            <input type="text" id="edit_link_name_${idx}" value="${escapeHtml(link.name)}"
                                class="w-full px-2.5 py-1 border border-slate-300 dark:border-slate-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-0.5">URL del enlace</label>
                            <input type="text" id="edit_link_url_${idx}" value="${escapeHtml(link.url)}"
                                class="w-full px-2.5 py-1 border border-slate-300 dark:border-slate-600 rounded-lg text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-1.5 pt-1">
                        <button type="button" onclick="cancelProjectLinkEdit()" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg">
                            Cancelar
                        </button>
                        <button type="button" onclick="saveProjectLinkEdit(${idx})" class="px-2.5 py-1 text-[11px] font-semibold text-white bg-[#0f172a] hover:bg-slate-800 rounded-lg flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <span>Guardar</span>
                        </button>
                    </div>
                `;
                itemDiv.appendChild(editFormDiv);
            } else {
                const topRow = document.createElement('div');
                topRow.className = 'flex items-start justify-between gap-2';

                const infoDiv = document.createElement('div');
                infoDiv.className = 'min-w-0 flex items-start gap-2 flex-1';
                infoDiv.innerHTML = `
                    <i data-lucide="link" class="w-3.5 h-3.5 text-[#0f172a] dark:text-slate-400 shrink-0 mt-0.5"></i>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 break-words block leading-snug">${escapeHtml(link.name)}</span>
                        <a href="${escapeHtml(link.url)}" target="_blank" class="text-[11px] text-blue-600 dark:text-blue-400 hover:underline break-all block mt-0.5">${escapeHtml(link.url)}</a>
                    </div>
                `;

                const actionBtnsDiv = document.createElement('div');
                actionBtnsDiv.className = 'flex items-center gap-1 shrink-0';

                const editBtn = document.createElement('button');
                editBtn.type = 'button';
                editBtn.className = 'p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg transition';
                editBtn.title = 'Editar enlace';
                editBtn.innerHTML = '<i data-lucide="pencil" class="w-3.5 h-3.5"></i>';
                editBtn.onclick = () => startProjectLinkEdit(idx);

                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'p-1 text-slate-400 hover:text-red-600 rounded-lg transition';
                deleteBtn.title = 'Eliminar enlace';
                deleteBtn.innerHTML = '<i data-lucide="trash-2" class="w-3.5 h-3.5"></i>';
                deleteBtn.onclick = () => removeProjectLink(idx);

                actionBtnsDiv.appendChild(editBtn);
                actionBtnsDiv.appendChild(deleteBtn);

                topRow.appendChild(infoDiv);
                topRow.appendChild(actionBtnsDiv);
                itemDiv.appendChild(topRow);
            }

            const assignables = allUsersList.filter(u => selectedMemberIds.length === 0 || selectedMemberIds.includes(u.id));
            if (assignables.length > 0) {
                const assigneesDiv = document.createElement('div');
                assigneesDiv.className = 'flex flex-wrap items-center gap-1 pt-1.5 border-t border-slate-200/60 dark:border-slate-700/60';

                const label = document.createElement('span');
                label.className = 'text-[10px] font-semibold text-slate-400 mr-1';
                label.textContent = 'Asignar a:';
                assigneesDiv.appendChild(label);

                assignables.forEach(u => {
                    const isAssigned = (link.assigned_user_ids || []).includes(u.id);
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `text-[10px] px-2 py-0.5 rounded-full border transition font-medium ${
                        isAssigned
                            ? 'bg-indigo-600 border-indigo-600 text-white shadow-xs'
                            : 'bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-indigo-400'
                    }`;
                    btn.textContent = u.name;
                    btn.onclick = () => toggleLinkAssignee(idx, u.id);
                    assigneesDiv.appendChild(btn);
                });
                itemDiv.appendChild(assigneesDiv);
            }

            listContainer.appendChild(itemDiv);

            // Hidden form inputs
            const nameInp = document.createElement('input');
            nameInp.type = 'hidden';
            nameInp.name = `links[${idx}][name]`;
            nameInp.value = link.name;
            hiddenInputsContainer.appendChild(nameInp);

            const urlInp = document.createElement('input');
            urlInp.type = 'hidden';
            urlInp.name = `links[${idx}][url]`;
            urlInp.value = link.url;
            hiddenInputsContainer.appendChild(urlInp);

            (link.assigned_user_ids || []).forEach(uId => {
                const assignInp = document.createElement('input');
                assignInp.type = 'hidden';
                assignInp.name = `links[${idx}][assigned_user_ids][]`;
                assignInp.value = uId;
                hiddenInputsContainer.appendChild(assignInp);
            });
        });

        if (window.lucide) lucide.createIcons();
    }

    function startProjectLinkEdit(idx) {
        editingLinkIndex = idx;
        renderProjectLinks();
    }

    function cancelProjectLinkEdit() {
        editingLinkIndex = null;
        renderProjectLinks();
    }

    function saveProjectLinkEdit(idx) {
        const nameInp = document.getElementById(`edit_link_name_${idx}`);
        const urlInp = document.getElementById(`edit_link_url_${idx}`);
        if (!nameInp || !urlInp) return;

        const name = nameInp.value.trim();
        let url = urlInp.value.trim();

        if (!name || !url) {
            alert('Por favor, ingresa un nombre y una URL válidos.');
            return;
        }

        if (!/^https?:\/\//i.test(url)) {
            url = 'https://' + url;
        }

        projectLinks[idx].name = name;
        projectLinks[idx].url = url;
        editingLinkIndex = null;
        renderProjectLinks();
    }

    function addProjectLink() {
        const nameInp = document.getElementById('link_name_input');
        const urlInp = document.getElementById('link_url_input');
        const name = nameInp ? nameInp.value.trim() : '';
        let url = urlInp ? urlInp.value.trim() : '';

        if (!name || !url) return;

        if (!/^https?:\/\//i.test(url)) {
            url = 'https://' + url;
        }

        projectLinks.push({
            name: name,
            url: url,
            assigned_user_ids: []
        });

        if (nameInp) nameInp.value = '';
        if (urlInp) urlInp.value = '';
        renderProjectLinks();
    }

    function removeProjectLink(idx) {
        projectLinks.splice(idx, 1);
        if (editingLinkIndex === idx) {
            editingLinkIndex = null;
        } else if (editingLinkIndex > idx) {
            editingLinkIndex--;
        }
        renderProjectLinks();
    }

    function toggleLinkAssignee(linkIdx, userId) {
        if (!projectLinks[linkIdx].assigned_user_ids) {
            projectLinks[linkIdx].assigned_user_ids = [];
        }
        const arr = projectLinks[linkIdx].assigned_user_ids;
        const pos = arr.indexOf(userId);
        if (pos >= 0) {
            arr.splice(pos, 1);
        } else {
            arr.push(userId);
        }
        renderProjectLinks();
    }

    function openProjectModal(project = null) {
        const form = document.getElementById('projectForm');
        const methodInput = document.getElementById('project_method');
        const titleHeading = document.getElementById('projectModalTitle');
        editingLinkIndex = null;

        if (document.getElementById('link_name_input')) document.getElementById('link_name_input').value = '';
        if (document.getElementById('link_url_input')) document.getElementById('link_url_input').value = '';

        if (project) {
            form.action = '/projects/' + project.id;
            methodInput.value = 'PUT';
            titleHeading.textContent = 'Editar Proyecto';
            document.getElementById('project_name').value = project.name;
            const memberIds = (project.members || []).map(u => u.id);
            document.querySelectorAll('.proj-user-checkbox').forEach(cb => {
                cb.checked = memberIds.includes(parseInt(cb.value));
            });

            projectLinks = (project.links || []).map(l => ({
                name: l.name,
                url: l.url,
                assigned_user_ids: (l.assigned_users || l.assignedUsers || []).map(u => typeof u === 'object' ? u.id : parseInt(u))
            }));
        } else {
            form.action = '{{ route('projects.store') }}';
            methodInput.value = 'POST';
            titleHeading.textContent = 'Nuevo Proyecto';
            form.reset();
            projectLinks = [];
        }
        renderProjectLinks();
        openModal('projectModal');
        if (window.lucide) lucide.createIcons();
    }

    function openModuleModal(projectId, module = null) {
        const form = document.getElementById('moduleForm');
        const methodInput = document.getElementById('module_method');
        const titleHeading = document.getElementById('moduleModalTitle');
        document.getElementById('module_project_id').value = projectId;

        if (module) {
            form.action = '/modules/' + module.id;
            methodInput.value = 'PUT';
            titleHeading.textContent = 'Editar Módulo';
            document.getElementById('module_name').value = module.name;
        } else {
            form.action = '{{ route('modules.store') }}';
            methodInput.value = 'POST';
            titleHeading.textContent = 'Nuevo Módulo';
            document.getElementById('module_name').value = '';
        }
        openModal('moduleModal');
    }

    function openIncidentModal() {
        openModal('incidentModal');
    }

    function openUsersListModal() {
        resetUserForm();
        openModal('usersListModal');
    }

    function editUserInModal(user) {
        const form = document.getElementById('userForm');
        const methodInput = document.getElementById('user_method');
        const titleHeading = document.getElementById('userFormTitle');
        const submitBtn = document.getElementById('btnSubmitUser');
        const cancelBtn = document.getElementById('btnCancelUserEdit');
        const pwdInput = document.getElementById('user_password');

        if (!form) return;

        form.action = '/users/' + user.id;
        methodInput.value = 'PUT';
        titleHeading.textContent = 'Actualizar Usuario';
        submitBtn.textContent = 'Actualizar Usuario';
        if (cancelBtn) cancelBtn.classList.remove('hidden');

        document.getElementById('user_name').value = user.name || '';
        document.getElementById('user_email').value = user.email || '';
        document.getElementById('user_role').value = user.role || 'Admin';

        pwdInput.removeAttribute('required');
        pwdInput.value = '';
        pwdInput.placeholder = 'Nueva contraseña (opcional)';

        if (window.lucide) lucide.createIcons();
    }

    function toggleUserPassword(userId) {
        const span = document.getElementById('user-clave-' + userId);
        const iconSpan = document.getElementById('user-clave-icon-' + userId);
        if (!span) return;

        const realClave = span.getAttribute('data-clave') || '';
        const isVisible = span.getAttribute('data-visible') === 'true';

        if (isVisible) {
            span.textContent = realClave ? '••••••••' : '(sin clave)';
            span.setAttribute('data-visible', 'false');
            if (iconSpan) iconSpan.innerHTML = '<i data-lucide="eye" class="w-3 h-3"></i>';
        } else {
            span.textContent = realClave;
            span.setAttribute('data-visible', 'true');
            if (iconSpan) iconSpan.innerHTML = '<i data-lucide="eye-off" class="w-3 h-3"></i>';
        }

        if (window.lucide) lucide.createIcons();
    }

    function resetUserForm() {
        const form = document.getElementById('userForm');
        const methodInput = document.getElementById('user_method');
        const titleHeading = document.getElementById('userFormTitle');
        const submitBtn = document.getElementById('btnSubmitUser');
        const cancelBtn = document.getElementById('btnCancelUserEdit');
        const pwdInput = document.getElementById('user_password');

        if (!form) return;

        form.action = '{{ route('users.store') }}';
        methodInput.value = 'POST';
        titleHeading.textContent = 'Agregar Nuevo Usuario';
        submitBtn.textContent = 'Crear Usuario';
        if (cancelBtn) cancelBtn.classList.add('hidden');

        form.reset();
        pwdInput.setAttribute('required', 'required');
        pwdInput.placeholder = 'Contraseña';
    }

    // ---- Recurring Task Modal ----
    let recSubtasks = [];

    function renderRecSubtasks() {
        const list = document.getElementById('rec-subtask-list');
        const inputs = document.getElementById('rec-subtask-inputs');
        if (!list || !inputs) return;
        list.innerHTML = '';
        inputs.innerHTML = '';

        recSubtasks.forEach((st, idx) => {
            const li = document.createElement('li');
            li.className = 'flex items-center gap-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5';
            li.innerHTML = `
                <span class="flex-1 text-xs text-slate-700 dark:text-slate-300 truncate">${escapeHtml(st.title)}</span>
                <button type="button" onclick="removeRecSubtask(${idx})" class="text-slate-400 hover:text-red-500">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>`;
            list.appendChild(li);

            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = `subtasks[${idx}][title]`;
            inp.value = st.title;
            inputs.appendChild(inp);
        });
        if (window.lucide) lucide.createIcons();
    }

    function addRecSubtask() {
        const input = document.getElementById('rec_subtask_input');
        const val = input.value.trim();
        if (val) {
            recSubtasks.push({ title: val });
            input.value = '';
            renderRecSubtasks();
        }
    }

    function removeRecSubtask(idx) {
        recSubtasks.splice(idx, 1);
        renderRecSubtasks();
    }

    function openRecurringTaskModal(rule = null) {
        const form = document.getElementById('recurringTaskForm');
        const methodInput = document.getElementById('recurring_task_method');
        const titleHeading = document.getElementById('recurringTaskModalTitle');

        recSubtasks = [];

        if (rule) {
            form.action = '/recurring-tasks/' + rule.id;
            methodInput.value = 'PUT';
            titleHeading.innerHTML = '<i data-lucide="pencil" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i><span>Editar Tarea Periódica</span>';
            document.getElementById('rec_title').value = rule.title || '';
            document.getElementById('rec_description').value = rule.description || '';
            document.getElementById('rec_recurrence').value = rule.recurrence || 'diaria';
            document.getElementById('rec_priority').value = rule.priority || 'media';
            document.getElementById('rec_module_id').value = rule.module_id || '';
            document.getElementById('rec_start_date').value = rule.start_date || '';
            document.getElementById('rec_start_time').value = rule.start_time || '';
            document.getElementById('rec_end_time').value = rule.end_time || '';
            document.getElementById('rec_needs_review').checked = !!rule.needs_review;

            const assignedIds = (rule.assigned_users || rule.assignedUsers || []).map(u => typeof u === 'object' ? u.id : parseInt(u));
            document.querySelectorAll('.rec-user-checkbox').forEach(cb => {
                cb.checked = assignedIds.includes(parseInt(cb.value));
            });

            recSubtasks = (rule.subtasks || []).map(st => ({ title: st.title }));
            renderRecSubtasks();
        } else {
            form.action = '{{ route('recurring-tasks.store') }}';
            methodInput.value = 'POST';
            titleHeading.innerHTML = '<i data-lucide="repeat" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i><span>Nueva Tarea Periódica</span>';
            form.reset();
            document.getElementById('rec_start_date').value = new Date().toISOString().split('T')[0];
            renderRecSubtasks();
        }

        openModal('recurringTaskModal');
        if (window.lucide) lucide.createIcons();
    }

    // Map of all tasks for quick access by ID from calendar events
    const allTasksMap = {
        @foreach($tasks as $t)
            '{{ $t->id }}': @json($t->load(['assignedUsers', 'subtasks'])),
        @endforeach
    };

    // Control de modo de vista del Calendario (Ancho completo vs Dividido con Lista de Tareas)
    function isCalendarSplitMode() {
        try {
            const saved = localStorage.getItem('calendarCollapsed') ?? localStorage.getItem('calendar_collapsed');
            return saved === 'true';
        } catch (e) {
            return false;
        }
    }

    function setCalendarSplitMode(split) {
        try {
            localStorage.setItem('calendarCollapsed', split ? 'true' : 'false');
            localStorage.setItem('calendar_collapsed', split ? 'true' : 'false');
        } catch (e) {}

        applyCalendarViewMode(split);
    }

    function toggleCalendarSplitView() {
        const isSplit = isCalendarSplitMode();
        setCalendarSplitMode(!isSplit);
    }

    function applyCalendarViewMode(split) {
        const cal = document.getElementById('cal-view-calendar');
        const list = document.getElementById('cal-view-list');
        const btnText = document.getElementById('btn-toggle-calendar-text');
        const btnIcon = document.getElementById('btn-toggle-calendar-icon');
        const btnToggle = document.getElementById('btn-toggle-calendar-split');

        if (split) {
            document.documentElement.classList.add('cal-split-active');
            if (cal) {
                cal.classList.add('lg:w-7/12');
                cal.classList.remove('cal-view-full');
            }
            if (list) {
                list.classList.remove('lg:hidden');
            }
            if (btnText) btnText.textContent = 'Expandir';
            if (btnToggle) btnToggle.title = 'Expandir calendario a pantalla completa';
            if (btnIcon) btnIcon.setAttribute('data-lucide', 'maximize-2');
        } else {
            document.documentElement.classList.remove('cal-split-active');
            if (cal) {
                cal.classList.remove('lg:w-7/12');
                cal.classList.add('cal-view-full');
            }
            if (list) {
                list.classList.add('lg:hidden');
                list.style.height = '';
                list.style.maxHeight = '';
            }
            if (btnText) btnText.textContent = 'Contraer';
            if (btnToggle) btnToggle.title = 'Contraer calendario para mostrar lista de tareas';
            if (btnIcon) btnIcon.setAttribute('data-lucide', 'columns-2');
        }

        if (window.lucide) {
            lucide.createIcons();
        }

        // Forzar reflow sincrónico en cal para asegurar que el navegador aplique los estilos de layout inmediatamente
        if (cal) void cal.offsetWidth;

        const refreshCalendar = () => {
            if (window.calendarInstance) {
                if (split) {
                    window.calendarInstance.setOption('aspectRatio', 1.35);
                } else {
                    window.calendarInstance.setOption('aspectRatio', window.innerWidth >= 1280 ? 1.55 : 1.4);
                }
                window.calendarInstance.updateSize();
                window.calendarInstance.render();
            }

            if (split) {
                syncCalendarListHeight();
            }
        };

        // Recalcular y renderizar inmediatamente
        refreshCalendar();

        // Y en el siguiente ciclo de renderizado para garantizar dimensiones finales completas
        requestAnimationFrame(() => {
            refreshCalendar();
        });
    }

    // FullCalendar Initialization
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('calendar-container');
        if (calendarEl && window.FullCalendar) {
            const isInitialSplit = isCalendarSplitMode();
            window.calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 'auto',
                aspectRatio: isInitialSplit ? 1.35 : (window.innerWidth >= 1280 ? 1.55 : 1.4),
                firstDay: 1,
                selectable: true,
                displayEventTime: false,
                events: [
                    @foreach($tasks as $t)
                        @if($t->start_date && $t->recurrence !== 'diaria')
                            {
                                id: '{{ $t->id }}',
                                title: '{{ addslashes($t->title) }}',
                                start: '{{ \Carbon\Carbon::parse($t->start_date)->format('Y-m-d') }}',
                                allDay: true,
                                backgroundColor: '{{ $t->status === "finalizado" ? "#10b981" : ($t->isOverdue() ? "#ef4444" : ($t->status === "en_proceso" ? "#f59e0b" : "#6366f1")) }}',
                                borderColor: 'transparent'
                            },
                        @endif
                    @endforeach
                ],

                datesSet: function() {
                    syncCalendarListHeight();
                },
                dateClick: function(info) {
                    @if(!$user->isHelper())
                        openTaskModal(null, 'no_iniciado', info.dateStr);
                    @endif
                },
                eventClick: function(info) {
                    const taskId = info.event.id;
                    const task = allTasksMap[taskId];
                    if (task) {
                        openTaskModal(task);
                    }
                }
            });
            window.calendarInstance.render();
            applyCalendarViewMode(isInitialSplit);

            window.addEventListener('theme-toggled', () => {
                if (window.calendarInstance) {
                    window.calendarInstance.render();
                    setTimeout(syncCalendarListHeight, 50);
                }
            });

            const calCard = document.getElementById('cal-view-calendar');
            if (calCard) {
                if (window.ResizeObserver) {
                    let lastCalWidth = 0;
                    new ResizeObserver((entries) => {
                        syncCalendarListHeight();
                        for (const entry of entries) {
                            const currentWidth = Math.round(entry.contentRect.width);
                            if (currentWidth > 0 && Math.abs(currentWidth - lastCalWidth) >= 2) {
                                lastCalWidth = currentWidth;
                                if (window.calendarInstance && (!document.getElementById('calendarModal') || document.getElementById('calendarModal').classList.contains('hidden'))) {
                                    const isSplit = isCalendarSplitMode();
                                    window.calendarInstance.setOption('aspectRatio', isSplit ? 1.35 : (window.innerWidth >= 1280 ? 1.55 : 1.4));
                                    window.calendarInstance.updateSize();
                                    window.calendarInstance.render();
                                }
                            }
                        }
                    }).observe(calCard);
                }
                window.addEventListener('resize', () => {
                    syncCalendarListHeight();
                    if (window.calendarInstance && (!document.getElementById('calendarModal') || document.getElementById('calendarModal').classList.contains('hidden'))) {
                        const isSplit = isCalendarSplitMode();
                        window.calendarInstance.setOption('aspectRatio', isSplit ? 1.35 : (window.innerWidth >= 1280 ? 1.55 : 1.4));
                        window.calendarInstance.updateSize();
                        window.calendarInstance.render();
                    }
                });
                setTimeout(syncCalendarListHeight, 100);
                setTimeout(syncCalendarListHeight, 400);
            }
        }
    });

    // Sincronización vertical exacta entre el Calendario y la Lista de Tareas
    function syncCalendarListHeight() {
        if (window.innerWidth >= 1024) {
            const cal = document.getElementById('cal-view-calendar');
            const list = document.getElementById('cal-view-list');
            const modal = document.getElementById('calendarModal');
            if (cal && list && (!modal || modal.classList.contains('hidden'))) {
                if (list.classList.contains('lg:hidden') || !isCalendarSplitMode()) {
                    list.style.height = '';
                    list.style.maxHeight = '';
                    return;
                }
                const h = cal.offsetHeight;
                if (h > 100) {
                    list.style.height = h + 'px';
                    list.style.maxHeight = h + 'px';
                }
            }
        } else {
            const list = document.getElementById('cal-view-list');
            if (list) {
                list.style.height = '';
                list.style.maxHeight = '';
            }
        }
    }

    // Control de vista expandida del calendario
    function expandCalendar() {
        const modal = document.getElementById('calendarModal');
        const modalSlot = document.getElementById('calendar-expanded-slot');
        const calendarEl = document.getElementById('calendar-container');
        if (!modal || !modalSlot || !calendarEl) return;

        modalSlot.appendChild(calendarEl);
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        calendarEl.classList.add('h-full');

        if (window.calendarInstance) {
            window.calendarInstance.setOption('height', '100%');
            window.calendarInstance.updateSize();
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeCalendarExpand() {
        const modal = document.getElementById('calendarModal');
        const normalSlot = document.getElementById('calendar-normal-slot');
        const calendarEl = document.getElementById('calendar-container');
        if (!modal || !normalSlot || !calendarEl) return;

        normalSlot.appendChild(calendarEl);
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        calendarEl.classList.remove('h-full');

        if (window.calendarInstance) {
            const isSplit = isCalendarSplitMode();
            window.calendarInstance.setOption('height', 'auto');
            window.calendarInstance.setOption('aspectRatio', isSplit ? 1.35 : (window.innerWidth >= 1280 ? 1.55 : 1.4));
            window.calendarInstance.updateSize();
            window.calendarInstance.render();
            if (isSplit) {
                setTimeout(syncCalendarListHeight, 50);
            }
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('calendarModal');
            if (modal && !modal.classList.contains('hidden')) {
                const taskModal = document.getElementById('taskModal');
                if (taskModal && !taskModal.classList.contains('hidden')) {
                    return;
                }
                closeCalendarExpand();
            }
        }
    });

    // -------------------------------------------------------------
    // Mobile Contextual Sub-navigation Logic
    // -------------------------------------------------------------
    function selectMobileCalView(viewMode) {
        const calCol = document.getElementById('cal-view-calendar');
        const listCol = document.getElementById('cal-view-list');
        if (!calCol || !listCol) return;

        document.querySelectorAll('.cal-subnav-btn').forEach(btn => {
            btn.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'dark:border-indigo-500', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'dark:border-blue-500', 'font-bold');
            btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        });

        if (viewMode === 'calendar') {
            calCol.classList.remove('cal-panel-mobile-hidden');
            listCol.classList.add('cal-panel-mobile-hidden');
            const activeBtn = document.getElementById('subnav-cal-btn-calendar');
            if (activeBtn) {
                activeBtn.classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'dark:border-indigo-500', 'font-bold');
                activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
            }
            sessionStorage.setItem('cal_mobile_active_tab', 'calendar');
            if (window.calendarInstance) {
                setTimeout(() => {
                    window.calendarInstance.updateSize();
                }, 50);
            }
        } else {
            calCol.classList.add('cal-panel-mobile-hidden');
            listCol.classList.remove('cal-panel-mobile-hidden');
            let activeId = 'subnav-cal-btn-list';
            if (viewMode === 'hoy') activeId = 'subnav-cal-btn-hoy';
            if (viewMode === 'mes') activeId = 'subnav-cal-btn-mes';
            const activeBtn = document.getElementById(activeId);
            if (activeBtn) {
                activeBtn.classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'dark:border-indigo-500', 'font-bold');
                activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
            }
            sessionStorage.setItem('cal_mobile_active_tab', 'list');
        }
    }

    function filterMobileKanban(status) {
        document.querySelectorAll('.kanban-subnav-btn').forEach(btn => {
            btn.classList.remove('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'dark:border-indigo-500', 'border-blue-600', 'text-blue-600', 'dark:text-blue-400', 'dark:border-blue-500', 'font-bold');
            btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        });

        const activeBtn = document.getElementById('subnav-kanban-btn-' + status);
        if (activeBtn) {
            activeBtn.classList.add('border-indigo-600', 'text-indigo-600', 'dark:text-indigo-400', 'dark:border-indigo-500', 'font-bold');
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        }

        const cols = document.querySelectorAll('[data-kanban-col]');
        cols.forEach(col => {
            if (status === 'todos' || col.getAttribute('data-kanban-col') === status) {
                col.classList.remove('kanban-col-mobile-hidden');
            } else {
                col.classList.add('kanban-col-mobile-hidden');
            }
        });
        sessionStorage.setItem('kanban_mobile_active_col', status);
    }

    function filterMobileIncidents(status) {
        document.querySelectorAll('.inc-subnav-btn').forEach(btn => {
            btn.classList.remove('border-red-600', 'text-red-600', 'dark:text-red-400', 'dark:border-red-500', 'font-bold');
            btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        });

        const activeBtn = document.getElementById('subnav-inc-btn-' + status);
        if (activeBtn) {
            activeBtn.classList.add('border-red-600', 'text-red-600', 'dark:text-red-400', 'dark:border-red-500', 'font-bold');
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        }

        const cols = document.querySelectorAll('[data-incident-col]');
        cols.forEach(col => {
            if (status === 'todas' || col.getAttribute('data-incident-col') === status) {
                col.classList.remove('incident-col-mobile-hidden');
            } else {
                col.classList.add('incident-col-mobile-hidden');
            }
        });
        sessionStorage.setItem('inc_mobile_active_col', status);
    }

    // Initialize Mobile Sub-nav states on load
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }

        if (window.innerWidth < 1024) {
            @if($activeView === 'calendario')
                const urlParams = new URLSearchParams(window.location.search);
                const calTab = urlParams.get('cal_subview') || sessionStorage.getItem('cal_mobile_active_tab') || 'calendar';
                const filter = urlParams.get('task_filter');
                if (calTab === 'list' || filter) {
                    selectMobileCalView(filter || 'list');
                } else {
                    selectMobileCalView('calendar');
                }
            @elseif($activeView === 'kanban')
                const savedCol = sessionStorage.getItem('kanban_mobile_active_col') || 'todos';
                filterMobileKanban(savedCol);
            @elseif($activeView === 'incidencias')
                const savedInc = sessionStorage.getItem('inc_mobile_active_col') || 'todas';
                filterMobileIncidents(savedInc);
            @endif
        }
    });
</script>

<style>
/* Fila inferior de tarea: oculta completamente sin reservar espacio en escritorio, expandible en hover */
@media (hover: hover) and (pointer: fine) and (min-width: 768px) {
    .task-bottom-row-expandable {
        display: none !important;
    }
    .group:hover .task-bottom-row-expandable,
    .group:focus-within .task-bottom-row-expandable {
        display: flex !important;
        animation: taskBottomRowFade 0.15s ease-out;
    }
    .task-actions-hover {
        opacity: 0 !important;
        pointer-events: none !important;
        transition: opacity 0.15s ease-in-out;
    }
    .group:hover .task-actions-hover,
    .group:focus-within .task-actions-hover {
        opacity: 1 !important;
        pointer-events: auto !important;
    }
}

@keyframes taskBottomRowFade {
    from {
        opacity: 0;
        transform: translateY(-2px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (min-width: 1024px) {
    html.cal-split-active #cal-view-calendar {
        width: 58.333333% !important; /* lg:w-7/12 */
    }
    html.cal-split-active #cal-view-list {
        display: flex !important; /* visible in split mode */
    }
}

#cal-view-calendar.cal-view-full #calendar-container {
    min-height: 580px;
}

@media (max-width: 1023px) {
    .cal-panel-mobile-hidden {
        display: none !important;
    }
    .kanban-col-mobile-hidden {
        display: none !important;
    }
    .incident-col-mobile-hidden {
        display: none !important;
    }
}
</style>
@endpush
