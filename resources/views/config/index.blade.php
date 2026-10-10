@extends('layouts.app')

@section('title', 'Configuración - Estados, Prioridad & Periodicidad')

@section('content')
<div class="flex-1 flex flex-col h-full w-full overflow-hidden bg-[#B3B3B3] dark:bg-[#0b0f19] transition-colors duration-200">

    <!-- Barra Superior / Encabezado -->
    <header class="bg-[#0f172a] lg:bg-[#B3B3B3] lg:dark:bg-[#0b0f19] border-b border-slate-700/60 lg:border-black/15 lg:dark:border-slate-800/80 px-4 sm:px-6 py-3.5 lg:py-0 lg:min-h-[72px] flex items-center justify-between shadow-md lg:shadow-none shrink-0 z-20">
        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard') }}" 
               id="btn-config-return"
               onclick="handleSettingsReturn(event)"
               class="p-2 rounded-xl text-slate-300 lg:text-black lg:dark:text-slate-300 hover:text-white lg:hover:text-black lg:dark:hover:text-white hover:bg-white/10 lg:hover:bg-black/10 lg:dark:hover:bg-slate-800 transition flex items-center gap-1.5 text-xs font-bold"
               title="Regresar">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Regresar</span>
            </a>
            <div class="h-4 w-[1px] bg-slate-700 lg:bg-black/20 lg:dark:bg-slate-700"></div>
            <div>
                <h1 class="text-base sm:text-lg font-bold text-white lg:text-black lg:dark:text-white tracking-tight flex items-center gap-2">
                    <i data-lucide="palette" class="w-4 h-4 text-indigo-700 dark:text-indigo-400"></i>
                    <span>Configuración</span>
                </h1>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Selector de Tema (Claro / Oscuro) -->
            <button type="button" onclick="toggleTheme()" 
                    class="p-2 rounded-xl text-amber-300 bg-slate-800/80 lg:bg-white/70 lg:dark:bg-slate-800/80 hover:bg-slate-700 lg:hover:bg-white lg:dark:hover:bg-slate-700 lg:text-black lg:dark:text-amber-300 transition border border-slate-700/70 lg:border-black/15 lg:dark:border-slate-700/70" 
                    title="Alternar tema claro / oscuro">
                <span class="theme-icon-moon"><i data-lucide="moon" class="w-4 h-4"></i></span>
                <span class="theme-icon-sun hidden"><i data-lucide="sun" class="w-4 h-4"></i></span>
            </button>
        </div>
    </header>

    <!-- Área de Contenido Principal (1 Contenedor) -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-10 flex items-start justify-center">
              <!-- Contenedor Principal de Configuración -->
        <div class="w-full max-w-3xl bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-soft p-5 sm:p-8 space-y-6">
            
            <!-- Mensaje de éxito al guardar -->
            @if (session('success'))
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-xs">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Selector de Tabs Centrado en la parte superior del cuadro -->
            <div class="flex items-center justify-center pb-2 border-b border-slate-100 dark:border-slate-800/80">
                <nav class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/90 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 gap-1" aria-label="Tabs de Configuración">
                    <button type="button" 
                            onclick="switchTab('estados')" 
                            id="tab-btn-estados"
                            class="tab-btn px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-2 bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs border border-slate-200/70 dark:border-slate-700 cursor-pointer">
                        <i data-lucide="list-todo" class="w-4 h-4"></i>
                        <span>Estados</span>
                    </button>
                    <button type="button" 
                            onclick="switchTab('prioridad')" 
                            id="tab-btn-prioridad"
                            class="tab-btn px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all duration-200 flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                        <i data-lucide="flag" class="w-4 h-4"></i>
                        <span>Prioridad</span>
                    </button>
                    <button type="button" 
                            onclick="switchTab('periodicidad')" 
                            id="tab-btn-periodicidad"
                            class="tab-btn px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-medium transition-all duration-200 flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white cursor-pointer">
                        <i data-lucide="repeat" class="w-4 h-4"></i>
                        <span>Tareas Periódicas</span>
                    </button>
                </nav>
            </div>

            <!-- ======================================================== -->
            <!-- TAB 1: ESTADOS                                           -->
            <!-- ======================================================== -->
            <div id="tab-content-estados" class="tab-pane space-y-6">
                <!-- Encabezado del Contenedor de Estados -->
                <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            Estados de la Tarea & Iconos
                        </h2>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                            {{ $estados->count() }} Registros
                        </span>
                    </div>
                </div>

                <!-- Alertas de Error de Validación si hubiese -->
                @if (isset($errors) && $errors->any())
                    <div class="p-3.5 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-300 text-xs">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulario para editar y guardar en BD con soporte de subida de archivos -->
                <form method="POST" action="{{ route('config.estados.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3">
                        @foreach($estados as $estado)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 gap-3.5 hover:border-slate-300 dark:hover:border-slate-700 transition">
                                
                                <input type="hidden" name="estados[{{ $estado->id }}][id]" value="{{ $estado->id }}">

                                <!-- Izquierda: Indicador Color, Icono, Nombre del Estado & Vista Previa -->
                                <div class="flex items-center space-x-3 flex-1 min-w-[220px]">
                                    <!-- Indicador Circular del Color -->
                                    <span id="preview-indicator-{{ $estado->id }}" 
                                          class="w-4 h-4 rounded-full shrink-0 shadow-xs border border-black/10 dark:border-white/10 transition-colors duration-200" 
                                          style="background-color: {{ $estado->color }};">
                                    </span>

                                    <!-- Vista previa del Icono actual / seleccionador rápido -->
                                    <div class="relative w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs group cursor-pointer hover:border-indigo-400 transition"
                                         onclick="document.getElementById('file-input-{{ $estado->id }}').click()"
                                         title="Haz clic para cambiar el icono">
                                        <img id="icon-preview-{{ $estado->id }}" 
                                             src="{{ $estado->ruta_icono ? asset($estado->ruta_icono) : '' }}" 
                                             alt="{{ $estado->nombre }}" 
                                             class="w-full h-full object-contain p-1 {{ $estado->ruta_icono ? '' : 'hidden' }}">
                                        <span id="icon-placeholder-{{ $estado->id }}" class="flex items-center justify-center text-slate-400 {{ $estado->ruta_icono ? 'hidden' : '' }}">
                                            <i data-lucide="circle" id="placeholder-circle-{{ $estado->id }}" class="w-5 h-5 stroke-[2.5]" style="color: {{ $estado->color }};"></i>
                                        </span>
                                    </div>

                                    <!-- Nombre y Badge -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <input type="text" 
                                                   name="estados[{{ $estado->id }}][nombre]" 
                                                   id="nombre-{{ $estado->id }}"
                                                   value="{{ old('estados.'.$estado->id.'.nombre', $estado->nombre) }}" 
                                                   data-badge-text="preview-badge-{{ $estado->id }}"
                                                   required
                                                   class="estado-nombre-input text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 bg-transparent border-b border-transparent hover:border-slate-300 dark:hover:border-slate-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:outline-none transition py-0.5 w-full sm:max-w-[200px]"
                                                   title="Haz clic para editar el nombre del estado">
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span id="preview-badge-{{ $estado->id }}" 
                                                  class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md text-white transition-colors duration-200 truncate max-w-[130px]"
                                                  style="background-color: {{ $estado->color }};">
                                                {{ $estado->nombre }}
                                            </span>
                                            <span id="badge-assigned-{{ $estado->id }}" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5 {{ $estado->ruta_icono ? '' : 'hidden' }}">
                                                <i data-lucide="check" class="w-3 h-3"></i> Icono asignado
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Derecha: Selector de Archivo de Icono + Controles de Color -->
                                <div class="flex items-center space-x-2.5 sm:justify-end shrink-0 flex-wrap gap-y-2">
                                    
                                    <!-- Botón e Input para Cargar Icono -->
                                    <div class="relative">
                                        <label for="file-input-{{ $estado->id }}" 
                                               class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5 shadow-2xs hover:border-indigo-400"
                                               title="Subir archivo de imagen para este estado">
                                            <i data-lucide="upload" class="w-3.5 h-3.5 text-indigo-500"></i>
                                            <span id="file-label-{{ $estado->id }}" class="hidden sm:inline">
                                                {{ $estado->ruta_icono ? 'Cambiar' : 'Subir Icono' }}
                                            </span>
                                        </label>
                                        <input type="file" 
                                               id="file-input-{{ $estado->id }}" 
                                               name="estados[{{ $estado->id }}][icono]" 
                                               accept="image/*"
                                               data-item-id="{{ $estado->id }}"
                                               data-preview="icon-preview-{{ $estado->id }}"
                                               data-placeholder="icon-placeholder-{{ $estado->id }}"
                                               data-label="file-label-{{ $estado->id }}"
                                               data-delete-btn="btn-delete-icon-{{ $estado->id }}"
                                               data-badge="badge-assigned-{{ $estado->id }}"
                                               class="hidden icon-file-input">
                                    </div>

                                    <!-- Campo oculto para indicar eliminación de imagen -->
                                    <input type="hidden" 
                                           name="estados[{{ $estado->id }}][eliminar_icono]" 
                                           id="eliminar-icono-{{ $estado->id }}" 
                                           value="0">

                                    <!-- Botón para Eliminar Icono / Imagen Personalizada -->
                                    <button type="button" 
                                            id="btn-delete-icon-{{ $estado->id }}"
                                            onclick="removeCustomIcon({{ $estado->id }})"
                                            class="px-2.5 py-2 rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50/50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5 shadow-2xs hover:border-red-400 {{ $estado->ruta_icono ? '' : 'hidden' }}"
                                            title="Eliminar imagen y volver al icono predeterminado">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
                                        <span class="hidden sm:inline">Eliminar</span>
                                    </button>

                                    <!-- Input Color Picker -->
                                    <div class="relative flex items-center justify-center">
                                        <input type="color" 
                                               id="color-{{ $estado->id }}" 
                                               value="{{ $estado->color }}"
                                               data-target="hex-{{ $estado->id }}"
                                               data-indicator="preview-indicator-{{ $estado->id }}"
                                               data-badge="preview-badge-{{ $estado->id }}"
                                               data-circle="placeholder-circle-{{ $estado->id }}"
                                               class="color-picker-input w-10 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-0.5 bg-white dark:bg-slate-700 transition hover:scale-105 active:scale-95 shrink-0"
                                               title="Seleccionar color">
                                    </div>

                                    <!-- Input Código Hexadecimal -->
                                    <div class="relative w-full sm:w-32">
                                        <input type="text" 
                                               id="hex-{{ $estado->id }}" 
                                               name="estados[{{ $estado->id }}][color]"
                                               value="{{ old('estados.'.$estado->id.'.color', $estado->color) }}"
                                               maxlength="7"
                                               data-target="color-{{ $estado->id }}"
                                               data-indicator="preview-indicator-{{ $estado->id }}"
                                               data-badge="preview-badge-{{ $estado->id }}"
                                               data-circle="placeholder-circle-{{ $estado->id }}"
                                               placeholder="#64748b"
                                               required
                                               class="hex-code-input w-full px-3 py-2 text-xs sm:text-sm font-mono uppercase rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition"
                                               title="Código hexadecimal">
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Botón de Guardar Cambios -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition shadow-sm active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- ======================================================== -->
            <!-- TAB 2: PRIORIDAD                                         -->
            <!-- ======================================================== -->
            <div id="tab-content-prioridad" class="tab-pane hidden space-y-6">
                <!-- Encabezado del Contenedor de Prioridad -->
                <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Prioridades de la Tarea & Iconos
                        </h2>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60">
                            {{ $prioridades->count() }} Registros
                        </span>
                    </div>
                </div>

                <!-- Formulario de Prioridad -->
                <form method="POST" action="{{ route('config.prioridades.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        @foreach($prioridades as $prio)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 gap-3.5 hover:border-slate-300 dark:hover:border-slate-700 transition">
                                
                                <input type="hidden" name="prioridades[{{ $prio->id }}][id]" value="{{ $prio->id }}">

                                <!-- Izquierda: Indicador Color, Icono, Nombre de la Prioridad & Vista Previa -->
                                <div class="flex items-center space-x-3 flex-1 min-w-[220px]">
                                    <!-- Indicador Circular del Color -->
                                    <span id="preview-indicator-{{ $prio->id }}" 
                                          class="w-4 h-4 rounded-full shrink-0 shadow-xs border border-black/10 dark:border-white/10 transition-colors duration-200" 
                                          style="background-color: {{ $prio->color }};">
                                    </span>

                                    <!-- Vista previa del Icono actual / seleccionador rápido -->
                                    <div class="relative w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs group cursor-pointer hover:border-indigo-400 transition"
                                         onclick="document.getElementById('file-input-{{ $prio->id }}').click()"
                                         title="Haz clic para cambiar el icono">
                                        <img id="icon-preview-{{ $prio->id }}" 
                                             src="{{ $prio->ruta_icono ? asset($prio->ruta_icono) : '' }}" 
                                             alt="{{ $prio->nombre }}" 
                                             class="w-full h-full object-contain p-1 {{ $prio->ruta_icono ? '' : 'hidden' }}">
                                        <span id="icon-placeholder-{{ $prio->id }}" class="flex items-center justify-center text-slate-400 {{ $prio->ruta_icono ? 'hidden' : '' }}">
                                            <i data-lucide="circle" id="placeholder-circle-{{ $prio->id }}" class="w-5 h-5 stroke-[2.5]" style="color: {{ $prio->color }};"></i>
                                        </span>
                                    </div>

                                    <!-- Nombre y Badge -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <input type="text" 
                                                   name="prioridades[{{ $prio->id }}][nombre]" 
                                                   id="nombre-{{ $prio->id }}"
                                                   value="{{ $prio->nombre }}" 
                                                   data-badge-text="preview-badge-{{ $prio->id }}"
                                                   required
                                                   class="estado-nombre-input text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 bg-transparent border-b border-transparent hover:border-slate-300 dark:hover:border-slate-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:outline-none transition py-0.5 w-full sm:max-w-[200px]"
                                                   title="Haz clic para editar el nombre de la prioridad">
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span id="preview-badge-{{ $prio->id }}" 
                                                  class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md text-white transition-colors duration-200 truncate max-w-[130px]"
                                                  style="background-color: {{ $prio->color }};">
                                                {{ $prio->nombre }}
                                            </span>
                                            <span id="badge-assigned-{{ $prio->id }}" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5 {{ $prio->ruta_icono ? '' : 'hidden' }}">
                                                <i data-lucide="check" class="w-3 h-3"></i> Icono asignado
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Derecha: Selector de Archivo de Icono + Controles de Color -->
                                <div class="flex items-center space-x-2.5 sm:justify-end shrink-0 flex-wrap gap-y-2">
                                    
                                    <!-- Botón e Input para Cargar Icono -->
                                    <div class="relative">
                                        <label for="file-input-{{ $prio->id }}" 
                                               class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5 shadow-2xs hover:border-indigo-400"
                                               title="Subir archivo de imagen para esta prioridad">
                                            <i data-lucide="upload" class="w-3.5 h-3.5 text-indigo-500"></i>
                                            <span id="file-label-{{ $prio->id }}" class="hidden sm:inline">
                                                {{ $prio->ruta_icono ? 'Cambiar' : 'Subir Icono' }}
                                            </span>
                                        </label>
                                        <input type="file" 
                                               id="file-input-{{ $prio->id }}" 
                                               name="prioridades[{{ $prio->id }}][icono]" 
                                               accept="image/*"
                                               data-item-id="{{ $prio->id }}"
                                               data-preview="icon-preview-{{ $prio->id }}"
                                               data-placeholder="icon-placeholder-{{ $prio->id }}"
                                               data-label="file-label-{{ $prio->id }}"
                                               data-delete-btn="btn-delete-icon-{{ $prio->id }}"
                                               data-badge="badge-assigned-{{ $prio->id }}"
                                               class="hidden icon-file-input">
                                    </div>

                                    <!-- Campo oculto para indicar eliminación de imagen -->
                                    <input type="hidden" 
                                           name="prioridades[{{ $prio->id }}][eliminar_icono]" 
                                           id="eliminar-icono-{{ $prio->id }}" 
                                           value="0">

                                    <!-- Botón para Eliminar Icono / Imagen Personalizada -->
                                    <button type="button" 
                                            id="btn-delete-icon-{{ $prio->id }}"
                                            onclick="removeCustomIcon({{ $prio->id }})"
                                            class="px-2.5 py-2 rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50/50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5 shadow-2xs hover:border-red-400 {{ $prio->ruta_icono ? '' : 'hidden' }}"
                                            title="Eliminar imagen y volver al icono predeterminado">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
                                        <span class="hidden sm:inline">Eliminar</span>
                                    </button>

                                    <!-- Input Color Picker -->
                                    <div class="relative flex items-center justify-center">
                                        <input type="color" 
                                               id="color-{{ $prio->id }}" 
                                               value="{{ $prio->color }}"
                                               data-target="hex-{{ $prio->id }}"
                                               data-indicator="preview-indicator-{{ $prio->id }}"
                                               data-badge="preview-badge-{{ $prio->id }}"
                                               data-circle="placeholder-circle-{{ $prio->id }}"
                                               class="color-picker-input w-10 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-0.5 bg-white dark:bg-slate-700 transition hover:scale-105 active:scale-95 shrink-0"
                                               title="Seleccionar color">
                                    </div>

                                    <!-- Input Código Hexadecimal -->
                                    <div class="relative w-full sm:w-32">
                                        <input type="text" 
                                               id="hex-{{ $prio->id }}" 
                                               name="prioridades[{{ $prio->id }}][color]"
                                               value="{{ $prio->color }}"
                                               maxlength="7"
                                               data-target="color-{{ $prio->id }}"
                                               data-indicator="preview-indicator-{{ $prio->id }}"
                                               data-badge="preview-badge-{{ $prio->id }}"
                                               data-circle="placeholder-circle-{{ $prio->id }}"
                                               placeholder="#64748b"
                                               required
                                               class="hex-code-input w-full px-3 py-2 text-xs sm:text-sm font-mono uppercase rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition"
                                               title="Código hexadecimal">
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Botón de Guardar Cambios -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition shadow-sm active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- ======================================================== -->
            <!-- TAB 3: TAREAS PERIÓDICAS / PERIODICIDAD                  -->
            <!-- ======================================================== -->
            <div id="tab-content-periodicidad" class="tab-pane hidden space-y-6">
                <!-- Encabezado del Contenedor de Periodicidad -->
                <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Tareas Periódicas & Periodicidad
                        </h2>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                            {{ $periodicidades->count() }} Registros
                        </span>
                    </div>
                </div>

                <!-- Formulario de Periodicidad -->
                <form method="POST" action="{{ route('config.periodicidades.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        @foreach($periodicidades as $peri)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 gap-3.5 hover:border-slate-300 dark:hover:border-slate-700 transition">
                                
                                <input type="hidden" name="periodicidades[{{ $peri->id }}][id]" value="{{ $peri->id }}">

                                <!-- Izquierda: Indicador Color, Icono, Nombre de la Periodicidad & Vista Previa -->
                                <div class="flex items-center space-x-3 flex-1 min-w-[220px]">
                                    <!-- Indicador Circular del Color -->
                                    <span id="preview-indicator-{{ $peri->id }}" 
                                          class="w-4 h-4 rounded-full shrink-0 shadow-xs border border-black/10 dark:border-white/10 transition-colors duration-200" 
                                          style="background-color: {{ $peri->color }};">
                                    </span>

                                    <!-- Vista previa del Icono actual / seleccionador rápido -->
                                    <div class="relative w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs group cursor-pointer hover:border-indigo-400 transition"
                                         onclick="document.getElementById('file-input-{{ $peri->id }}').click()"
                                         title="Haz clic para cambiar el icono">
                                        <img id="icon-preview-{{ $peri->id }}" 
                                             src="{{ $peri->ruta_icono ? asset($peri->ruta_icono) : '' }}" 
                                             alt="{{ $peri->nombre }}" 
                                             class="w-full h-full object-contain p-1 {{ $peri->ruta_icono ? '' : 'hidden' }}">
                                        <span id="icon-placeholder-{{ $peri->id }}" class="flex items-center justify-center text-slate-400 {{ $peri->ruta_icono ? 'hidden' : '' }}">
                                            <i data-lucide="circle" id="placeholder-circle-{{ $peri->id }}" class="w-5 h-5 stroke-[2.5]" style="color: {{ $peri->color }};"></i>
                                        </span>
                                    </div>

                                    <!-- Nombre y Badge -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <input type="text" 
                                                   name="periodicidades[{{ $peri->id }}][nombre]" 
                                                   id="nombre-{{ $peri->id }}"
                                                   value="{{ $peri->nombre }}" 
                                                   data-badge-text="preview-badge-{{ $peri->id }}"
                                                   required
                                                   class="estado-nombre-input text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 bg-transparent border-b border-transparent hover:border-slate-300 dark:hover:border-slate-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:outline-none transition py-0.5 w-full sm:max-w-[200px]"
                                                   title="Haz clic para editar el nombre de la frecuencia">
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span id="preview-badge-{{ $peri->id }}" 
                                                  class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md text-white transition-colors duration-200 truncate max-w-[130px]"
                                                  style="background-color: {{ $peri->color }};">
                                                {{ $peri->nombre }}
                                            </span>
                                            <span id="badge-assigned-{{ $peri->id }}" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5 {{ $peri->ruta_icono ? '' : 'hidden' }}">
                                                <i data-lucide="check" class="w-3 h-3"></i> Icono asignado
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Derecha: Selector de Archivo de Icono + Controles de Color -->
                                <div class="flex items-center space-x-2.5 sm:justify-end shrink-0 flex-wrap gap-y-2">
                                    
                                    <!-- Botón e Input para Cargar Icono -->
                                    <div class="relative">
                                        <label for="file-input-{{ $peri->id }}" 
                                               class="px-2.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5 shadow-2xs hover:border-indigo-400"
                                               title="Subir archivo de imagen para esta periodicidad">
                                            <i data-lucide="upload" class="w-3.5 h-3.5 text-indigo-500"></i>
                                            <span id="file-label-{{ $peri->id }}" class="hidden sm:inline">
                                                {{ $peri->ruta_icono ? 'Cambiar' : 'Subir Icono' }}
                                            </span>
                                        </label>
                                        <input type="file" 
                                               id="file-input-{{ $peri->id }}" 
                                               name="periodicidades[{{ $peri->id }}][icono]" 
                                               accept="image/*"
                                               data-item-id="{{ $peri->id }}"
                                               data-preview="icon-preview-{{ $peri->id }}"
                                               data-placeholder="icon-placeholder-{{ $peri->id }}"
                                               data-label="file-label-{{ $peri->id }}"
                                               data-delete-btn="btn-delete-icon-{{ $peri->id }}"
                                               data-badge="badge-assigned-{{ $peri->id }}"
                                               class="hidden icon-file-input">
                                    </div>

                                    <!-- Campo oculto para indicar eliminación de imagen -->
                                    <input type="hidden" 
                                           name="periodicidades[{{ $peri->id }}][eliminar_icono]" 
                                           id="eliminar-icono-{{ $peri->id }}" 
                                           value="0">

                                    <!-- Botón para Eliminar Icono / Imagen Personalizada -->
                                    <button type="button" 
                                            id="btn-delete-icon-{{ $peri->id }}"
                                            onclick="removeCustomIcon({{ $peri->id }})"
                                            class="px-2.5 py-2 rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50/50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5 shadow-2xs hover:border-red-400 {{ $peri->ruta_icono ? '' : 'hidden' }}"
                                            title="Eliminar imagen y volver al icono predeterminado">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
                                        <span class="hidden sm:inline">Eliminar</span>
                                    </button>

                                    <!-- Input Color Picker -->
                                    <div class="relative flex items-center justify-center">
                                        <input type="color" 
                                               id="color-{{ $peri->id }}" 
                                               value="{{ $peri->color }}"
                                               data-target="hex-{{ $peri->id }}"
                                               data-indicator="preview-indicator-{{ $peri->id }}"
                                               data-badge="preview-badge-{{ $peri->id }}"
                                               data-circle="placeholder-circle-{{ $peri->id }}"
                                               class="color-picker-input w-10 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-0.5 bg-white dark:bg-slate-700 transition hover:scale-105 active:scale-95 shrink-0"
                                               title="Seleccionar color">
                                    </div>

                                    <!-- Input Código Hexadecimal -->
                                    <div class="relative w-full sm:w-32">
                                        <input type="text" 
                                               id="hex-{{ $peri->id }}" 
                                               name="periodicidades[{{ $peri->id }}][color]"
                                               value="{{ $peri->color }}"
                                               maxlength="7"
                                               data-target="color-{{ $peri->id }}"
                                               data-indicator="preview-indicator-{{ $peri->id }}"
                                               data-badge="preview-badge-{{ $peri->id }}"
                                               data-circle="placeholder-circle-{{ $peri->id }}"
                                               placeholder="#64748b"
                                               required
                                               class="hex-code-input w-full px-3 py-2 text-xs sm:text-sm font-mono uppercase rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition"
                                               title="Código hexadecimal">
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Botón de Guardar Cambios -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition shadow-sm active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>

    </main>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Función para validar y normalizar un color hexadecimal
        function normalizeHex(hexValue) {
            let clean = hexValue.trim();
            if (!clean.startsWith('#')) {
                clean = '#' + clean;
            }
            if (/^#[0-9A-Fa-f]{3}$/.test(clean)) {
                clean = '#' + clean.substring(1).split('').map(c => c + c).join('');
            }
            if (/^#[0-9A-Fa-f]{6}$/.test(clean)) {
                return clean.toLowerCase();
            }
            return null;
        }

        // Actualizar indicadores visuales de color
        function updatePreviews(hexColor, indicatorId, badgeId, circleId) {
            const indicator = document.getElementById(indicatorId);
            const badge = document.getElementById(badgeId);
            const circle = document.getElementById(circleId);
            if (indicator) indicator.style.backgroundColor = hexColor;
            if (badge) badge.style.backgroundColor = hexColor;
            if (circle) circle.style.color = hexColor;
        }

        // Sincronización: Al cambiar el input type="color" -> actualiza el input de texto hexadecimal
        document.querySelectorAll('.color-picker-input').forEach(colorInput => {
            const hexInputId = colorInput.getAttribute('data-target');
            const indicatorId = colorInput.getAttribute('data-indicator');
            const badgeId = colorInput.getAttribute('data-badge');
            const circleId = colorInput.getAttribute('data-circle');
            const hexInput = document.getElementById(hexInputId);

            colorInput.addEventListener('input', (e) => {
                const currentVal = e.target.value.toUpperCase(); // e.g. #3B82F6
                if (hexInput) {
                    hexInput.value = currentVal;
                }
                updatePreviews(currentVal, indicatorId, badgeId, circleId);
            });
        });

        // Sincronización: Al escribir en el input de texto hexadecimal -> actualiza el input type="color"
        document.querySelectorAll('.hex-code-input').forEach(hexInput => {
            const colorInputId = hexInput.getAttribute('data-target');
            const indicatorId = hexInput.getAttribute('data-indicator');
            const badgeId = hexInput.getAttribute('data-badge');
            const circleId = hexInput.getAttribute('data-circle');
            const colorInput = document.getElementById(colorInputId);

            const handleHexChange = () => {
                const validHex = normalizeHex(hexInput.value);
                if (validHex) {
                    if (colorInput) colorInput.value = validHex;
                    updatePreviews(validHex, indicatorId, badgeId, circleId);
                }
            };

            hexInput.addEventListener('input', handleHexChange);
            hexInput.addEventListener('blur', () => {
                const validHex = normalizeHex(hexInput.value);
                if (validHex) {
                    hexInput.value = validHex.toUpperCase();
                }
            });
        });

        // Sincronización en vivo del nombre con la etiqueta de vista previa
        document.querySelectorAll('.estado-nombre-input').forEach(nameInput => {
            const badgeId = nameInput.getAttribute('data-badge-text');
            const badge = document.getElementById(badgeId);
            if (badge) {
                nameInput.addEventListener('input', (e) => {
                    badge.textContent = e.target.value || 'Estado';
                });
            }
        });

        // Previsualización inmediata al seleccionar archivo de icono
        document.querySelectorAll('.icon-file-input').forEach(fileInput => {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                const itemId = this.getAttribute('data-item-id');
                if (file) {
                    const previewId = this.getAttribute('data-preview');
                    const placeholderId = this.getAttribute('data-placeholder');
                    const labelId = this.getAttribute('data-label');
                    const deleteBtnId = this.getAttribute('data-delete-btn');
                    const badgeId = this.getAttribute('data-badge');

                    const previewImg = document.getElementById(previewId);
                    const placeholder = document.getElementById(placeholderId);
                    const label = document.getElementById(labelId);
                    const deleteBtn = document.getElementById(deleteBtnId);
                    const badge = document.getElementById(badgeId);
                    const eliminarInput = document.getElementById('eliminar-icono-' + itemId);

                    if (eliminarInput) {
                        eliminarInput.value = '0';
                    }
                    if (previewImg) {
                        previewImg.src = URL.createObjectURL(file);
                        previewImg.classList.remove('hidden');
                    }
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                    if (deleteBtn) {
                        deleteBtn.classList.remove('hidden');
                    }
                    if (label) {
                        label.textContent = file.name.length > 12 ? file.name.substring(0, 10) + '...' : file.name;
                    }
                    if (badge) {
                        badge.classList.remove('hidden');
                    }
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }
            });
        });

        // Eliminar icono personalizado y volver inmediatamente al círculo predeterminado
        window.removeCustomIcon = function(id) {
            const eliminarInput = document.getElementById('eliminar-icono-' + id);
            if (eliminarInput) {
                eliminarInput.value = '1';
            }

            const fileInput = document.getElementById('file-input-' + id);
            if (fileInput) {
                fileInput.value = '';
            }

            const previewImg = document.getElementById('icon-preview-' + id);
            if (previewImg) {
                previewImg.classList.add('hidden');
                previewImg.src = '';
            }

            const placeholder = document.getElementById('icon-placeholder-' + id);
            if (placeholder) {
                placeholder.classList.remove('hidden');
            }

            const deleteBtn = document.getElementById('btn-delete-icon-' + id);
            if (deleteBtn) {
                deleteBtn.classList.add('hidden');
            }

            const label = document.getElementById('file-label-' + id);
            if (label) {
                label.textContent = 'Subir Icono';
            }

            const badge = document.getElementById('badge-assigned-' + id);
            if (badge) {
                badge.classList.add('hidden');
            }

            if (window.lucide) {
                window.lucide.createIcons();
            }
        };

        if (window.lucide) {
            window.lucide.createIcons();
        }

        // ---- Lógica dinámica de retorno a la vista anterior ----
        window.handleSettingsReturn = function(e) {
            if (e) e.preventDefault();
            const baseDashboard = "{{ route('dashboard') }}";
            let targetHref = baseDashboard;
            try {
                const savedUrl = localStorage.getItem('settingsReturnUrl');
                const savedView = localStorage.getItem('settingsReturnView');
                
                if (savedUrl) {
                    const parsed = new URL(savedUrl, window.location.origin);
                    if (parsed.origin === window.location.origin && !parsed.pathname.includes('/configuracion')) {
                        targetHref = savedUrl;
                    }
                }
                
                if (targetHref === baseDashboard && savedView && savedView !== 'configuracion') {
                    const separator = baseDashboard.includes('?') ? '&' : '?';
                    targetHref = baseDashboard + separator + 'view=' + encodeURIComponent(savedView);
                }
            } catch (err) {
                console.error('Error al resolver vista de retorno:', err);
            }
            window.location.href = targetHref;
        };

        // Actualizar href del botón de regreso con la vista guardada
        const returnBtn = document.getElementById('btn-config-return');
        if (returnBtn) {
            try {
                const baseDashboard = "{{ route('dashboard') }}";
                let targetHref = baseDashboard;
                const savedUrl = localStorage.getItem('settingsReturnUrl');
                const savedView = localStorage.getItem('settingsReturnView');
                
                if (savedUrl) {
                    const parsed = new URL(savedUrl, window.location.origin);
                    if (parsed.origin === window.location.origin && !parsed.pathname.includes('/configuracion')) {
                        targetHref = savedUrl;
                    }
                }
                
                if (targetHref === baseDashboard && savedView && savedView !== 'configuracion') {
                    const separator = baseDashboard.includes('?') ? '&' : '?';
                    targetHref = baseDashboard + separator + 'view=' + encodeURIComponent(savedView);
                }
                returnBtn.setAttribute('href', targetHref);
            } catch (err) {
                console.error(err);
            }
        }

        // ---- Lógica de Tabs (Estados, Prioridad, Periodicidad) ----
        window.switchTab = function(tabName) {
            // Ocultar todos los paneles
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));

            // Resetear estilos de todos los botones de tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-white', 'dark:bg-slate-900', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-xs', 'border', 'border-slate-200/70', 'dark:border-slate-700', 'font-bold');
                btn.classList.add('text-slate-500', 'dark:text-slate-400', 'font-medium');
            });

            // Mostrar panel activo
            const activePane = document.getElementById('tab-content-' + tabName);
            if (activePane) {
                activePane.classList.remove('hidden');
            }

            // Activar botón seleccionado
            const activeBtn = document.getElementById('tab-btn-' + tabName);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-500', 'dark:text-slate-400', 'font-medium');
                activeBtn.classList.add('bg-white', 'dark:bg-slate-900', 'text-indigo-600', 'dark:text-indigo-400', 'shadow-xs', 'border', 'border-slate-200/70', 'dark:border-slate-700', 'font-bold');
            }

            // Guardar preferencia de pestaña activa
            try {
                localStorage.setItem('config_active_tab', tabName);
            } catch (e) {}

            if (window.lucide) {
                window.lucide.createIcons();
            }
        };

        // Restaurar último tab activo o por defecto 'estados'
        @if(session('active_tab'))
            switchTab('{{ session("active_tab") }}');
        @else
            try {
                const savedTab = localStorage.getItem('config_active_tab');
                if (savedTab && ['estados', 'prioridad', 'periodicidad'].includes(savedTab)) {
                    switchTab(savedTab);
                }
            } catch (e) {}
        @endif
    });
</script>
@endpush
