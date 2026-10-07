@extends('layouts.app')

@section('title', 'Configuración - Estados de la Tarea')

@section('content')
<div class="flex-1 flex flex-col h-full w-full overflow-hidden bg-slate-50 dark:bg-slate-950 transition-colors duration-200">

    <!-- Barra Superior / Encabezado -->
    <header class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-6 py-3.5 flex items-center justify-between shadow-soft shrink-0 z-20">
        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard') }}" 
               class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-1.5 text-xs font-semibold"
               title="Volver al Tablero">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Volver al Tablero</span>
            </a>
            <div class="h-4 w-[1px] bg-slate-200 dark:bg-slate-700"></div>
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <i data-lucide="palette" class="w-4 h-4 text-indigo-500"></i>
                    <span>Configuración</span>
                </h1>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Selector de Tema (Claro / Oscuro) -->
            <button type="button" onclick="toggleTheme()" 
                    class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition border border-slate-200/80 dark:border-slate-700/80" 
                    title="Alternar tema claro / oscuro">
                <span class="theme-icon-moon"><i data-lucide="moon" class="w-4 h-4"></i></span>
                <span class="theme-icon-sun hidden"><i data-lucide="sun" class="w-4 h-4 text-amber-400"></i></span>
            </button>
        </div>
    </header>

    <!-- Área de Contenido Principal (1 Contenedor) -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-10 flex items-start justify-center">
        
        <!-- Contenedor: Estados de la tarea (Datos desde la base de datos) -->
        <div class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-soft p-5 sm:p-8 space-y-6">
            
            <!-- Encabezado del Contenedor -->
            <div class="border-b border-slate-100 dark:border-slate-800/80 pb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        Estados de la Tarea
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Edita los nombres y colores distintivos de cada estado directamente en la base de datos.
                    </p>
                </div>
                <div class="hidden sm:block">
                    <span class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                        {{ $estados->count() }} Registros
                    </span>
                </div>
            </div>

            <!-- Alertas de Error de Validación si hubiese -->
            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900/50 text-red-700 dark:text-red-300 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario para editar y guardar en BD -->
            <form method="POST" action="{{ route('config.estados.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    @foreach($estados as $estado)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 gap-3.5 hover:border-slate-300 dark:hover:border-slate-700 transition">
                            
                            <input type="hidden" name="estados[{{ $estado->id }}][id]" value="{{ $estado->id }}">

                            <!-- Nombre del Estado & Vista Previa -->
                            <div class="flex items-center space-x-3 flex-1 min-w-[200px]">
                                <span id="preview-indicator-{{ $estado->id }}" 
                                      class="w-4 h-4 rounded-full shrink-0 shadow-xs border border-black/10 dark:border-white/10 transition-colors duration-200" 
                                      style="background-color: {{ $estado->color }};">
                                </span>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <input type="text" 
                                               name="estados[{{ $estado->id }}][nombre]" 
                                               id="nombre-{{ $estado->id }}"
                                               value="{{ old('estados.'.$estado->id.'.nombre', $estado->nombre) }}" 
                                               data-badge-text="preview-badge-{{ $estado->id }}"
                                               required
                                               class="estado-nombre-input text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 bg-transparent border-b border-transparent hover:border-slate-300 dark:hover:border-slate-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:outline-none transition py-0.5 w-full sm:max-w-[220px]"
                                               title="Haz clic para editar el nombre del estado">
                                    </div>
                                    <span id="preview-badge-{{ $estado->id }}" 
                                          class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md mt-0.5 text-white transition-colors duration-200"
                                          style="background-color: {{ $estado->color }};">
                                        {{ $estado->nombre }}
                                    </span>
                                </div>
                            </div>

                            <!-- Controles de Color (Color Picker + Hexadecimal) -->
                            <div class="flex items-center space-x-2.5 sm:justify-end shrink-0">
                                <!-- Input Color -->
                                <div class="relative flex items-center justify-center">
                                    <input type="color" 
                                           id="color-{{ $estado->id }}" 
                                           value="{{ $estado->color }}"
                                           data-target="hex-{{ $estado->id }}"
                                           data-indicator="preview-indicator-{{ $estado->id }}"
                                           data-badge="preview-badge-{{ $estado->id }}"
                                           class="color-picker-input w-10 h-10 rounded-xl cursor-pointer border border-slate-300 dark:border-slate-600 p-0.5 bg-white dark:bg-slate-700 transition hover:scale-105 active:scale-95 shrink-0"
                                           title="Seleccionar color">
                                </div>

                                <!-- Input Código Hexadecimal (Se envía en el formulario) -->
                                <div class="relative w-full sm:w-36">
                                    <input type="text" 
                                           id="hex-{{ $estado->id }}" 
                                           name="estados[{{ $estado->id }}][color]"
                                           value="{{ old('estados.'.$estado->id.'.color', $estado->color) }}"
                                           maxlength="7"
                                           data-target="color-{{ $estado->id }}"
                                           data-indicator="preview-indicator-{{ $estado->id }}"
                                           data-badge="preview-badge-{{ $estado->id }}"
                                           placeholder="#64748b"
                                           required
                                           class="hex-code-input w-full px-3 py-2 text-xs sm:text-sm font-mono uppercase rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition"
                                           title="Código hexadecimal">
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Botones de Acción (Guardar en Base de Datos) -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                    <button type="submit" 
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-semibold transition shadow-sm active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </div>

            </form>

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

        // Actualizar indicadores visuales
        function updatePreviews(hexColor, indicatorId, badgeId) {
            const indicator = document.getElementById(indicatorId);
            const badge = document.getElementById(badgeId);
            if (indicator) indicator.style.backgroundColor = hexColor;
            if (badge) badge.style.backgroundColor = hexColor;
        }

        // Sincronización: Al cambiar el input type="color" -> actualiza el input de texto hexadecimal
        document.querySelectorAll('.color-picker-input').forEach(colorInput => {
            const hexInputId = colorInput.getAttribute('data-target');
            const indicatorId = colorInput.getAttribute('data-indicator');
            const badgeId = colorInput.getAttribute('data-badge');
            const hexInput = document.getElementById(hexInputId);

            colorInput.addEventListener('input', (e) => {
                const currentVal = e.target.value.toUpperCase(); // e.g. #3B82F6
                if (hexInput) {
                    hexInput.value = currentVal;
                }
                updatePreviews(currentVal, indicatorId, badgeId);
            });
        });

        // Sincronización: Al escribir en el input de texto hexadecimal -> actualiza el input type="color"
        document.querySelectorAll('.hex-code-input').forEach(hexInput => {
            const colorInputId = hexInput.getAttribute('data-target');
            const indicatorId = hexInput.getAttribute('data-indicator');
            const badgeId = hexInput.getAttribute('data-badge');
            const colorInput = document.getElementById(colorInputId);

            const handleHexChange = () => {
                const validHex = normalizeHex(hexInput.value);
                if (validHex) {
                    if (colorInput) colorInput.value = validHex;
                    updatePreviews(validHex, indicatorId, badgeId);
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

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>
@endpush
