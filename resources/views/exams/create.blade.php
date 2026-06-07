@extends('layouts.app')

@section('title', 'Créer un Examen')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <a href="{{ route('exams.index') }}"
                   class="w-14 h-14 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-600 hover:bg-[#9A0002] hover:text-white hover:-translate-x-1 transition-all">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>

                <div>
                    <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Nouvel examen</p>
                    <h1 class="text-4xl font-black text-gray-950 mt-1">Créer un examen</h1>
                    <p class="text-gray-500 font-medium mt-1">Configurez les informations principales avant d’ajouter les questions.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- STEPS --}}
    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-lg p-7">
        <div class="relative max-w-3xl mx-auto">
            <div class="absolute top-7 left-10 right-10 h-1 bg-[#EFE6DE] rounded-full"></div>
            <div class="absolute top-7 left-10 h-1 w-1/3 bg-[#9A0002] rounded-full"></div>

            <div class="relative grid grid-cols-3 gap-4">
                @php
                    $steps = [
                        ['number' => 1, 'title' => 'Infos examen', 'active' => true],
                        ['number' => 2, 'title' => 'Questions', 'active' => false],
                        ['number' => 3, 'title' => 'Génération', 'active' => false],
                    ];
                @endphp

                @foreach($steps as $step)
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black shadow-sm
                            {{ $step['active'] ? 'bg-[#9A0002] text-white scale-110' : 'bg-[#FAF7F4] text-gray-400 border border-[#EFE6DE]' }}">
                            {{ $step['number'] }}
                        </div>
                        <p class="mt-4 text-xs font-black uppercase tracking-wider
                            {{ $step['active'] ? 'text-[#9A0002]' : 'text-gray-400' }}">
                            {{ $step['title'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <form action="{{ route('exams.store') }}" method="POST" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        @csrf

        <div class="xl:col-span-2 rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8 space-y-7">

            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-950">Informations générales</h2>
                    <p class="text-sm text-gray-500">Ces données seront utilisées dans le sujet AMC.</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Titre de l’examen *</label>
                <input type="text" name="title" required placeholder="Ex : Algorithmique Avancée"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Description</label>
                <textarea name="description" rows="3" placeholder="Objectifs, remarques, contexte..."
                          class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Nom du cours *</label>
                    <input type="text" name="course_name" required
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Langue de l’examen</label>
                    <select name="exam_language"
                            class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] transition">
                        <option value="fr">Français</option>
                        <option value="en">Anglais</option>
                        <option value="ar">Arabe</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Nom de l’enseignant</label>
                    <input type="text" name="teacher_name" value="{{ auth()->user()->full_name }}"
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Durée *</label>
                    <div class="relative">
                        <input type="number" name="duration" placeholder="90"
                               class="w-full px-5 py-4 pr-20 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition" min="1" required>
                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">min</span>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Instructions pour étudiants</label>
                <textarea name="instructions" rows="4" placeholder="Remplir les cases correctement..."
                          class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition"></textarea>
            </div>
        </div>

        {{-- SIDE SETTINGS --}}
        <div class="space-y-6">
            <div class="rounded-[2.5rem] bg-[#9A0002] text-white shadow-xl p-7 relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-44 h-44 bg-white/10 rounded-full blur-2xl"></div>

                <h3 class="relative text-2xl font-black">Paramètres AMC</h3>
                <p class="relative text-white/70 text-sm mt-1">Format, code étudiant et mélange.</p>

                <div class="relative mt-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-black text-white/90">Format de page</label>
                        <select name="page_format" id="page_format"
                                class="w-full px-5 py-4 rounded-2xl bg-white text-gray-900 font-black outline-none">
                            <option value="A4">A4 — Standard</option>
                            <option value="A3">A3 — Grand format</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black text-white/90">Longueur ID étudiant</label>
                        <input type="number" name="student_id_length" value="6"
                               class="w-full px-5 py-4 rounded-2xl bg-white text-gray-900 font-black outline-none">
                    </div>
                </div>
            </div>

            <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-7 space-y-4">
                <h3 class="text-xl font-black text-gray-950">Randomisation</h3>

                <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] cursor-pointer">
                    <span class="font-black text-gray-700">Mélanger les questions</span>
                    <input type="checkbox" name="shuffle_questions" checked class="w-5 h-5 accent-[#9A0002]">
                </label>

                <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] cursor-pointer">
                    <span class="font-black text-gray-700">Mélanger les réponses</span>
                    <input type="checkbox" name="shuffle_answers" checked class="w-5 h-5 accent-[#9A0002]">
                </label>

                <button type="submit"
                        class="w-full mt-4 px-6 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    Continuer vers les questions
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
@endsection