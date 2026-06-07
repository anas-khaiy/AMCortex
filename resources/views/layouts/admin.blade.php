<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin AMCortex - @yield('title')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-[#FAF7F4] text-gray-900">

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside class="w-72 bg-white border-r border-[#EFE6DE] p-6 flex flex-col">

        <div class="flex items-center gap-3 mb-10">
            <div class="w-14 h-14 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center">
                <i data-lucide="brain-circuit" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-[#9A0002]">AMCortex</h1>
                <p class="text-xs text-gray-400 font-bold">Admin Panel</p>
            </div>
        </div>

        <nav class="space-y-2 flex-1">

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black
               {{ request()->routeIs('admin.dashboard') ? 'bg-[#9A0002] text-white' : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                Dashboard
            </a>

            <a href="{{ route('admin.teachers') }}"
               class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black
               {{ request()->routeIs('admin.teachers*') ? 'bg-[#9A0002] text-white' : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                <i data-lucide="users" class="w-5 h-5"></i>
                Enseignants
            </a>

            <a href="{{ route('admin.teachers.create') }}"
               class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black
               {{ request()->routeIs('admin.teachers.create') ? 'bg-[#9A0002] text-white' : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Ajouter enseignant
            </a>

            <a href="{{ route('admin.students') }}"
               class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black
               {{ request()->routeIs('admin.students') ? 'bg-[#9A0002] text-white' : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                Étudiants
            </a>

            <a href="{{ route('admin.exams') }}"
               class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black
               {{ request()->routeIs('admin.exams') ? 'bg-[#9A0002] text-white' : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                <i data-lucide="file-text" class="w-5 h-5"></i>
                Examens
            </a>

            <a href="{{ route('admin.statistics') }}"
               class="flex items-center gap-3 px-5 py-4 rounded-2xl font-black
               {{ request()->routeIs('admin.statistics') ? 'bg-[#9A0002] text-white' : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                Statistiques
            </a>

        </nav>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-gray-500 hover:bg-red-50 hover:text-[#9A0002]">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                Déconnexion
            </button>
        </form>

    </aside>

    {{-- MAIN --}}
    <main class="flex-1">

        <header class="h-24 bg-white/80 border-b border-[#EFE6DE] px-8 flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">Administration</p>
                <h2 class="text-2xl font-black text-gray-950">@yield('title')</h2>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="font-black text-gray-900">{{ auth()->user()->full_name }}</p>
                    <p class="text-xs font-black text-[#9A0002] uppercase">Administrateur</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center font-black">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </div>
            </div>
        </header>

        <section class="p-8">
            @yield('content')
        </section>

    </main>

</div>

{{-- FOOTER --}}
<footer class="border-t border-[#EFE6DE] bg-white px-8 py-5">
    <div class="flex flex-col md:flex-row items-center justify-between gap-3">

        <div>
            <h3 class="font-black text-[#9A0002] text-lg">AMCortex</h3>
            <p class="text-sm text-gray-500">
                Plateforme intelligente de correction et d’analyse pédagogique.
            </p>
        </div>

        <div class="text-sm text-gray-500 text-center md:text-right">
            <p class="font-semibold text-gray-700">
                Contact professionnel
            </p>

            <a href="mailto:amcortexx@gmail.com"
               class="text-[#9A0002] font-bold hover:underline">
                amcortexx@gmail.com
            </a>

            <p class="mt-1 text-xs text-gray-400">
                © {{ date('Y') }} AMCortex — Tous droits réservés
            </p>
        </div>

    </div>
</footer>

<script>
    lucide.createIcons();
</script>

</body>
</html>