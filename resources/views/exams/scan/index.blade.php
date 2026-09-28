@extends('layouts.app')

@section('title', 'Scanner les copies')

@section('content')
<div class="space-y-8">

    {{-- Notifications --}}
    @if(session('success') || session('error'))
        <div class="rounded-2xl p-5 flex items-start gap-4 border shadow-sm
            {{ session('success') ? 'text-green-800 bg-green-50 border-green-100' : 'text-red-800 bg-red-50 border-red-100' }}">
            <i data-lucide="{{ session('success') ? 'check-circle-2' : 'alert-circle' }}" class="w-5 h-5 mt-0.5"></i>
            <div class="font-bold">
                {{ session('success') ?? session('error') }}
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-[#111827] text-white p-8 shadow-xl">
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#9A0002]/40 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-white/50">AMC Pipeline</p>
                <h1 class="text-4xl font-black mt-2">Correction Automatisée</h1>

                @if($exam)
                    <p class="mt-2 text-white/70">
                        Examen sélectionné :
                        <span class="font-black text-white">{{ $exam->title }}</span>
                    </p>
                @else
                    <p class="mt-2 text-white/70">Sélectionnez un examen pour démarrer le workflow AMC.</p>
                @endif
            </div>

            @if($exam)
                <a href="{{ route('exams.scan.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-white text-gray-900 rounded-2xl font-black hover:-translate-y-1 transition">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    Changer d'examen
                </a>
            @endif
            
        </div>
    </div>

    {{-- Liste des examens --}}
    @if(!$exam)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($exams as $e)
                <a href="{{ route('exams.scan.index.exam', $e->id) }}"
                   class="group relative overflow-hidden bg-white border border-[#EFE6DE] rounded-[2rem] p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all">
                    <div class="absolute top-0 left-0 h-full w-1.5 bg-[#9A0002]"></div>

                    <div class="flex items-start justify-between">
                        <div class="w-14 h-14 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center group-hover:bg-[#9A0002] group-hover:text-white transition">
                            <i data-lucide="file-text" class="w-7 h-7"></i>
                        </div>

                        <span class="text-xs font-black px-3 py-1 rounded-full bg-[#FAF7F4] border border-[#EFE6DE] text-gray-500">
                            {{ $e->title}}_{{ $e->id }}
                        </span>
                    </div>

                    <div class="mt-5">
                        <h3 class="text-xl font-black text-gray-950 group-hover:text-[#9A0002] transition">
                            {{ $e->title }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            {{ $e->course_name ?? 'Sans cours' }}
                        </p>
                    </div>

                    <div class="mt-6 flex items-center justify-between pt-4 border-t border-[#EFE6DE]">
                        <span class="text-sm text-gray-500">Commencer le scan</span>
                        <span class="inline-flex items-center gap-1 text-[#9A0002] font-black">
                            Ouvrir
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
                        </span>
                    </div>
                </a>
            @empty
                <div class="col-span-full bg-white border border-dashed border-[#EFE6DE] rounded-[2rem] p-10 text-center text-gray-500">
                    Aucun examen trouvé.
                </div>
            @endforelse
        </div>
        <div class="mt-6">
            {{ $exams->links() }}
        </div>
    @else

        @php
            $status = $exam->scan_status ?? 'pending';

            $statusConfig = [
                'pending' => ['label' => 'En attente', 'color' => 'bg-gray-100 text-gray-700'],
                'uploaded' => ['label' => 'Upload terminé', 'color' => 'bg-blue-100 text-blue-700'],
                'analysed' => ['label' => 'Analyse terminée', 'color' => 'bg-indigo-100 text-indigo-700'],
                'associated' => ['label' => 'Association terminée', 'color' => 'bg-amber-100 text-amber-700'],
                'graded' => ['label' => 'Notation terminée', 'color' => 'bg-green-100 text-green-700'],
                'error' => ['label' => 'Erreur', 'color' => 'bg-red-100 text-red-700'],
            ];

            $currentStatus = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => 'bg-gray-100 text-gray-700'];
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            {{-- Side panel --}}
            <div class="xl:col-span-4 space-y-6">
                <div class="rounded-[2rem] overflow-hidden bg-white border border-[#EFE6DE] shadow-xl">
                    <div class="bg-[#9A0002] text-white p-6">
                        <p class="text-xs font-black uppercase tracking-widest text-white/60">Résumé</p>
                        <div class="flex items-center justify-between mt-3">
                            <h3 class="text-2xl font-black">{{ $exam->title }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-black bg-white text-[#9A0002]">
                                {{ $currentStatus['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-[#EFE6DE] pb-4">
                            <span class="font-bold text-gray-500">Copies lues</span>
                            <span class="text-4xl font-black text-gray-950">{{ $stats['total'] ?? 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between border-b border-[#EFE6DE] pb-4">
                            <span class="font-bold text-gray-500">Réussies</span>
                            <span class="text-4xl font-black text-green-600">{{ $stats['completed'] ?? 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-500">Erreurs</span>
                            <span class="text-4xl font-black text-red-600">{{ $stats['errors'] ?? 0 }}</span>
                        </div>

                        <div class="pt-4 space-y-3">
                            <a href="{{ route('exams.show', $exam->id) }}"
                               class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-700 font-black hover:text-[#9A0002] transition">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Voir le sujet
                            </a>

                            @if($status === 'graded')
                                <a href="{{ route('exams.scan.export', $exam->id) }}"
                                   class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Télécharger le CSV
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="xl:col-span-8">
                <div class="rounded-[2rem] bg-white border border-[#EFE6DE] shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-[#EFE6DE] flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#111827] text-white flex items-center justify-center">
                            <i data-lucide="scan-line" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-950">Workflow AMC</h2>
                            <p class="text-sm text-gray-500">Suivez les étapes dans l’ordre pour corriger les copies.</p>
                        </div>
                    </div>

                    <div class="divide-y divide-[#EFE6DE]">

                        {{-- Step 1 --}}
                        <section class="p-6 hover:bg-[#FAF7F4] transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#9A0002] text-white flex items-center justify-center font-black">1</div>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-950">Importer les scan</h3>
                                        <p class="text-sm text-gray-500 mt-1">Ajoutez les scans PDF/JPG/PNG des copies.</p>
                                    </div>
                                </div>

                                @if(!in_array($status, ['pending','error']))
                                    <span class="text-green-600 font-black text-sm">✔ Terminé</span>
                                @endif
                            </div>

                            @if(in_array($status, ['pending','error']))
                                <form action="{{ route('exams.scan.upload', $exam->id) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                                    @csrf
                                    <input type="file" name="scans[]" multiple required
                                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-3 file:px-4 file:rounded-2xl file:border-0 file:bg-[#9A0002] file:text-white file:font-black hover:file:bg-[#7A0001]">
                                    <button class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition">
                                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                        Importer
                                    </button>
                                </form>
                            @else
                            @if($exam && $exam->scan_status === 'uploaded')
                                <div class="rounded-2xl p-5 bg-yellow-50 border border-yellow-200 text-yellow-800 font-bold flex gap-3">
                                    <i data-lucide="triangle-alert" class="w-5 h-5"></i>
                                    <div>
                                        Vérifiez que les scans importés correspondent bien à l’examen
                                        <span class="text-[#9A0002]">{{ $exam->title }}</span>
                                        avant de lancer l’analyse.
                                    </div>
                                </div>
                            @endif
                                <div class="mt-5">
                                    <form action="{{ route('exams.scan.reset-upload', $exam->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white border border-[#EFE6DE] text-gray-700 font-black hover:text-[#9A0002] transition">
                                            <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                                            Réimporter les scans
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </section>

                        {{-- Step 2 --}}
                        <section class="p-6 hover:bg-[#FAF7F4] transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#111827] text-white flex items-center justify-center font-black">2</div>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-950">Analyse AMC</h3>
                                        <p class="text-sm text-gray-500 mt-1">Lecture automatique des copies scannées.</p>
                                    </div>
                                </div>

                                @if(in_array($status, ['analysed','associated','graded']))
                                    <span class="text-green-600 font-black text-sm">✔ Terminé</span>
                                @endif
                            </div>

                            @if($status === 'uploaded')
                                <form method="POST" action="{{ route('exams.scan.analyse', $exam->id) }}" class="mt-5">
                                    @csrf
                                    <button class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#111827] text-white font-black hover:bg-black transition">
                                        <i data-lucide="search-check" class="w-4 h-4"></i>
                                        Lancer l’analyse
                                    </button>
                                </form>
                            @endif
                        </section>

                        {{-- Step 3 --}}
                        <section class="p-6 hover:bg-[#FAF7F4] transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#111827] text-white flex items-center justify-center font-black">3</div>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-950">Association des copies</h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Associez chaque copie scannée au bon étudiant avant la notation.
                                        </p>
                                    </div>
                                </div>

                                @if(in_array($status, ['associated','graded']))
                                    <span class="text-green-600 font-black text-sm">✔ Terminé</span>
                                @endif
                            </div>

                            @if($status === 'analysed')
                                @php
                                    $slug = \Illuminate\Support\Str::slug($exam->title ?? 'exam', '-');
                                    if ($slug === '') { $slug = 'exam'; }

                                    $metaFile = storage_path("app/amc/{$slug}_{$exam->id}/session_meta.json");

                                    $mode = 'anonymous';
                                    $hasSelectedStudents = false;

                                    if (file_exists($metaFile)) {
                                        $meta = json_decode(file_get_contents($metaFile), true);
                                        $mode = $meta['mode'] ?? 'anonymous';
                                        $hasSelectedStudents = !empty($meta['student_codes']) || !empty($meta['temporary_students']);
                                    }
                                @endphp

                                <div class="mt-5 rounded-2xl border border-amber-100 bg-amber-50 p-5 text-amber-800">
                                    <div class="flex gap-3">
                                        <i data-lucide="info" class="w-5 h-5 mt-0.5"></i>
                                        <div>
                                            <p class="font-black">Association recommandée</p>

                                            @if($mode === 'named')
                                                <p class="text-sm mt-1">
                                                    Cet examen est en mode nominatif. Lancez d’abord l’association automatique.
                                                    Si AMC ne reconnaît pas correctement les copies, vous serez redirigé vers l’association manuelle.
                                                </p>
                                            @elseif($mode === 'anonymous' && $hasSelectedStudents)
                                                <p class="text-sm mt-1">
                                                    Cet examen est anonyme avec une liste d’étudiants. L’association automatique n’est pas fiable dans ce cas.
                                                    Veuillez associer chaque copie manuellement.
                                                </p>
                                            @else
                                                <p class="text-sm mt-1">
                                                    Cet examen est anonyme sans liste d’étudiants. Aucune association étudiant n’est nécessaire.
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    @if($mode === 'named')
                                        <form method="POST" action="{{ route('exams.scan.associate', $exam->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#9A0002] hover:bg-[#7A0001] text-white font-black shadow-lg">
                                                <i data-lucide="wand-sparkles" class="w-4 h-4"></i>
                                                Lancer l’association automatique
                                            </button>
                                        </form>

                                        <a href="{{ route('exams.scan.manual', $exam->id) }}"
                                           class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white border border-[#EFE6DE] hover:bg-[#FAF7F4] text-gray-700 font-black">
                                            <i data-lucide="hand" class="w-4 h-4"></i>
                                            Association manuelle
                                        </a>
                                    @elseif($mode === 'anonymous' && $hasSelectedStudents)
                                        <a href="{{ route('exams.scan.manual', $exam->id) }}"
                                           class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#111827] hover:bg-black text-white font-black shadow-lg">
                                            <i data-lucide="user-check" class="w-4 h-4"></i>
                                            Ouvrir l’association manuelle
                                        </a>
                                    @else
                                        <form method="POST" action="{{ route('exams.scan.associate', $exam->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white font-black shadow-lg">
                                                <i data-lucide="skip-forward" class="w-4 h-4"></i>
                                                Continuer sans association
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            @if(in_array($status, ['associated','graded']))
                                <form method="POST" action="{{ route('exams.scan.reset-association', $exam->id) }}" class="mt-3">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white border border-[#EFE6DE] text-gray-700 font-black hover:text-[#9A0002] transition">
                                        <i data-lucide="refresh-ccw" class="w-4 h-4"></i>
                                        Refaire l’association
                                    </button>
                                </form>
                            @endif
                        </section>

                        {{-- Step 4 --}}
                        <section class="p-6 hover:bg-[#FAF7F4] transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#111827] text-white flex items-center justify-center font-black">4</div>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-950">Notation & Export</h3>
                                        <p class="text-sm text-gray-500 mt-1">Calcul des notes et génération du fichier CSV.</p>
                                    </div>
                                </div>

                                @if($status === 'graded')
                                    <span class="text-green-600 font-black text-sm">✔ Terminé</span>
                                @endif
                            </div>

                            @if($status === 'associated')
                                <form method="POST" action="{{ route('exams.scan.grade', $exam->id) }}" class="mt-5">
                                    @csrf
                                    <button class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 text-white font-black hover:bg-green-700 transition">
                                        <i data-lucide="calculator" class="w-4 h-4"></i>
                                        Calculer les notes
                                    </button>
                                </form>
                            @endif
                        </section>

                        {{-- Step 5 --}}
                        @if($status === 'graded')
                            <section class="p-6 bg-green-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex gap-4">
                                        <div class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center font-black">5</div>
                                        <div>
                                            <h3 class="text-lg font-black text-green-900">Résultats prêts</h3>
                                            <p class="text-sm text-green-700 mt-1">Le fichier des notes est prêt au téléchargement.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <a href="{{ route('exams.scan.export', $exam->id) }}"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 text-white font-black hover:bg-green-700 transition">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                        Télécharger CSV
                                    </a>

                                    <a href="{{ route('results.exam', $exam->id) }}"
                                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-gray-700 border border-green-100 font-black hover:bg-green-100 transition">
                                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                                        Voir résultats
                                    </a>
                                </div>
                            </section>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
@endsection