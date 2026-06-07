<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMCortex - @yield('title')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .sidebar-collapsed { width: 5.5rem !important; }
        .sidebar-collapsed .nav-label,
        .sidebar-collapsed .logo-text,
        .sidebar-collapsed .sidebar-card,
        .sidebar-collapsed .logout-label { display: none; }
        .sidebar-collapsed .nav-item { justify-content: center; }
        .nav-item {
            transition: all 0.25s ease;
        }
        .nav-item:hover {
            transform: translateX(4px);
        }
    </style>
</head>

<body class="bg-[#FAF7F4] text-gray-900">

<div class="flex min-h-screen overflow-hidden">
        {{-- SIDEBAR --}}
        <aside id="sidebar"
               class="w-72 bg-white text-gray-800 border-r border-[#EFE6DE] flex-shrink-0 flex flex-col transition-all duration-300 relative min-h-screen">

            <button onclick="toggleSidebar()"
                    class="absolute -right-4 top-24 w-9 h-9 rounded-full bg-white text-[#9A0002] shadow-xl flex items-center justify-center z-30">
                <i id="toggle-icon" data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>

            {{-- Logo --}}
            <div class="p-7">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center">
                        <i data-lucide="brain-circuit" class="w-6 h-6 text-white"></i>
                    </div>
                    <div class="logo-text">
                        <h1 class="text-2xl font-black text-[#9A0002]">AMCortex</h1>
                        <p class="text-xs text-gray-400">Smart Exam Platform</p>
                    </div>
                </div>
            </div>

            {{-- Menu --}}
            <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
                @php
                    $menu = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                        ['route' => 'exams.index', 'label' => 'Examens', 'icon' => 'file-text'],
                        ['route' => 'questions.global', 'label' => 'Questions', 'icon' => 'database'],
                        ['route' => 'students.index', 'label' => 'Étudiants', 'icon' => 'users'],
                        ['route' => 'exams.scan.index', 'label' => 'Scanner Copies', 'icon' => 'scan'],
                        ['route' => 'results.index', 'label' => 'Résultats', 'icon' => 'bar-chart-3'],
                        ['route' => 'analytics.index', 'label' => 'Statistiques', 'icon' => 'chart-no-axes-combined'],
                        ['route' => 'settings', 'label' => 'Paramètres', 'icon' => 'settings'],
                    ];
                @endphp

                @foreach($menu as $item)
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="nav-item flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300
                        {{ request()->routeIs($item['route'])
                            ? 'bg-[#9A0002] text-white shadow-lg'
                            : 'text-gray-600 hover:bg-[#9A0002]/10 hover:text-[#9A0002]' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="nav-label font-bold">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Sidebar Card --}}
            <div class="sidebar-card mx-4 mb-4 p-5 rounded-[2rem] bg-[#9A0002]/5 border border-[#9A0002]/10 border border-white/10">
                <div class="w-14 h-14 rounded-2xl bg-[#9A0002] text-white hover:bg-[#7A0001] flex items-center justify-center mb-4">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <h3 class="font-black">Correction rapide</h3>
                <p class="text-sm text-white/70 mt-1">Scannez et corrigez vos copies AMC facilement.</p>

                <a href="{{ route('exams.scan.index') }}"
                   class="mt-4 inline-flex w-full justify-center py-3 rounded-2xl bg-white text-[#9A0002] font-black hover:scale-105 transition">
                    Commencer
                </a>
            </div>

            {{-- Logout --}}
            <div class="p-4 border-t border-white/10">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-gray-500 hover:bg-red-50 hover:text-[#9A0002] hover:text-white transition">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span class="logout-label font-bold">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN --}}
        <div class="flex-1 flex flex-col min-w-0 bg-[#FAF7F4]">

            {{-- TOPBAR --}}
            <header class="relative z-[9999] h-24 bg-white/80 backdrop-blur-xl border-b border-[#EFE6DE] flex items-center justify-between px-8 shrink-0">

                <div class="hidden md:flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                        <i data-lucide="brain-circuit" class="w-6 h-6"></i>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">
                            AMCortex Workspace
                        </p>
                        <h2 class="text-xl font-black text-gray-950">
                            Correction intelligente des examens
                        </h2>
                    </div>
                </div>

                <div class="md:hidden flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center">
                        <i data-lucide="brain-circuit" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-xl font-black text-[#9A0002]">AMCortex</h2>
                </div>

                <div class="flex items-center gap-4">
                    <div id="notificationWrapper" class="relative z-[10000]">
                        <button type="button"
                                onclick="toggleNotifications(event)"
                                class="relative w-12 h-12 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-600 hover:text-[#9A0002] transition">
                            <i data-lucide="bell" class="w-5 h-5"></i>

                            @if(session('success') || session('error') || $errors->any())
                                <span class="absolute top-3 right-3 w-2 h-2 bg-[#9A0002] rounded-full"></span>
                            @endif
                        </button>

                        <div id="notificationsDropdown"
                             class="hidden absolute right-0 mt-4 w-96 max-w-[90vw] bg-white border border-[#EFE6DE] rounded-[2rem] shadow-2xl z-[10000] overflow-hidden">

                            <div class="p-5 border-b border-[#EFE6DE] bg-[#FAF7F4]">
                                <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">Notifications</p>
                            </div>

                            <div class="p-4 space-y-3 max-h-96 overflow-y-auto">

                                @if(session('success'))
                                    <div class="rounded-2xl bg-green-50 border border-green-100 p-4 text-green-700">
                                        <p class="font-black">Succès</p>
                                        <p class="text-sm font-semibold mt-1">{{ session('success') }}</p>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div class="rounded-2xl bg-red-50 border border-red-100 p-4 text-red-700">
                                        <p class="font-black">Erreur</p>
                                        <p class="text-sm font-semibold mt-1">{{ session('error') }}</p>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="rounded-2xl bg-amber-50 border border-amber-100 p-4 text-amber-700">
                                        <p class="font-black">Validation</p>
                                        <ul class="text-sm font-semibold mt-1 space-y-1">
                                            @foreach($errors->all() as $error)
                                                <li>• {{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if(!session('success') && !session('error') && !$errors->any())
                                    <div class="text-center py-8">
                                        <div class="w-14 h-14 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center mx-auto mb-3">
                                            <i data-lucide="bell-check" class="w-7 h-7"></i>
                                        </div>
                                        <p class="font-black text-gray-950">Aucune notification</p>
                                        <p class="text-sm text-gray-500 mt-1">Tout est clair pour le moment.</p>
                                    </div>
                                @endif

                            </div>

                            <a href="{{ route('settings') }}"
                               class="block text-center p-4 bg-[#FAF7F4] border-t border-[#EFE6DE] text-[#9A0002] font-black hover:bg-[#9A0002] hover:text-white transition">
                                Gérer les notifications
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pl-4 border-l border-[#EFE6DE]">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-black text-gray-900">{{ auth()->user()->full_name }}</p>
                            <p class="text-[10px] font-black text-[#9A0002] uppercase tracking-widest">Professeur</p>
                        </div>

                        <div class="w-12 h-12 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center font-black shadow-lg">
                            {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            {{-- CONTENT --}}
            <main class="flex-1 overflow-y-auto p-8">
                
                @yield('content')
            </main>
        </div>
    
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

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('toggle-icon');

        sidebar.classList.toggle('sidebar-collapsed');

        icon.setAttribute(
            'data-lucide',
            sidebar.classList.contains('sidebar-collapsed') ? 'chevron-right' : 'chevron-left'
        );

        lucide.createIcons();
    }

    function toggleNotifications(event) {
        event.stopPropagation();

        const dropdown = document.getElementById('notificationsDropdown');
        dropdown.classList.toggle('hidden');

        if (window.lucide) lucide.createIcons();
    }

    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('notificationsDropdown');
        const wrapper = document.getElementById('notificationWrapper');

        if (dropdown && wrapper && !wrapper.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>

</body>
</html>