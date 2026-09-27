@extends('layouts.auth')

@section('content')
<div class="w-full max-w-md">
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 shadow-2xl rounded-2xl p-5 sm:p-8">
        <div class="flex flex-col items-center text-center mb-8">
            
            <h1 class="text-2xl font-bold text-white tracking-tight">Gestor de Tareas</h1>
            
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Correo</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700/80 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-900/90 border border-slate-700/80 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition" title="Mostrar/Ocultar contraseña">
                        <span id="password-toggle-icon"><i data-lucide="eye" class="w-4 h-4"></i></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                    <span>Recordar</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                <span>Ingresar al Sistema</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        
    </div>
</div>

<script>
    function fillDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const iconSpan = document.getElementById('password-toggle-icon');
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (iconSpan) iconSpan.innerHTML = '<i data-lucide="eye-off" class="w-4 h-4"></i>';
        } else {
            input.type = 'password';
            if (iconSpan) iconSpan.innerHTML = '<i data-lucide="eye" class="w-4 h-4"></i>';
        }

        if (window.lucide) lucide.createIcons();
    }
</script>

</script>


{{-- ========================================================= --}}
{{-- TARJETA DE INSTALACIÓN PWA --}}
{{-- ========================================================= --}}

<div
    id="pwa-install-card"
    class="hidden fixed bottom-5 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-md z-50"
>
    <div
        class="relative bg-slate-800/95 backdrop-blur-xl border border-slate-700/70 rounded-2xl shadow-2xl shadow-black/40 p-4 sm:p-5"
    >

        {{-- Botón cerrar --}}
        <button
            type="button"
            id="pwa-install-close"
            class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-700/70 transition"
            aria-label="Cerrar"
        >
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>


        <div class="flex items-start gap-4 pr-8">

            {{-- Icono --}}
            <div
                class="shrink-0 w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-600/20"
            >
                <i
                    data-lucide="download"
                    class="w-6 h-6 text-white"
                ></i>
            </div>


            {{-- Texto --}}
            <div class="flex-1 min-w-0">

                <h3 class="text-sm font-bold text-white">
                    Instalar aplicación
                </h3>

                <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                    Instala la aplicación para acceder más rápido
                    y disfrutar de una mejor experiencia.
                </p>

            </div>

        </div>


        {{-- Botón descargar --}}
        <button
            type="button"
            id="pwa-install-btn"
            onclick="triggerPWAInstall()"
            class="w-full mt-4 py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 transition transform active:scale-[0.98] flex items-center justify-center gap-2"
        >

            <i
                data-lucide="download"
                class="w-4 h-4"
            ></i>

            <span>Descargar aplicación</span>

        </button>

    </div>
</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | Mostrar tarjeta cuando la PWA pueda instalarse
    |--------------------------------------------------------------------------
    */

    window.addEventListener('pwa-installable', function () {

        const card = document.getElementById('pwa-install-card');

        if (!card) {
            return;
        }

        card.classList.remove('hidden');

        if (window.lucide) {
            lucide.createIcons();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Cerrar tarjeta
    |--------------------------------------------------------------------------
    */

    const pwaCloseButton =
        document.getElementById('pwa-install-close');

    if (pwaCloseButton) {

        pwaCloseButton.addEventListener('click', function () {

            const card =
                document.getElementById('pwa-install-card');

            if (card) {
                card.classList.add('hidden');
            }

        });

    }

</script>


@endsection
