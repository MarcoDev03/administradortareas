@extends('layouts.app')

@section('title', 'Gestor de Proyectos & Kanban - ' . ($activeProject ? $activeProject->name : 'Inicio'))

@section('content')

<!-- Mobile Sidebar Backdrop -->
<div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden"></div>

<!-- Sidebar Navigation -->
<aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-72 max-w-[85vw] bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 shrink-0">
    <!-- Header App Logo -->
    <div class="p-5 border-b border-slate-800 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-indigo-600 rounded-lg text-white">
                <i data-lucide="kanban" class="w-5 h-5"></i>
            </div>
            <h1 class="font-bold text-lg text-white tracking-wide">NegocioManager</h1>
        </div>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- New Project Button -->
    @if($user->hasFullAccess())
    <div class="p-4">
        <button onclick="openProjectModal()"
            class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg flex items-center justify-center space-x-2 transition-all shadow-md shadow-indigo-900/20 active:scale-95 text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nuevo Proyecto</span>
        </button>
    </div>
    @endif

    <!-- Current User Info -->
    <div class="px-4 pb-4">
        <div class="flex items-center justify-between bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-indigo-600/30 border border-indigo-500/40 text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0">
                    {{ $user->initials }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-200 truncate">{{ $user->name }}</p>
                    
                </div>
            </div>
            
        </div>
    </div>

    <!-- Section Buttons: Proyecto / Calendario Global -->
    <div class="px-4 pb-4 grid grid-cols-2 gap-2">
        <a href="{{ route('dashboard', ['project_id' => $activeProject ? $activeProject->id : '', 'view' => $activeView]) }}"
            class="py-2 px-2 rounded-lg text-xs font-medium flex items-center justify-center space-x-1.5 transition
            {{ request()->has('project_id') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-slate-200 hover:bg-slate-700' }}">
            <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
            <span>Proyectos</span>
        </a>
        <a href="{{ route('dashboard', ['view' => 'calendario']) }}"
            class="py-2 px-2 rounded-lg text-xs font-medium flex items-center justify-center space-x-1.5 transition
            {{ !request()->has('project_id') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-800 text-slate-400 hover:text-slate-200 hover:bg-slate-700' }}">
            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
            <span>Todo</span>
        </a>
    </div>

    <!-- Projects List -->
    <div class="flex-1 overflow-y-auto px-3 py-2 space-y-2">
        <div class="px-3 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">
            {{ $user->isHelper() ? 'Tus Proyectos Asignados' : 'Tus Proyectos' }} ({{ $visibleProjects->count() }})
        </div>

        @if($visibleProjects->isEmpty())
            <div class="text-center py-8 px-4 text-slate-500 text-sm">
                {{ $user->isHelper() ? 'No tienes tareas asignadas en ningún proyecto todavía.' : 'No tienes proyectos guardados. ¡Crea uno para empezar!' }}
            </div>
        @else
            @foreach($visibleProjects as $proj)
                @php $isActive = $activeProject && $activeProject->id == $proj->id; @endphp
                <div class="rounded-lg bg-slate-800/40 border border-slate-800/80 overflow-hidden">
                    <div class="group flex items-center justify-between px-3 py-2.5 text-sm font-medium
                        {{ $isActive ? 'bg-indigo-600/20 text-indigo-300 font-semibold border-l-4 border-indigo-500 pl-2' : 'text-slate-300' }}">
                        <a href="{{ route('dashboard', ['project_id' => $proj->id, 'view' => 'calendario']) }}" class="flex items-center space-x-2 truncate flex-1 min-w-0">
                            <i data-lucide="folder" class="w-4 h-4 shrink-0 {{ $isActive ? 'text-indigo-400' : 'text-slate-400' }}"></i>
                            <span class="truncate">{{ $proj->name }}</span>
                        </a>
                        <div class="flex items-center space-x-1 shrink-0">
                            @if($user->hasFullAccess())
                                <button title="Agregar Módulo" onclick="openModuleModal({{ $proj->id }})" class="p-1 text-slate-400 hover:text-indigo-300 hover:bg-slate-700 rounded transition">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                </button>
                                <button title="Editar proyecto" onclick='openProjectModal(@json($proj))' class="p-1 text-slate-400 hover:text-white hover:bg-slate-700 rounded transition">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                            @endif
                            <button onclick="toggleProjectAccordion({{ $proj->id }})" class="p-1 text-slate-400 hover:text-white rounded transition">
                                <i data-lucide="chevron-down" id="acc-icon-{{ $proj->id }}" class="w-4 h-4 transition-transform duration-200 {{ $isActive ? '' : '-rotate-90' }}"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Modules Accordion -->
                    <div id="acc-content-{{ $proj->id }}" class="{{ $isActive ? '' : 'hidden' }} pl-6 pr-2 py-1 space-y-1 bg-slate-950/40 border-t border-slate-800/60 text-xs">
                        <a href="{{ route('dashboard', ['project_id' => $proj->id, 'module_id' => 'all', 'view' => $activeView]) }}"
                            class="flex items-center justify-between px-2.5 py-1.5 rounded cursor-pointer transition
                            {{ $isActive && $activeModuleId === 'all' ? 'bg-indigo-500/20 text-indigo-300 font-medium' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                            <div class="flex items-center space-x-2">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>Todos</span>
                            </div>
                            <span class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded-full">{{ $proj->tasks->count() }}</span>
                        </a>

                        @foreach($proj->modules as $mod)
                            <div class="group flex items-center justify-between px-2.5 py-1.5 rounded cursor-pointer transition
                                {{ $isActive && $activeModuleId == $mod->id ? 'bg-indigo-500/20 text-indigo-300 font-medium' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                                <a href="{{ route('dashboard', ['project_id' => $proj->id, 'module_id' => $mod->id, 'view' => $activeView]) }}" class="flex items-center space-x-2 truncate flex-1">
                                    <i data-lucide="box" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span class="truncate">{{ $mod->name }}</span>
                                </a>
                                <div class="flex items-center space-x-1">
                                    <span class="text-[10px] bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded-full">{{ $mod->tasks->count() }}</span>
                                    @if($user->hasFullAccess())
                                        <button title="Editar módulo" onclick="openModuleModal({{ $proj->id }}, {{ json_encode($mod) }})" class="p-1 opacity-0 group-hover:opacity-100 text-slate-400 hover:text-white hover:bg-slate-700 rounded transition">
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
    <div class="p-4 border-t border-slate-800 text-xs text-slate-500 flex flex-col items-center space-y-2">
        <button id="pwa-install-btn" onclick="triggerPWAInstall()" class="hidden text-indigo-400 hover:text-indigo-300 transition text-[11px] flex items-center space-x-1.5 py-1 px-2.5 rounded-lg bg-indigo-950/60 border border-indigo-500/30 w-full justify-center">
            <i data-lucide="download" class="w-3.5 h-3.5"></i>
            <span>Instalar Aplicación</span>
        </button>

        @if($user->hasFullAccess())
            <button onclick="openUsersListModal()" class="text-slate-400 hover:text-indigo-300 transition text-[11px] flex items-center space-x-1 py-0.5">
                <i data-lucide="users" class="w-3.5 h-3.5"></i>
                <span>Gestionar Usuarios ({{ $allUsers->count() }})</span>
            </button>
        @endif
    </div>
</aside>

<!-- Main Content Workspace -->
<main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">

    <!-- Top Workspace Navbar (Responsive: mobile 2-row layout with scrollable tabs, desktop 3-column layout) -->
    <header class="bg-white border-b border-slate-200 px-4 sm:px-6 flex flex-col lg:flex-row lg:items-stretch shadow-sm shrink-0">

        <!-- Top row on mobile / Left column on desktop -->
        <div class="flex items-center justify-between lg:flex-1 py-3 lg:py-4 gap-2 min-w-0">
            <!-- Mobile hamburger + Project title -->
            <div class="flex items-center space-x-2 sm:space-x-4 min-w-0">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-600 hover:text-slate-900 p-1 -ml-1 rounded-lg hover:bg-slate-100 focus:outline-none shrink-0" aria-label="Abrir menú">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div class="min-w-0">
                    <div class="flex items-center space-x-2 flex-wrap sm:flex-nowrap gap-y-1">
                        <h2 class="text-base sm:text-xl font-bold text-slate-800 truncate max-w-[200px] sm:max-w-xs md:max-w-md">
                            {{ $activeProject ? $activeProject->name : 'Selecciona un Proyecto' }}
                        </h2>
                        @if($activeProject && $activeModuleId && $activeModuleId !== 'all')
                            @php $activeMod = $activeProject->modules->firstWhere('id', $activeModuleId); @endphp
                            @if($activeMod)
                                <span class="text-xs bg-indigo-50 text-indigo-600 border border-indigo-200 font-semibold px-2.5 py-0.5 rounded-full flex items-center space-x-1 shrink-0">
                                    <i data-lucide="box" class="w-3 h-3"></i>
                                    <span class="truncate max-w-[120px]">{{ $activeMod->name }}</span>
                                </span>
                            @endif
                        @endif
                    </div>
                    @if($activeProject)
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 truncate">
                            {{ $filteredTasks->count() }} tarea(s) pendientes hoy &mdash; {{ $tasks->count() }} total
                        </p>
                    @endif
                </div>
            </div>

            <!-- Right actions on mobile/tablet (< lg) -->
            <div class="flex items-center space-x-2 lg:hidden shrink-0">
                @if($activeProject)
                    @if($activeView === 'kanban' || $activeView === 'calendario')
                        @if(!$user->isHelper())
                            <button onclick="openTaskModal()"
                                class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-xs flex items-center space-x-1.5 transition shadow-sm active:scale-95" title="Agregar Tarea">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Agregar Tarea</span>
                            </button>
                        @endif
                    @elseif($activeView === 'incidencias')
                        <button onclick="openIncidentModal()"
                            class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-xs flex items-center space-x-1.5 transition shadow-sm active:scale-95" title="Reportar Incidencia">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Reportar</span>
                        </button>
                    @elseif($activeView === 'periodicas')
                        @if(!$user->isHelper())
                            <button onclick="openRecurringTaskModal()"
                                class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-xs flex items-center space-x-1.5 transition shadow-sm active:scale-95" title="Nueva Tarea Periódica">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Nueva</span>
                            </button>
                        @endif
                    @elseif($activeView === 'enlaces' && !$user->isHelper())
                        <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))'
                            class="py-1.5 px-2.5 sm:py-2 sm:px-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-xs flex items-center space-x-1.5 transition shadow-sm active:scale-95" title="Gestionar Enlaces">
                            <i data-lucide="link" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Enlaces</span>
                        </button>
                    @endif
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Cerrar Sesión" class="flex items-center space-x-1.5 p-1.5 px-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs transition">
                        <span class="hidden sm:inline">Salir</span>
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i> 
                    </button>
                </form>
            </div>
        </div>

        <!-- Center: Tab Navigation — horizontally scrollable on mobile, centered inside header on desktop -->
        @if($activeProject)
        <nav class="flex items-stretch overflow-x-auto no-scrollbar border-t border-slate-100 lg:border-t-0 -mx-4 px-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:px-0 flex-nowrap shrink-0">
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'calendario']) }}"
                class="flex items-center space-x-1.5 px-3 sm:px-4 py-2.5 lg:py-0 text-xs sm:text-sm font-medium border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'calendario' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                <span>Calendario</span>
            </a>
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'kanban']) }}"
                class="flex items-center space-x-1.5 px-3 sm:px-4 py-2.5 lg:py-0 text-xs sm:text-sm font-medium border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'kanban' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i data-lucide="kanban" class="w-4 h-4"></i>
                <span>Tablero Kanban</span>
            </a>
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'enlaces']) }}"
                class="flex items-center space-x-1.5 px-3 sm:px-4 py-2.5 lg:py-0 text-xs sm:text-sm font-medium border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'enlaces' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i data-lucide="link" class="w-4 h-4"></i>
                <span>Enlaces</span>
                @php
                    $userLinkCount = $user->isHelper()
                        ? $activeProject->links->filter(fn($l) => $l->assignedUsers->contains('id', $user->id))->count()
                        : $activeProject->links->count();
                @endphp
                @if($userLinkCount > 0)
                    <span class="text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full font-semibold">
                        {{ $userLinkCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'incidencias']) }}"
                class="flex items-center space-x-1.5 px-3 sm:px-4 py-2.5 lg:py-0 text-xs sm:text-sm font-medium border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'incidencias' ? 'border-red-600 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Incidencias</span>
                @if($incidents->count() > 0)
                    <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full font-semibold">
                        {{ $incidents->count() }}
                    </span>
                @endif
            </a>
            @if($user->isAdmin())
            <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => 'periodicas']) }}"
                class="flex items-center space-x-1.5 px-3 sm:px-4 py-2.5 lg:py-0 text-xs sm:text-sm font-medium border-b-2 whitespace-nowrap transition shrink-0
                {{ $activeView === 'periodicas' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <i data-lucide="repeat" class="w-4 h-4"></i>
                <span>Tareas Periódicas</span>
                @if($recurringTasks->count() > 0)
                    <span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full font-semibold">
                        {{ $recurringTasks->count() }}
                    </span>
                @endif
            </a>
            @endif
        </nav>
        @endif

        <!-- Right (Desktop lg+ only): Action Button & Logout (mirrors the left column) -->
        <div class="hidden lg:flex lg:flex-1 items-center justify-end py-4 space-x-3">
            @if($activeProject)
                @if($activeView === 'kanban' || $activeView === 'calendario')
                    @if(!$user->isHelper())
                        <button onclick="openTaskModal()"
                            class="py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm flex items-center space-x-2 transition shadow-sm active:scale-95">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Agregar Tarea</span>
                        </button>
                    @endif
                @elseif($activeView === 'incidencias')
                    <button onclick="openIncidentModal()"
                        class="py-2 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm flex items-center space-x-2 transition shadow-sm active:scale-95">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Reportar Incidencia</span>
                    </button>
                @elseif($activeView === 'periodicas')
                    @if(!$user->isHelper())
                        <button onclick="openRecurringTaskModal()"
                            class="py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm flex items-center space-x-2 transition shadow-sm active:scale-95">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Nueva Tarea Periódica</span>
                        </button>
                    @endif
                @elseif($activeView === 'enlaces' && !$user->isHelper())
                    <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))'
                        class="py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm flex items-center space-x-2 transition shadow-sm active:scale-95">
                        <i data-lucide="link" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Gestionar Enlaces</span>
                    </button>
                @endif
            @endif
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Cerrar Sesión" class="flex items-center space-x-2 py-2 px-3 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm rounded transition">
                    <span>Cerrar Sesión</span>
                    <i data-lucide="log-out" class="w-4 h-4"></i> 
                </button>
            </form>
        </div>
    </header>





    <!-- Workspace Body Area -->
    <div class="flex-1 overflow-y-auto p-4 md:p-6">
        @if(!$activeProject)
            <div class="h-full flex flex-col items-center justify-center text-center py-20">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4">
                    <i data-lucide="folder-open" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Sin Proyecto Seleccionado</h3>
                <p class="text-sm text-slate-400 max-w-sm mt-1">Crea o selecciona un proyecto en la barra lateral izquierda para comenzar.</p>
            </div>
        @else

            <!-- VIEW 1: CALENDARIO -->
            @if($activeView === 'calendario')
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Left List: Tasks list by date -->
                    <div class="lg:w-7/12 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <!-- Task List Header & Tabs -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 gap-2">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="list-checks" class="w-4 h-4 text-indigo-600"></i>
                                <span>Lista de Tareas del Proyecto</span>
                            </h3>

                            <!-- Tabs de Filtro: Tareas de Hoy vs Tareas del Mes -->
                            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs overflow-x-auto no-scrollbar max-w-full">
                                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => $activeView, 'task_filter' => 'hoy']) }}"
                                    class="px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 whitespace-nowrap {{ $taskFilter === 'hoy' ? 'bg-white text-indigo-600 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>Tareas de Hoy</span>
                                    <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $taskFilter === 'hoy' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'bg-slate-200 text-slate-600' }}">{{ $todayTasks->count() }}</span>
                                </a>
                                <a href="{{ route('dashboard', ['project_id' => $activeProject->id, 'module_id' => $activeModuleId, 'view' => $activeView, 'task_filter' => 'mes']) }}"
                                    class="px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 whitespace-nowrap {{ $taskFilter === 'mes' ? 'bg-white text-indigo-600 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}">
                                    <i data-lucide="calendar-range" class="w-3.5 h-3.5"></i>
                                    <span>Tareas del Mes</span>
                                    <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $taskFilter === 'mes' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'bg-slate-200 text-slate-600' }}">{{ $monthTasks->count() }}</span>
                                </a>
                            </div>
                        </div>

                        @if($filteredTasks->isEmpty())
                            <div class="text-center py-12 text-slate-400 text-xs">
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
                                            <div class="flex items-center gap-2 pb-2 mb-3 border-b border-slate-200">
                                                <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                                                <h4 class="text-xs font-bold text-slate-800">
                                                    @if($dateKey === 'sin_fecha')
                                                        Sin Fecha Definida
                                                    @else
                                                        {{ ucfirst(\Carbon\Carbon::parse($dateKey)->locale('es')->isoFormat('dddd D \d\e MMMM, YYYY')) }}
                                                        @if(\Carbon\Carbon::parse($dateKey)->isToday())
                                                            <span class="text-[9px] bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.2 rounded ml-1">Hoy</span>
                                                        @endif
                                                    @endif
                                                </h4>
                                                <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full ml-auto">
                                                    {{ $groupTasks->count() }} tarea(s)
                                                </span>
                                            </div>
                                        @endif

                                        <div class="space-y-3">
                                            @foreach($groupTasks as $t)
                                                <div class="group p-3 sm:p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-100 transition shadow-2xs space-y-2">
                                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5 sm:gap-3">
                                                        <!-- Left: Checkbox + Title/Description -->
                                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                                            <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="mt-0.5 shrink-0">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $t->status === 'finalizado' ? 'no_iniciado' : ($t->needs_review && !$user->hasFullAccess() ? 'en_revision' : 'finalizado') }}">
                                                                <input type="checkbox" onchange="this.form.submit()" {{ $t->status === 'finalizado' ? 'checked' : '' }}
                                                                    class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer">
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
                                                                <h4 class="text-xs font-semibold break-words {{ $t->status === 'finalizado' ? 'line-through text-slate-400' : ($t->isOverdue() ? 'text-red-600 font-bold' : ($isTodayTask ? 'text-indigo-700 font-bold' : 'text-slate-800')) }}">
                                                                    {{ $t->title }}
                                                                    @if($t->isOverdue())
                                                                        <span class="text-[9px] bg-red-100 text-red-700 font-bold px-1.5 py-0.5 rounded ml-1 uppercase inline-block">Retrasada</span>
                                                                    @elseif($isTodayTask)
                                                                        <span class="text-[9px] bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.5 rounded ml-1 uppercase inline-block">Para Hoy</span>
                                                                    @endif
                                                                </h4>
                                                                @if($t->description)
                                                                    <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1 break-words">{{ $t->description }}</p>
                                                                @endif
                                                                @if($t->isOverdue() && $dueDateStr)
                                                                    <span class="text-[10px] text-red-600 font-medium inline-flex items-center gap-1 mt-0.5">
                                                                        <i data-lucide="alert-circle" class="w-3 h-3 text-red-500 shrink-0"></i>
                                                                        <span>Debía realizarse el {{ $dueDateStr }}</span>
                                                                    </span>
                                                                @endif
                                                                @if($t->status === 'finalizado' && $t->completed_at)
                                                                    <span class="text-[10px] text-emerald-600 font-medium inline-flex items-center gap-1 mt-0.5">
                                                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-500 shrink-0"></i>
                                                                        <span>Finalizada el {{ $t->completed_at->format('d/m/Y \a \l\a\s H:i') }}</span>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Right: Quick Status buttons (Admin/Dev), Status Badge, Edit Button -->
                                                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap sm:flex-nowrap justify-between sm:justify-end pl-6 sm:pl-0 pt-1 sm:pt-0 border-t sm:border-t-0 border-slate-100 sm:border-transparent shrink-0">
                                                            @if($user->hasFullAccess())
                                                                @if($t->needs_review && $t->status === 'en_revision')
                                                                    <div class="flex items-center gap-1">
                                                                        <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="inline">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <input type="hidden" name="status" value="finalizado">
                                                                            <button type="submit" title="Aprobar (Finalizar)" class="p-1 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 transition">
                                                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                                            </button>
                                                                        </form>
                                                                        <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="inline">
                                                                            @csrf
                                                                            @method('PATCH')
                                                                            <input type="hidden" name="status" value="rechazado">
                                                                            <button type="submit" title="Rechazar" class="p-1 rounded-md bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 transition">
                                                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @else
                                                                    <div class="flex items-center gap-1 sm:opacity-0 sm:group-hover:opacity-100 transition duration-150">
                                                                        @foreach([
                                                                            ['id' => 'no_iniciado', 'title' => 'No Iniciado', 'icon' => 'circle', 'active' => 'bg-slate-200 text-slate-800 border-slate-400', 'inactive' => 'bg-white text-slate-400 border-slate-200 hover:bg-slate-50'],
                                                                            ['id' => 'en_proceso', 'title' => 'En Proceso', 'icon' => 'clock', 'active' => 'bg-amber-100 text-amber-800 border-amber-400', 'inactive' => 'bg-white text-slate-400 border-slate-200 hover:bg-slate-50'],
                                                                            ['id' => 'finalizado', 'title' => 'Finalizado', 'icon' => 'check-circle', 'active' => 'bg-emerald-100 text-emerald-800 border-emerald-400', 'inactive' => 'bg-white text-slate-400 border-slate-200 hover:bg-slate-50'],
                                                                        ] as $stBtn)
                                                                            <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="inline">
                                                                                @csrf
                                                                                @method('PATCH')
                                                                                <input type="hidden" name="status" value="{{ $stBtn['id'] }}">
                                                                                <button type="submit" title="{{ $stBtn['title'] }}"
                                                                                    class="w-6 h-6 rounded-md flex items-center justify-center border transition {{ $t->status === $stBtn['id'] ? $stBtn['active'] : $stBtn['inactive'] }}">
                                                                                    <i data-lucide="{{ $stBtn['icon'] }}" class="w-3.5 h-3.5"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            @endif

                                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md 
                                                                {{ $t->status === 'finalizado' ? 'bg-emerald-50 text-emerald-700' : ($t->status === 'en_proceso' ? 'bg-amber-50 text-amber-700' : ($t->status === 'en_revision' ? 'bg-violet-50 text-violet-700' : ($t->status === 'rechazado' ? 'bg-red-50 text-red-700' : 'bg-slate-200 text-slate-700'))) }}">
                                                                {{ str_replace('_', ' ', ucfirst($t->status)) }}
                                                            </span>

                                                            <button type="button" onclick="openCommentsModal({{ $t->id }}, this.getAttribute('data-title'))" data-title="{{ $t->title }}" class="p-1 text-slate-400 hover:text-indigo-600 rounded hover:bg-slate-100 flex items-center gap-1 transition" title="Comentarios">
                                                                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                                                <span id="task-comment-count-{{ $t->id }}" class="text-[11px] font-semibold {{ $t->comments_count > 0 ? 'text-indigo-600' : 'text-slate-400' }}">{{ $t->comments_count }}</span>
                                                            </button>

                                                            @if(!$user->isHelper())
                                                                <button onclick='openTaskModal(@json($t->load(["assignedUsers", "subtasks"])))' class="p-1 text-slate-400 hover:text-slate-600" title="Editar tarea">
                                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                                </button>
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
                                                                            class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600 cursor-pointer">
                                                                    </form>
                                                                    <span class="{{ $sub->status === 'finalizado' ? 'line-through text-slate-400' : 'text-slate-600' }}">
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

                    <!-- Right FullCalendar Render -->
                    <div class="lg:w-5/12 bg-white rounded-2xl border border-slate-200 p-3 sm:p-5 shadow-sm overflow-hidden">
                        <div id="calendar-container" class="w-full overflow-x-auto"></div>
                    </div>
                </div>
            @endif

            <!-- VIEW 2: KANBAN -->
            @if($activeView === 'kanban')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 items-start max-w-[1600px] mx-auto pb-6">
                    @php
                        $columns = [
                            ['id' => 'no_iniciado', 'title' => 'No Iniciado', 'border' => 'border-slate-400', 'badgeBg' => 'bg-slate-200', 'badgeText' => 'text-slate-700', 'icon' => 'circle'],
                            ['id' => 'en_proceso', 'title' => 'En Proceso', 'border' => 'border-amber-500', 'badgeBg' => 'bg-amber-100', 'badgeText' => 'text-amber-800', 'icon' => 'clock'],
                            ['id' => 'en_revision', 'title' => 'En Revisión', 'border' => 'border-violet-500', 'badgeBg' => 'bg-violet-100', 'badgeText' => 'text-violet-800', 'icon' => 'search'],
                            ['id' => 'rechazado', 'title' => 'Rechazado', 'border' => 'border-red-500', 'badgeBg' => 'bg-red-100', 'badgeText' => 'text-red-800', 'icon' => 'x-circle'],
                            ['id' => 'finalizado', 'title' => 'Finalizado', 'border' => 'border-emerald-500', 'badgeBg' => 'bg-emerald-100', 'badgeText' => 'text-emerald-800', 'icon' => 'check-circle'],
                        ];
                    @endphp

                    @foreach($columns as $col)
                        @php $colTasks = $filteredTasks->where('status', $col['id']); @endphp
                        <div ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event, '{{ $col['id'] }}')"
                            class="bg-slate-100/80 rounded-xl border-t-4 {{ $col['border'] }} border-x border-b border-slate-200/80 flex flex-col max-h-[75vh] transition-all duration-200 shadow-sm kanban-column">
                            
                            <!-- Column Header -->
                            <div class="p-4 flex items-center justify-between border-b border-slate-200/60 bg-white/50 rounded-t-lg">
                                <div class="flex items-center space-x-2">
                                    <span class="p-1.5 rounded-md {{ $col['badgeBg'] }} {{ $col['badgeText'] }}">
                                        <i data-lucide="{{ $col['icon'] }}" class="w-4 h-4"></i>
                                    </span>
                                    <h3 class="font-semibold text-slate-800 text-sm">{{ $col['title'] }}</h3>
                                </div>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">
                                    {{ $colTasks->count() }}
                                </span>
                            </div>

                            <!-- Botón rápido agregar al inicio de la columna -->
                            @if(!$user->isHelper() && $col['id'] !== 'rechazado' && $col['id'] !== 'finalizado')
                                <div class="p-2 border-b border-slate-200/60 bg-white/30">
                                    <button onclick="openTaskModal(null, '{{ $col['id'] }}')"
                                        class="w-full py-1.5 text-xs text-slate-600 hover:text-indigo-600 hover:bg-white rounded-md transition flex items-center justify-center space-x-1 font-medium border border-dashed border-slate-300 hover:border-indigo-300 shadow-sm">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>Añadir Tarea</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Lista de Tarjetas / Drop Zone -->
                            <div class="p-3 flex-1 overflow-y-auto space-y-3 min-h-[150px]">
                                @if($colTasks->isEmpty())
                                    <div class="h-32 border-2 border-dashed border-slate-200 rounded-lg flex items-center justify-center text-slate-400 text-xs text-center p-4">
                                        Arrastra una tarea aquí o haz clic en +
                                    </div>
                                @else
                                    @foreach($colTasks as $t)
                                        <div draggable="true" ondragstart="handleDragStart(event, {{ $t->id }})"
                                            class="bg-white p-4 rounded-lg shadow-sm border border-slate-200 hover:shadow-md transition group cursor-grab active:cursor-grabbing space-y-2.5">
                                            
                                            <!-- Card Header: Title & Actions -->
                                            <div class="flex items-start justify-between gap-2">
                                                 @php
                                                     $isTodayTaskKanban = $t->status !== 'finalizado' && !$t->isOverdue() && (
                                                         ($t->start_date && \Carbon\Carbon::parse($t->start_date)->isToday()) ||
                                                         ($t->end_date && \Carbon\Carbon::parse($t->end_date)->isToday()) ||
                                                         (!$t->start_date && !$t->end_date && $t->created_at && $t->created_at->isToday())
                                                     );
                                                 @endphp
                                                 <h4 class="font-medium text-sm leading-snug break-words flex-1 min-w-0 {{ $t->status === 'finalizado' ? 'line-through text-slate-400' : ($t->isOverdue() ? 'text-red-600 font-bold' : ($isTodayTaskKanban ? 'text-indigo-700 font-bold' : 'text-slate-800')) }}">
                                                     {{ $t->title }}
                                                     @if($t->isOverdue())
                                                         <span class="text-[9px] bg-red-100 text-red-700 font-bold px-1.5 py-0.5 rounded ml-1 uppercase inline-block">Retrasada</span>
                                                     @elseif($isTodayTaskKanban)
                                                         <span class="text-[9px] bg-indigo-100 text-indigo-700 font-bold px-1.5 py-0.5 rounded ml-1 uppercase inline-block">Para Hoy</span>
                                                     @endif
                                                 </h4>
                                                <div class="flex items-center space-x-1 opacity-80 group-hover:opacity-100 transition shrink-0">
                                                    <button type="button" onclick="openCommentsModal({{ $t->id }}, this.getAttribute('data-title'))" data-title="{{ $t->title }}" class="p-1 text-slate-400 hover:text-indigo-600 rounded hover:bg-slate-100 flex items-center gap-0.5" title="Comentarios">
                                                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                                        <span id="task-comment-count-kanban-{{ $t->id }}" class="text-[10px] font-semibold {{ $t->comments_count > 0 ? 'text-indigo-600' : 'text-slate-400' }}">{{ $t->comments_count }}</span>
                                                    </button>
                                                    @if(!$user->isHelper())
                                                        <button onclick='openTaskModal(@json($t->load(["assignedUsers", "subtasks"])))' class="p-1 text-slate-400 hover:text-indigo-600 rounded hover:bg-slate-100" title="Editar tarea">
                                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('tasks.destroy', $t->id) }}" onsubmit="return confirm('¿Eliminar esta tarea?')" class="inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100" title="Eliminar tarea">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($t->description)
                                                <p class="text-xs text-slate-500 line-clamp-2">{{ $t->description }}</p>
                                            @endif

                                            <!-- Badges: Needs Review, Module, Start Date, End Date -->
                                            @if($t->needs_review || $t->module || $t->start_date || $t->end_date)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @if($t->needs_review)
                                                        <span class="text-[10px] bg-violet-50 text-violet-600 border border-violet-100 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="search" class="w-3 h-3"></i>
                                                            <span>Requiere revisión</span>
                                                        </span>
                                                    @endif
                                                    @if($t->module)
                                                        <span class="text-[10px] bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="box" class="w-3 h-3 text-indigo-500"></i>
                                                            <span>{{ $t->module->name }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->start_date)
                                                        <span class="text-[10px] bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                                            <span>{{ $t->start_date }}{{ $t->start_time ? ' '.$t->start_time : '' }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->recurrence && $t->recurrence !== 'ninguna')
                                                        <span class="text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                            <i data-lucide="repeat" class="w-3 h-3 text-indigo-500"></i>
                                                            <span>{{ ucfirst($t->recurrence) }}</span>
                                                        </span>
                                                    @endif
                                                    @if($t->end_date)

                                                        <span class="text-[10px] bg-purple-50 text-purple-600 border border-purple-100 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
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
                                                             <span class="text-[10px] bg-red-50 text-red-700 border border-red-100 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                                 <i data-lucide="alert-triangle" class="w-3 h-3 text-red-600"></i>
                                                                 <span>Debía: {{ $dueOverdueStr }}</span>
                                                             </span>
                                                         @endif
                                                     @endif
                                                    @if($t->status === 'finalizado' && $t->completed_at)
                                                        <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-100 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
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
                                                        <button type="submit" class="w-full py-1 text-[11px] font-medium bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-md transition flex items-center justify-center space-x-1">
                                                            <i data-lucide="check" class="w-3 h-3"></i>
                                                            <span>Aprobar</span>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="flex-1">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="rechazado">
                                                        <button type="submit" class="w-full py-1 text-[11px] font-medium bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-md transition flex items-center justify-center space-x-1">
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
                                                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $stPct }}%"></div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Card Footer: Priority Badge + Member Avatars + Quick Status Select -->
                                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2">
                                                <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded shrink-0
                                                    {{ $t->priority === 'urgente' || $t->priority === 'alta' ? 'bg-red-50 text-red-600 border border-red-100' : ($t->priority === 'media' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-slate-100 text-slate-600 border border-slate-200') }}">
                                                    {{ $t->priority }}
                                                </span>

                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <div class="flex items-center -space-x-1.5 shrink-0">
                                                        @foreach($t->assignedUsers as $u)
                                                            <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[8px] flex items-center justify-center border border-white" title="{{ $u->name }}">
                                                                {{ $u->initials }}
                                                            </span>
                                                        @endforeach
                                                    </div>

                                                    <form method="POST" action="{{ route('tasks.updateStatus', $t->id) }}" class="inline">
                                                        @csrf @method('PATCH')
                                                        <select name="status" onchange="this.form.submit()" class="text-[11px] bg-slate-50 border border-slate-200 rounded text-slate-600 py-0.5 px-1 focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
            @endif

            <!-- VIEW 3: INCIDENCIAS -->
            @if($activeView === 'incidencias')
                <div class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-sm gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Tablero de Incidencias</h3>
                            <p class="text-xs text-slate-500">Reporta y dale seguimiento a fallas o requerimientos del proyecto.</p>
                        </div>
                        <button onclick="openIncidentModal()" class="py-2 px-3.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl text-xs flex items-center justify-center gap-1.5 transition shrink-0 self-start sm:self-auto">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            <span>Reportar Incidencia</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                        @php
                            $incCols = [
                                ['id' => 'abierta', 'title' => 'Abiertas', 'bg' => 'border-red-500'],
                                ['id' => 'en_proceso', 'title' => 'En Proceso', 'bg' => 'border-amber-500'],
                                ['id' => 'resuelta', 'title' => 'Resueltas', 'bg' => 'border-emerald-500'],
                            ];
                        @endphp

                        @foreach($incCols as $icol)
                            @php $incItems = $incidents->where('estado', $icol['id']); @endphp
                            <div class="bg-white rounded-2xl p-4 border border-slate-200 border-t-4 {{ $icol['bg'] }} shadow-sm space-y-3">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ $icol['title'] }} ({{ $incItems->count() }})</h4>

                                <div class="space-y-3">
                                    @foreach($incItems as $inc)
                                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="text-xs font-bold text-slate-800 break-words flex-1 min-w-0">{{ $inc->titulo }}</h5>
                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase shrink-0
                                                    {{ $inc->prioridad === 'urgente' ? 'bg-red-100 text-red-700' : 'bg-slate-200 text-slate-700' }}">
                                                    {{ $inc->prioridad }}
                                                </span>
                                            </div>
                                            @if($inc->descripcion)
                                                <p class="text-[11px] text-slate-500 break-words">{{ $inc->descripcion }}</p>
                                            @endif
                                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between flex-wrap gap-2 text-[10px] text-slate-400">
                                                <span class="truncate max-w-[180px]">Reportado por: <strong>{{ $inc->reporter ? $inc->reporter->name : 'Anónimo' }}</strong></span>
                                                <form method="POST" action="{{ route('incidents.updateStatus', $inc->id) }}">
                                                    @csrf @method('PATCH')
                                                    <select name="estado" onchange="this.form.submit()" class="text-[10px] bg-white border border-slate-200 rounded px-1.5 py-0.5">
                                                        <option value="abierta" {{ $inc->estado === 'abierta' ? 'selected' : '' }}>Abierta</option>
                                                        <option value="en_proceso" {{ $inc->estado === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                                        <option value="resuelta" {{ $inc->estado === 'resuelta' ? 'selected' : '' }}>Resuelta</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- VIEW 4: ENLACES DE PROYECTO -->
            @if($activeView === 'enlaces')
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Enlaces y Recursos del Proyecto</h3>
                            <p class="text-xs text-slate-500">URLs y recursos compartidos asignados a los miembros de este proyecto.</p>
                        </div>
                        @if($user->hasFullAccess())
                            <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))' class="py-2 px-3.5 bg-indigo-600 text-white text-xs font-medium rounded-xl hover:bg-indigo-700 transition self-start sm:self-auto shrink-0">
                                Administrar Enlaces
                            </button>
                        @endif
                    </div>

                    @php
                        $visibleLinks = $user->isHelper()
                            ? $activeProject->links->filter(fn($l) => $l->assignedUsers->contains('id', $user->id))
                            : $activeProject->links;
                    @endphp

                    @if($visibleLinks->isEmpty())
                        <div class="text-center py-12 text-slate-400 text-xs">
                            {{ $user->isHelper() ? 'No tienes enlaces asignados en este proyecto todavía.' : 'No hay enlaces configurados para este proyecto.' }}
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($visibleLinks as $link)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-3">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-xs font-bold text-slate-800 flex items-start gap-1.5 leading-snug min-w-0 flex-1">
                                                <i data-lucide="link" class="w-3.5 h-3.5 text-indigo-600 shrink-0 mt-0.5"></i>
                                                <span class="break-words">{{ $link->name }}</span>
                                            </h4>
                                            @if($user->hasFullAccess())
                                                <button onclick='openProjectModal(@json($activeProject->load("links.assignedUsers")))' title="Editar enlace" class="p-1 text-slate-400 hover:text-indigo-600 hover:bg-slate-200/60 rounded transition shrink-0">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <a href="{{ $link->url }}" target="_blank" class="text-[11px] text-indigo-600 hover:underline break-all block mt-1.5">
                                            {{ $link->url }}
                                        </a>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between flex-wrap gap-2 text-[10px] text-slate-400">
                                        <span>Miembros asignados:</span>
                                        <div class="flex items-center gap-1 flex-wrap">
                                            @foreach($link->assignedUsers as $u)
                                                <span class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[8px] flex items-center justify-center" title="{{ $u->name }}">
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
            @endif

            {{-- VIEW 5: TAREAS PERIÓDICAS --}}
            @if($activeView === 'periodicas')
                <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Reglas de Tareas Periódicas y Recurrentes</h3>
                            <p class="text-xs text-slate-500">Configura tareas repetitivas (Diarias, Semanales, Mensuales o Anuales). Se autogeneran automáticamente al conectarse cualquier usuario.</p>
                        </div>
                        @if(!$user->isHelper())
                            <button onclick="openRecurringTaskModal()" class="py-2 px-3.5 bg-indigo-600 text-white text-xs font-medium rounded-xl hover:bg-indigo-700 transition flex items-center gap-1.5 shadow-sm self-start sm:self-auto shrink-0">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Nueva Tarea Periódica</span>
                            </button>
                        @endif
                    </div>

                    @if($recurringTasks->isEmpty())
                        <div class="text-center py-12 text-slate-400 text-xs">
                            No hay reglas de tareas periódicas configuradas para este proyecto.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($recurringTasks as $rule)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-3 shadow-xs hover:shadow-sm transition">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-sm font-bold text-slate-800 leading-snug break-words flex-1 min-w-0">{{ $rule->title }}</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $rule->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $rule->is_active ? 'Activa' : 'Detenida' }}
                                            </span>
                                        </div>
                                        @if($rule->description)
                                            <p class="text-xs text-slate-500 line-clamp-2 break-words">{{ $rule->description }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            <span class="text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-0.5 rounded font-semibold inline-flex items-center space-x-1">
                                                <i data-lucide="repeat" class="w-3 h-3 text-indigo-500"></i>
                                                <span>{{ ucfirst($rule->recurrence) }}</span>
                                            </span>
                                            <span class="text-[10px] bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                <i data-lucide="bar-chart-2" class="w-3 h-3"></i>
                                                <span>Prioridad: {{ ucfirst($rule->priority) }}</span>
                                            </span>
                                            @if($rule->module)
                                                <span class="text-[10px] bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded font-medium inline-flex items-center space-x-1">
                                                    <i data-lucide="box" class="w-3 h-3 text-indigo-500"></i>
                                                    <span>{{ $rule->module->name }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pt-3 border-t border-slate-200/80 space-y-2">
                                        <div class="text-[10px] text-slate-500 flex items-center justify-between flex-wrap gap-1">
                                            <span>Inicio: <strong>{{ $rule->start_date }}</strong></span>
                                            <span>Última generación: <strong>{{ $rule->last_generated_at ? $rule->last_generated_at->format('d/m/Y H:i') : 'Nunca' }}</strong></span>
                                        </div>

                                        @if(!$user->isHelper())
                                            <div class="flex items-center justify-end gap-1.5 pt-1 flex-wrap">
                                                <form method="POST" action="{{ route('recurring-tasks.toggleActive', $rule->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $rule->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                        {{ $rule->is_active ? 'Pausar / Detener' : 'Reanudar' }}
                                                    </button>
                                                </form>
                                                <button onclick='openRecurringTaskModal(@json($rule->load(["assignedUsers", "subtasks"])))' class="p-1 text-slate-400 hover:text-indigo-600 rounded hover:bg-slate-100 transition" title="Editar regla">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="{{ route('recurring-tasks.destroy', $rule->id) }}" onsubmit="return confirm('¿Eliminar esta regla periódica?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100 transition" title="Eliminar regla">
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
            @endif


        @endif
    </div>
</main>

<!-- MODALS -->

<!-- 1. Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4 hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden max-h-[92vh] flex flex-col">
        <div class="px-4 sm:px-5 py-3 border-b border-slate-100 flex items-center justify-between shrink-0">
            <h3 id="taskModalTitle" class="font-bold text-slate-800 text-base">Nueva Tarea</h3>
            <button onclick="closeModal('taskModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="taskForm" method="POST" action="{{ route('tasks.store') }}" class="px-4 sm:px-5 pb-4 sm:pb-5 max-h-[calc(92vh-55px)] overflow-y-auto">
            @csrf
            <input type="hidden" id="task_method" name="_method" value="POST">
            <input type="hidden" name="project_id" value="{{ $activeProject ? $activeProject->id : '' }}">

            <div class="flex flex-col md:flex-row gap-4 pt-4">
                <!-- Left column: title, description, needs_review, subtasks -->
                <div class="w-full md:w-8/12 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Título</label>
                        <input type="text" id="task_title" name="title" required autofocus
                            placeholder="¿Qué hay que hacer?"
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea id="task_description" name="description" rows="4"
                            placeholder="Detalles opcionales de la tarea..."
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 outline-none transition resize-none"></textarea>
                    </div>

                    <!-- Needs review checkbox -->
                    <label class="flex items-start gap-2 px-3 py-2 bg-violet-50/60 border border-violet-100 rounded-lg cursor-pointer">
                        <input type="checkbox" id="task_needs_review" name="needs_review" value="1"
                            class="mt-0.5 rounded border-slate-300 text-violet-600 focus:ring-violet-500 accent-violet-600">
                        <span class="text-xs text-slate-700">
                            <span class="font-medium">Esta tarea necesita revisión</span><br>
                            <span class="text-slate-500">Antes de Finalizar deberá pasar por "En Revisión" y ser aprobada o rechazada por un Admin.</span>
                        </span>
                    </label>

                    <!-- Subtasks section -->
                    <div class="pt-3 border-t border-slate-100">
                        <label class="block text-xs font-medium text-slate-700 mb-2 flex items-center gap-1.5">
                            <i data-lucide="list-todo" class="w-3.5 h-3.5 text-indigo-500"></i>
                            Subtareas
                            <span id="subtask-counter" class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-full font-semibold hidden"></span>
                        </label>

                        <!-- Subtask list -->
                        <ul id="subtask-list" class="space-y-1 mb-2 max-h-36 overflow-y-auto pr-0.5"></ul>

                        <!-- Hidden inputs for subtasks -->
                        <div id="subtask-inputs"></div>

                        <!-- Add subtask input -->
                        <div class="flex gap-1.5">
                            <input type="text" id="subtask_input"
                                placeholder="Nueva subtarea... (Enter para agregar)"
                                class="flex-1 px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-slate-800 outline-none transition">
                            <button type="button" onclick="addSubtask()"
                                class="shrink-0 p-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:cursor-not-allowed text-white rounded-lg transition flex items-center">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right column: project/module, status, priority, dates, assignees -->
                <div class="w-full md:w-4/12 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Módulo del Proyecto</label>
                        <select id="task_module_id" name="module_id"
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 outline-none transition">
                            <option value="">Sin Módulo (General)</option>
                            @if($activeProject)
                                @foreach($activeProject->modules as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Estado</label>
                        <select id="task_status" name="status"
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 outline-none transition">
                            <option value="no_iniciado">No Iniciado</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="en_revision">En Revisión</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Prioridad</label>
                        <select id="task_priority" name="priority"
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-slate-800 outline-none transition">
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>

                    <!-- Fecha y Hora de Inicio -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Fecha y Hora de Inicio</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="date" id="task_start_date" name="start_date"
                                class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-slate-800 outline-none transition">
                            <input type="time" id="task_start_time" name="start_time"
                                placeholder="Hora (opcional)"
                                class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-slate-800 outline-none transition">
                        </div>
                    </div>

                    <!-- Agregar fecha de fin -->
                    <div id="end-date-toggle-btn">
                        <button type="button" onclick="showEndDate()" class="text-[11px] font-medium text-indigo-600 hover:text-indigo-700 flex items-center space-x-1">
                            <i data-lucide="plus" class="w-3 h-3"></i>
                            <span>Agregar fecha de fin</span>
                        </button>
                    </div>

                    <div id="end-date-section" class="hidden p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-medium text-slate-700">Fecha y Hora de Fin</label>
                            <button type="button" onclick="hideEndDate()" class="text-[11px] font-medium text-red-500 hover:text-red-600 flex items-center space-x-1">
                                <i data-lucide="x" class="w-3 h-3"></i>
                                <span>Quitar</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="date" id="task_end_date" name="end_date"
                                class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-slate-800 outline-none transition">
                            <input type="time" id="task_end_time" name="end_time"
                                placeholder="Hora (opcional)"
                                class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs text-slate-800 outline-none transition">
                        </div>
                    </div>

                    <!-- Asignar Usuarios -->
@if($activeProject)
<div>
    <label class="block text-xs font-medium text-slate-700 mb-1">Asignar a Usuarios</label>
    <div class="border border-slate-200 rounded-lg max-h-28 overflow-y-auto divide-y divide-slate-100">
        @foreach($activeProject->members as $u)
            <label class="flex items-center space-x-2 px-2.5 py-1.5 text-xs cursor-pointer hover:bg-slate-50">
                <input type="checkbox" name="assigned_user_ids[]" value="{{ $u->id }}" class="task-user-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                <span class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 text-[8px] font-bold flex items-center justify-center shrink-0">
                    {{ $u->initials }}
                </span>
                <span class="text-slate-700 truncate">{{ $u->name }}</span>
                <span class="text-slate-400 shrink-0">({{ $u->role }})</span>
            </label>
        @endforeach
    </div>
</div>
@endif
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 mt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('taskModal')" class="w-full sm:w-auto px-3.5 py-2 sm:py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition text-center">Cancelar</button>
                <button type="submit" id="btnSaveTaskPerUser" name="create_per_user" value="1" title="Crea 1 tarea independiente para cada usuario seleccionado" class="w-full sm:w-auto px-3.5 py-2 sm:py-1.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm inline-flex items-center justify-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Crear para cada usuario</span>
                </button>
                <button type="submit" class="w-full sm:w-auto px-3.5 py-2 sm:py-1.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition shadow-sm text-center">Guardar Tarea</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Project Modal -->
<div id="projectModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="projectModalTitle" class="text-sm font-bold text-slate-800">Nuevo Proyecto</h3>
            <button onclick="closeModal('projectModal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="projectForm" method="POST" action="{{ route('projects.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" id="project_method" name="_method" value="POST">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Proyecto</label>
                <input type="text" id="project_name" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Miembros con Acceso</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                    @foreach($allUsers as $u)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="member_user_ids[]" value="{{ $u->id }}" onchange="renderProjectLinks()" class="proj-user-checkbox rounded border-slate-300 text-indigo-600 accent-indigo-600">
                            <span class="truncate">{{ $u->name }} ({{ $u->role }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Enlaces del Proyecto -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
                <label class="block text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="link" class="w-3.5 h-3.5 text-indigo-600"></i>
                    Enlaces del Proyecto
                </label>

                <!-- Formulario agregar enlace -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <input type="text" id="link_name_input" placeholder="Nombre (Ej. Repositorio, Figma...)" class="w-full sm:flex-1 px-3 py-1.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <input type="text" id="link_url_input" placeholder="URL (Ej. https://...)" class="w-full sm:flex-1 px-3 py-1.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <button type="button" onclick="addProjectLink()" class="w-full sm:w-auto px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-xs flex items-center justify-center gap-1 transition shrink-0">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Agregar</span>
                    </button>
                </div>

                <!-- Lista de enlaces agregados -->
                <div id="project-links-list" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>

                <!-- Inputs ocultos para enviar con el formulario -->
                <div id="project-links-hidden-inputs"></div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('projectModal')" class="w-full sm:w-auto py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-xl text-xs text-center">Cancelar</button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-xs text-center">Guardar Proyecto</button>
            </div>
        </form>
    </div>
</div>


<!-- 3. Module Modal -->
<div id="moduleModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="moduleModalTitle" class="text-sm font-bold text-slate-800">Nuevo Módulo</h3>
            <button onclick="closeModal('moduleModal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="moduleForm" method="POST" action="{{ route('modules.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" id="module_method" name="_method" value="POST">
            <input type="hidden" id="module_project_id" name="project_id" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Módulo</label>
                <input type="text" id="module_name" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('moduleModal')" class="w-full sm:w-auto py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-xl text-xs text-center">Cancelar</button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-xs text-center">Guardar Módulo</button>
            </div>
        </form>
    </div>
</div>

<!-- 4. Incident Modal -->
<div id="incidentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-800">Reportar Incidencia</h3>
            <button onclick="closeModal('incidentModal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form method="POST" action="{{ route('incidents.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="project_id" value="{{ $activeProject ? $activeProject->id : '' }}">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Título de la Incidencia</label>
                <input type="text" name="titulo" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción</label>
                <textarea name="descripcion" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioridad</label>
                    <select name="prioridad" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label>
                    <select name="estado" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="abierta" selected>Abierta</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="resuelta">Resuelta</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('incidentModal')" class="w-full sm:w-auto py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium rounded-xl text-xs text-center">Cancelar</button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl text-xs text-center">Guardar Incidencia</button>
            </div>
        </form>
    </div>
</div>

<!-- 5. Users List Modal -->
<div id="usersListModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-800">Gestión de Usuarios del Equipo</h3>
            <button onclick="closeModal('usersListModal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="userForm" method="POST" action="{{ route('users.store') }}" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            @csrf
            <input type="hidden" id="user_method" name="_method" value="POST">

            <div class="flex items-center justify-between gap-2">
                <h4 id="userFormTitle" class="text-xs font-bold text-slate-700">Agregar Nuevo Usuario</h4>
                <button type="button" id="btnCancelUserEdit" onclick="resetUserForm()" class="text-[10px] text-slate-500 hover:text-slate-700 font-medium hidden flex items-center gap-1 shrink-0">
                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                    <span>Cancelar edición</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input type="text" id="user_name" name="name" placeholder="Nombre completo" required class="w-full px-2.5 py-1.5 border rounded-lg text-xs">
                <input type="email" id="user_email" name="email" placeholder="Correo electrónico" required class="w-full px-2.5 py-1.5 border rounded-lg text-xs">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <input type="password" id="user_password" name="password" placeholder="Contraseña" required class="w-full px-2.5 py-1.5 border rounded-lg text-xs">
                <select id="user_role" name="role" required class="w-full px-2.5 py-1.5 border rounded-lg text-xs">
                    <option value="Admin">Admin</option>
                    <option value="Desarrollador">Desarrollador</option>
                    <option value="Ayudante">Ayudante</option>
                </select>
            </div>
            <button type="submit" id="btnSubmitUser" class="w-full py-2 sm:py-1.5 bg-indigo-600 text-white font-medium text-xs rounded-lg hover:bg-indigo-700 transition">Crear Usuario</button>
        </form>

        <div class="divide-y divide-slate-100">
            @foreach($allUsers as $u)
                <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                            {{ $u->initials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ $u->name }}</p>
                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                <p class="text-[10px] text-slate-400 truncate">{{ $u->email }}</p>
                                <span class="text-slate-300">•</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-[10px] text-slate-500 font-mono" id="user-clave-{{ $u->id }}" data-clave="{{ $u->clave ?? '' }}" data-visible="false">
                                        {{ $u->clave ? '••••••••' : '(sin clave)' }}
                                    </span>
                                    @if($u->clave)
                                        <button type="button" onclick="toggleUserPassword({{ $u->id }})" class="p-0.5 text-slate-400 hover:text-slate-600 transition" title="Mostrar/Ocultar contraseña">
                                            <span id="user-clave-icon-{{ $u->id }}"><i data-lucide="eye" class="w-3 h-3"></i></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 self-end sm:self-auto shrink-0">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $u->role }}</span>
                        <button type="button" onclick='editUserInModal(@json($u))' class="p-1 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded transition" title="Editar usuario">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                        @if(Auth::id() !== $u->id)
                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar a {{ $u->name }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-red-600 hover:bg-slate-100 rounded transition" title="Eliminar usuario">
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

<!-- 6. Modal Tarea Periódica / Recurrente -->
<div id="recurringTaskModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 max-h-[92vh] flex flex-col">
        <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50 shrink-0">
            <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2" id="recurringTaskModalTitle">
                <i data-lucide="repeat" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i>
                <span>Nueva Tarea Periódica</span>
            </h3>
            <button onclick="closeModal('recurringTaskModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="recurringTaskForm" method="POST" action="{{ route('recurring-tasks.store') }}" class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
            @csrf
            <input type="hidden" id="recurring_task_method" name="_method" value="POST">
            <input type="hidden" name="project_id" value="{{ $activeProject ? $activeProject->id : '' }}">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Título de la Tarea Periódica *</label>
                <input type="text" id="rec_title" name="title" required placeholder="Ej: Revisión diaria de servidores..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción</label>
                <textarea id="rec_description" name="description" rows="2" placeholder="Detalles de la tarea..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Periodicidad / Frecuencia *</label>
                    <select id="rec_recurrence" name="recurrence" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="diaria">Diaria (Todos los días)</option>
                        <option value="semanal">Semanal (Cada semana)</option>
                        <option value="mensual">Mensual (Una vez al mes)</option>
                        <option value="anual">Anual (Una vez al año)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioridad *</label>
                    <select id="rec_priority" name="priority" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Módulo del Proyecto</label>
                    <select id="rec_module_id" name="module_id"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
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
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha de Inicio Base *</label>
                    <input type="date" id="rec_start_date" name="start_date" required
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hora Inicio (Opcional)</label>
                    <input type="time" id="rec_start_time" name="start_time"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hora Fin (Opcional)</label>
                    <input type="time" id="rec_end_time" name="end_time"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <!-- Needs review checkbox -->
            <label class="flex items-start gap-2 px-3 py-2 bg-violet-50/60 border border-violet-100 rounded-lg cursor-pointer">
                <input type="checkbox" id="rec_needs_review" name="needs_review" value="1"
                    class="mt-0.5 rounded border-slate-300 text-violet-600 focus:ring-violet-500 accent-violet-600">
                <span class="text-xs text-slate-700">
                    <span class="font-medium">Requiere revisión al completarse</span><br>
                    <span class="text-slate-500">Las tareas generadas deberán pasar por revisión de Admin.</span>
                </span>
            </label>

           <!-- Asignar Usuarios -->
@if($activeProject)
<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Asignar Usuarios</label>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-lg max-h-32 overflow-y-auto">
        @foreach($activeProject->members as $u)
            <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                <input type="checkbox" name="assigned_user_ids[]" value="{{ $u->id }}" class="rec-user-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="truncate">{{ $u->name }} <span class="text-[10px] text-slate-400">({{ $u->role }})</span></span>
            </label>
        @endforeach
    </div>
</div>
@endif

            <!-- Subtareas Plantilla -->
            <div class="pt-2 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Subtareas Plantilla</label>
                <ul id="rec-subtask-list" class="space-y-1 mb-2 max-h-32 overflow-y-auto pr-0.5"></ul>
                <div id="rec-subtask-inputs"></div>
                <div class="flex gap-1.5">
                    <input type="text" id="rec_subtask_input" placeholder="Nueva subtarea plantilla..."
                        class="flex-1 px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs text-slate-800 outline-none">
                    <button type="button" onclick="addRecSubtask()" class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shrink-0">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                <button type="button" onclick="closeModal('recurringTaskModal')" class="w-full sm:w-auto py-2 px-4 border border-slate-300 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 text-center">
                    Cancelar
                </button>
                <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 shadow-xs text-center">
                    Guardar Regla Periódica
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 6. Comments Modal -->
<div id="commentsModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[88vh]">
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60 shrink-0">
            <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                <div class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600 shrink-0">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-slate-800 text-sm truncate" id="commentsModalTaskTitle">Comentarios</h3>
                    <p class="text-[11px] text-slate-400 truncate">Todos los miembros pueden comentar y colaborar.</p>
                </div>
            </div>
            <button onclick="closeModal('commentsModal')" class="text-slate-400 hover:text-slate-600 transition shrink-0 p-1"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <div id="commentsList" class="p-3.5 sm:p-5 overflow-y-auto flex-1 space-y-3 min-h-[140px]">
            <div class="text-center py-8 text-slate-400 text-xs">Cargando comentarios...</div>
        </div>

        <form id="commentForm" onsubmit="submitComment(event)" class="p-3 sm:p-3.5 border-t border-slate-100 bg-slate-50/40 flex flex-col sm:flex-row gap-2 sm:items-end shrink-0">
            <input type="hidden" id="comment_task_id" value="">
            <div class="w-full sm:flex-1">
                <textarea id="comment_input" rows="2" required placeholder="Escribe un comentario..." class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none"></textarea>
            </div>
            <button type="submit" id="btnSubmitComment" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-xs rounded-xl transition flex items-center justify-center gap-1 shrink-0 shadow-sm">
                <span>Comentar</span>
                <i data-lucide="send" class="w-3.5 h-3.5"></i>
            </button>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
    }

    function toggleProjectAccordion(projId) {
        const content = document.getElementById('acc-content-' + projId);
        const icon = document.getElementById('acc-icon-' + projId);
        if (content) content.classList.toggle('hidden');
        if (icon) icon.classList.toggle('-rotate-90');
    }

    function openModal(id) {
        if (window.innerWidth < 1024) {
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
            li.className = 'flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5';
            li.innerHTML = `
                <input type="checkbox" ${st.status === 'finalizado' ? 'checked' : ''}
                    onchange="toggleSubtaskStatus(${idx})"
                    class="h-3.5 w-3.5 shrink-0 rounded border-slate-300 text-indigo-600 accent-indigo-500">
                <span class="flex-1 text-xs truncate ${st.status === 'finalizado' ? 'line-through text-slate-400' : 'text-slate-700'}">${escapeHtml(st.title)}</span>
                <button type="button" onclick="removeSubtask(${idx})"
                    class="shrink-0 p-0.5 text-slate-300 hover:text-red-500 rounded transition" title="Eliminar subtarea">
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

        // Reset subtasks and end-date
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
        listEl.innerHTML = '<div class="text-center py-8 text-slate-400 text-xs">Cargando comentarios...</div>';
        
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
            listEl.innerHTML = '<div class="text-center py-8 text-slate-400 text-xs flex flex-col items-center justify-center gap-1.5"><i data-lucide="message-square" class="w-6 h-6 text-slate-300"></i><span>No hay comentarios aún. ¡Sé el primero en comentar!</span></div>';
            if (window.lucide) lucide.createIcons();
            return;
        }

        listEl.innerHTML = comments.map(c => `
            <div class="pt-3 first:pt-0 border-b border-slate-100 pb-3 last:border-b-0 flex items-start justify-between gap-2.5 sm:gap-3 group">
                <div class="flex items-start gap-2.5 min-w-0 flex-1">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-indigo-200 mt-0.5">
                        ${escapeHtml(c.user_initials)}
                    </div>
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-800">${escapeHtml(c.user_name)}</span>
                            <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.2 rounded font-medium">${escapeHtml(c.user_role)}</span>
                            <span class="text-[10px] text-slate-400" title="${c.created_at_formatted}">${c.created_at}</span>
                        </div>
                        <p class="text-xs text-slate-700 whitespace-pre-line leading-relaxed break-words">${escapeHtml(c.comment)}</p>
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
            listBadge.className = `text-[11px] font-semibold ${count > 0 ? 'text-indigo-600' : 'text-slate-400'}`;
        }
        const kanbanBadge = document.getElementById(`task-comment-count-kanban-${taskId}`);
        if (kanbanBadge) {
            kanbanBadge.textContent = count;
            kanbanBadge.className = `text-[10px] font-semibold ${count > 0 ? 'text-indigo-600' : 'text-slate-400'}`;
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
        col.classList.add('ring-2', 'ring-indigo-400', 'bg-indigo-50/40');
    }

    function handleDragLeave(e) {
        const col = e.currentTarget;
        col.classList.remove('ring-2', 'ring-indigo-400', 'bg-indigo-50/40');
    }

    function handleDrop(e, newStatus) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.remove('ring-2', 'ring-indigo-400', 'bg-indigo-50/40');

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
            listContainer.innerHTML = '<p class="text-xs text-slate-400 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 italic">Aún no hay enlaces agregados.</p>';
            return;
        }

        const selectedMemberIds = Array.from(document.querySelectorAll('.proj-user-checkbox:checked')).map(cb => parseInt(cb.value));

        projectLinks.forEach((link, idx) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 space-y-2';

            if (editingLinkIndex === idx) {
                // Modo Edición Inline
                const editFormDiv = document.createElement('div');
                editFormDiv.className = 'space-y-2';
                editFormDiv.innerHTML = `
                    <div class="flex items-center justify-between pb-1 border-b border-slate-200">
                        <span class="text-[11px] font-bold text-indigo-600 flex items-center gap-1">
                            <i data-lucide="pencil" class="w-3 h-3"></i>
                            Editar Enlace
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">Nombre del enlace</label>
                            <input type="text" id="edit_link_name_${idx}" value="${escapeHtml(link.name)}"
                                class="w-full px-2.5 py-1 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-600 mb-0.5">URL del enlace</label>
                            <input type="text" id="edit_link_url_${idx}" value="${escapeHtml(link.url)}"
                                class="w-full px-2.5 py-1 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-1.5 pt-1">
                        <button type="button" onclick="cancelProjectLinkEdit()" class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg transition">
                            Cancelar
                        </button>
                        <button type="button" onclick="saveProjectLinkEdit(${idx})" class="px-2.5 py-1 text-[11px] font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition flex items-center gap-1">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <span>Guardar</span>
                        </button>
                    </div>
                `;
                itemDiv.appendChild(editFormDiv);
            } else {
                // Modo Visualización
                const topRow = document.createElement('div');
                topRow.className = 'flex items-start justify-between gap-2';

                const infoDiv = document.createElement('div');
                infoDiv.className = 'min-w-0 flex items-start gap-2 flex-1';
                infoDiv.innerHTML = `
                    <i data-lucide="link" class="w-3.5 h-3.5 text-indigo-600 shrink-0 mt-0.5"></i>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-bold text-slate-800 break-words block leading-snug">${escapeHtml(link.name)}</span>
                        <a href="${escapeHtml(link.url)}" target="_blank" class="text-[11px] text-indigo-600 hover:underline break-all block mt-0.5">${escapeHtml(link.url)}</a>
                    </div>
                `;

                const actionBtnsDiv = document.createElement('div');
                actionBtnsDiv.className = 'flex items-center gap-1 shrink-0';

                const editBtn = document.createElement('button');
                editBtn.type = 'button';
                editBtn.className = 'p-1 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-lg transition';
                editBtn.title = 'Editar enlace';
                editBtn.innerHTML = '<i data-lucide="pencil" class="w-3.5 h-3.5"></i>';
                editBtn.onclick = () => startProjectLinkEdit(idx);

                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'p-1 text-slate-400 hover:text-red-600 hover:bg-white rounded-lg transition';
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
                assigneesDiv.className = 'flex flex-wrap items-center gap-1 pt-1.5 border-t border-slate-200/60';

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
                            : 'bg-white border-slate-300 text-slate-600 hover:border-indigo-400'
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
            li.className = 'flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5';
            li.innerHTML = `
                <span class="flex-1 text-xs text-slate-700 truncate">${escapeHtml(st.title)}</span>
                <button type="button" onclick="removeRecSubtask(${idx})" class="text-slate-300 hover:text-red-500">
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
            titleHeading.innerHTML = '<i data-lucide="pencil" class="w-5 h-5 text-indigo-600"></i><span>Editar Tarea Periódica</span>';
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
            titleHeading.innerHTML = '<i data-lucide="repeat" class="w-5 h-5 text-indigo-600"></i><span>Nueva Tarea Periódica</span>';
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

    // FullCalendar Initialization
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('calendar-container');
        if (calendarEl && window.FullCalendar) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 'auto',
                firstDay: 1,
                selectable: true,
                events: [
                    @foreach($tasks as $t)
                        @if($t->start_date && $t->recurrence !== 'diaria')
                            {
                                id: '{{ $t->id }}',
                                title: '{{ addslashes($t->title) }}',
                                start: '{{ $t->start_date }}',
                                backgroundColor: '{{ $t->status === "finalizado" ? "#10b981" : ($t->isOverdue() ? "#ef4444" : ($t->status === "en_proceso" ? "#f59e0b" : "#6366f1")) }}',
                                borderColor: 'transparent'
                            },
                        @endif
                    @endforeach
                ],

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
            calendar.render();
        }
    });
</script>
@endpush
