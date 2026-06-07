@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')

<div class="max-w-7xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#9A0002] to-[#6f0001] p-8 shadow-2xl">
        <div class="absolute -top-24 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 left-20 w-72 h-72 bg-black/10 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-white/80 font-bold">Bonjour, Pr. {{ explode(' ', auth()->user()->full_name)[0] }} </p>
                <h1 class="text-4xl md:text-5xl font-black text-white mt-2">Tableau de bord</h1>
                <p class="text-white/80 mt-3 max-w-xl">
                    Gérez vos examens, vos questions et la correction automatique depuis un seul espace.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('exams.create') }}"
                   class="px-6 py-4 rounded-2xl bg-white text-[#9A0002] font-black shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all">
                    + Créer examen
                </a>

                <a href="{{ route('exams.scan.index') }}"
                   class="px-6 py-4 rounded-2xl bg-black/25 text-white font-black border border-white/20 hover:bg-black/35 hover:-translate-y-1 transition-all">
                    Scanner copies
                </a>
            </div>
        </div>
    </div>
    {{-- DOCUMENTATION BUTTON --}}
<div class="relative">

    {{-- Small floating button --}}
    <button onclick="toggleDocCard()"
            id="docButton"
            class="group flex items-center gap-4 px-6 py-4 rounded-[2rem] bg-white border border-[#EFE6DE] shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all">

        <div class="w-16 h-16 rounded-[1.5rem] bg-[#9A0002] text-white flex items-center justify-center shadow-lg group-hover:rotate-6 group-hover:scale-110 transition-all">
            <i data-lucide="book-open-check" class="w-8 h-8"></i>
        </div>

        <div class="text-left">
            <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">
                Documentation
            </p>
            <p class="font-black text-gray-950">
                Comment fonctionne AMCortex ?
            </p>
            <p class="font-black text-red-950">
                Installation des dépendances AMCortex
            </p>
        </div>
    </button>

    {{-- Hidden animated card --}}
    <div id="docCard"
         class="doc-card hidden mt-6 relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">

        <div class="absolute -right-16 -top-16 w-72 h-72 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row items-start gap-6">
            <div class="w-20 h-20 rounded-[2rem] bg-[#9A0002] text-white flex items-center justify-center shadow-xl">
                <i data-lucide="book-open-check" class="w-10 h-10"></i>
            </div>

            <div>
                <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">
                    Documentation AMCortex
                </p>

                <h2 class="text-3xl font-black text-gray-950 mt-2">
                    Comprendre le fonctionnement complet de la plateforme
                </h2>

                <p class="text-gray-500 font-medium mt-3 max-w-3xl leading-relaxed">
                    Découvrez tout le parcours AMCortex : création d’examens,
                    importation CSV/JSON, génération PDF AMC, scan des copies,
                    correction automatique, association des étudiants, calcul des notes,
                    téléchargement des copies corrigées et historique des résultats.
                </p>

                <div class="flex flex-wrap gap-3 mt-6">

                    <a href="{{ route('settings') }}"
                       class="px-6 py-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-700 font-black hover:text-[#9A0002] hover:border-[#9A0002] transition-all">
                        Comment fonctionne AMCortex ?
                    </a>
                </div>
            </div>
             {{-- INSTALL ALERT --}}
<div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 shadow-sm">

    <div class="flex items-start gap-4">

        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
            <i data-lucide="triangle-alert" class="w-7 h-7"></i>
        </div>

        <div class="flex-1">

            <p class="text-xs font-black uppercase tracking-widest text-amber-700">
                Configuration système
            </p>

            <h3 class="text-2xl font-black text-gray-900 mt-2">
                Installation des dépendances AMCortex
            </h3>

            <p class="text-gray-600 mt-3 leading-relaxed">
                AMCortex nécessite certains composants techniques pour fonctionner correctement :
                Auto-Multiple-Choice (AMC), Python, XeLaTeX et outils de traitement PDF.
            </p>

            <p class="text-gray-600 mt-2 leading-relaxed">
                Une installation automatique via script est recommandée afin de simplifier
                la configuration du système.
            </p>

            <div class="flex flex-wrap gap-3 mt-5">

                <a href="{{ route('download.installer') }}"
                   class="px-5 py-3 rounded-2xl bg-black text-white font-black">
                    Télécharger le script d'installation
                </a>

            </div>

        </div>

    </div>

</div>
        </div>
    </div>
