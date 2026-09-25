@extends('layouts.auth')

@section('content')
<div class="w-full max-w-md">
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 shadow-2xl rounded-2xl p-5 sm:p-8">
        <div class="flex flex-col items-center text-center mb-8">
            <div class="w-14 h-14 bg-indigo-600/20 border border-indigo-500/30 rounded-2xl flex items-center justify-center text-indigo-400 mb-4 shadow-inner">
                <i data-lucide="kanban" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Gestor de Proyectos</h1>
            <p class="text-xs text-slate-400 mt-1">Inicia sesión para acceder a tus tableros y tareas</p>
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
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Correo Electrónico</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email', 'admin@admin.com') }}" required autofocus
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700/80 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Contraseña</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" id="password" name="password" value="password" required
                        class="w-full pl-10 pr-10 py-2.5 bg-slate-900/90 border border-slate-700/80 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition" title="Mostrar/Ocultar contraseña">
                        <span id="password-toggle-icon"><i data-lucide="eye" class="w-4 h-4"></i></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition transform active:scale-95 flex items-center justify-center gap-2">
                <span>Ingresar al Sistema</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-700/60 text-center">
            <p class="text-xs text-slate-400 mb-3">Acceso rápido de prueba:</p>
            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                <button type="button" onclick="fillDemo('admin@admin.com')"
                    class="py-2 px-1.5 sm:px-2 bg-slate-700/50 hover:bg-slate-700 border border-slate-600/50 rounded-lg text-slate-300 text-xs font-medium transition text-center">
                    <span class="block text-[11px] font-bold text-indigo-400">Admin</span>
                    <span class="text-[10px] text-slate-400 truncate block">admin@admin.com</span>
                </button>
                <button type="button" onclick="fillDemo('dev@admin.com')"
                    class="py-2 px-1.5 sm:px-2 bg-slate-700/50 hover:bg-slate-700 border border-slate-600/50 rounded-lg text-slate-300 text-xs font-medium transition text-center">
                    <span class="block text-[11px] font-bold text-emerald-400">Desarrollador</span>
                    <span class="text-[10px] text-slate-400 truncate block">dev@admin.com</span>
                </button>
                <button type="button" onclick="fillDemo('ayudante@admin.com')"
                    class="py-2 px-1.5 sm:px-2 bg-slate-700/50 hover:bg-slate-700 border border-slate-600/50 rounded-lg text-slate-300 text-xs font-medium transition text-center">
                    <span class="block text-[11px] font-bold text-amber-400">Ayudante</span>
                    <span class="text-[10px] text-slate-400 truncate block">ayudante@...</span>
                </button>
            </div>
        </div>
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
@endsection