</div>

    {{-- STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($stats as $label => $stat)
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 border border-white shadow-lg hover:-translate-y-2 hover:shadow-2xl transition-all duration-500">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#9A0002]/10 rounded-full blur-2xl"></div>

                <div class="relative flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-xs font-black uppercase tracking-wider">
                            {{ str_replace('_', ' ', $label) }}
                        </p>
                        <p class="text-4xl font-black text-gray-900 mt-2">{{ $stat['value'] }}</p>
                    </div>

                    <div class="w-14 h-14 rounded-3xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center group-hover:bg-[#9A0002] group-hover:text-white group-hover:rotate-6 group-hover:scale-110 transition-all">
                        <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MAIN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- EXAMENS --}}
        <div class="lg:col-span-2 rounded-[2.5rem] bg-white border border-white shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-black text-gray-900">Examens récents</h2>
                    <p class="text-gray-500 text-sm">Cliquez sur un examen pour l’ouvrir.</p>
                </div>

                <a href="{{ route('exams.index') }}"
                   class="px-4 py-2 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] font-black hover:bg-[#9A0002] hover:text-white transition">
                    Voir tout
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($recentExams as $exam)
                    <a href="{{ route('exams.show', $exam['id']) }}"
                       class="group relative overflow-hidden rounded-[2rem] p-5 bg-[#fbf8f5] border border-[#EFE6DE] shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">

                        <div class="absolute inset-x-0 top-0 h-2 bg-[#9A0002]"></div>

                        <div class="flex items-start justify-between mt-3">
                            <div class="w-14 h-14 rounded-3xl bg-white text-[#9A0002] flex items-center justify-center shadow-sm group-hover:bg-[#9A0002] group-hover:text-white group-hover:rotate-6 transition">
                                <i data-lucide="file-text" class="w-6 h-6"></i>
                            </div>

                            <span class="px-3 py-1 rounded-full text-xs font-black
                                {{ $exam['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $exam['status'] === 'published' ? 'Disponible' : 'En préparation' }}
                            </span>
                        </div>

                        <h3 class="text-xl font-black text-gray-900 mt-5 group-hover:text-[#9A0002] transition">
                            {{ $exam['title'] }}
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">{{ $exam['course_name'] }}</p>

                        <div class="grid grid-cols-3 gap-3 mt-5">
                            <div class="rounded-2xl bg-white p-3 text-center border border-[#EFE6DE]">
                                <p class="text-2xl font-black text-[#9A0002]">{{ $exam['questions_count'] }}</p>
                                <p class="text-[10px] font-black text-gray-400 uppercase">Questions</p>
                            </div>

                            <div class="rounded-2xl bg-white p-3 text-center border border-[#EFE6DE]">
                                <p class="text-2xl font-black text-[#9A0002]">{{ $exam['scanned_copies'] }}</p>
                                <p class="text-[10px] font-black text-gray-400 uppercase">Copies</p>
                            </div>

                            <div class="rounded-2xl bg-white p-3 text-center border border-[#EFE6DE]">
                                <p class="text-2xl font-black text-[#9A0002]">{{ $exam['total_points'] }}</p>
                                <p class="text-[10px] font-black text-gray-400 uppercase">Points</p>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between text-sm font-black text-gray-400 group-hover:text-[#9A0002]">
                            <span>Ouvrir</span>
                            <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-2 transition"></i>
                        </div>
                    </a>
                @empty
                    <div class="md:col-span-2 p-10 text-center text-gray-400">
                        Aucun examen récent.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RESUME --}}
        <div class="rounded-[2.5rem] bg-white border border-white shadow-xl p-6">
            @php
                $total = $stats['total_exams']['value'];
                $progress = $total ? round(($quickStats['published_exams'] / $total) * 100) : 0;
            @endphp

            <h2 class="text-2xl font-black text-gray-900">Résumé</h2>
            <p class="text-sm text-gray-500 mb-6">Progression de vos examens</p>

            <div class="relative mx-auto w-44 h-44 rounded-full bg-[#9A0002] p-3 shadow-xl">
                <div class="w-full h-full rounded-full bg-white flex flex-col items-center justify-center">
                    <p class="text-4xl font-black text-gray-900">{{ $progress }}%</p>
                    <p class="text-xs font-bold text-gray-400">progression</p>
                </div>
            </div>

            <div class="mt-8 space-y-4">
                <div class="p-4 rounded-3xl bg-green-50 flex justify-between items-center">
                    <span class="font-bold text-green-700">Disponibles</span>
                    <span class="text-2xl font-black text-green-700">{{ $quickStats['published_exams'] }}</span>
                </div>

                <div class="p-4 rounded-3xl bg-amber-50 flex justify-between items-center">
                    <span class="font-bold text-amber-700">En préparation</span>
                    <span class="text-2xl font-black text-amber-700">{{ $quickStats['draft_exams'] }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
</div>

<style>
    .doc-card {
        animation: docReveal 0.45s ease forwards;
        transform-origin: top left;
    }

    @keyframes docReveal {
        from {
            opacity: 0;
            transform: translateY(-18px) scale(0.96);
            filter: blur(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0);
        }
    }
</style>
<script>
    function toggleDocCard() {
        const card = document.getElementById('docCard');
        card.classList.toggle('hidden');

        if (window.lucide) lucide.createIcons();
    }

    if (window.lucide) lucide.createIcons();

    if (window.lucide) lucide.createIcons();
</script>
@endsection