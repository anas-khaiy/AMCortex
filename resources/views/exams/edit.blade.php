@extends('layouts.app')

@section('title', 'Modifier l\'examen')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center gap-5">
            <a href="{{ route('exams.index') }}"
               class="w-14 h-14 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-600 hover:bg-[#9A0002] hover:text-white hover:-translate-x-1 transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>

            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Modification</p>
                <h1 class="text-4xl font-black text-gray-950 mt-1">{{ $exam->title }}</h1>
                <p class="text-gray-500 font-medium mt-1">Mettez à jour les paramètres principaux de cet examen.</p>
            </div>
        </div>
    </div>
    

    <form action="{{ route('exams.update', $exam->id) }}" method="POST" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        @csrf
        @method('PUT')

        <div class="xl:col-span-2 rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8 space-y-7">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-950">Informations de l’examen</h2>
                    <p class="text-sm text-gray-500">Ces informations seront utilisées lors de la génération AMC.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Titre de l’examen *</label>
                    <input type="text" name="title" value="{{ old('title', $exam->title) }}" required
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Nom du cours *</label>
                    <input type="text" name="course_name" value="{{ old('course_name', $exam->course_name) }}" required
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Durée</label>
                    <div class="relative">
                        <input type="number" name="duration" value="{{ old('duration', $exam->duration) }}"
                               class="w-full px-5 py-4 pr-20 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">min</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-gray-700">Taille ID étudiant</label>
                    <input type="number" name="student_id_length" value="{{ old('student_id_length', $exam->student_id_length) }}"
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Description</label>
                <textarea name="description" rows="3"
                          class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">{{ old('description', $exam->description) }}</textarea>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Instructions</label>
                <textarea name="instructions" rows="4"
                          class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">{{ old('instructions', $exam->instructions) }}</textarea>
            </div>
        </div>

        {{-- SIDE PANEL --}}
        <div class="space-y-6">
            <div class="rounded-[2.5rem] bg-[#9A0002] text-white shadow-xl p-7 relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-44 h-44 bg-white/10 rounded-full blur-2xl"></div>

                <h3 class="relative text-2xl font-black">Résumé</h3>
                <p class="relative text-white/70 text-sm mt-1">État actuel de l’examen.</p>

                <div class="relative mt-6 space-y-4">
                    <div class="p-4 rounded-2xl bg-white/10 border border-white/10 flex justify-between">
                        <span class="font-bold text-white/80">Total points</span>
                        <span class="font-black">{{ $exam->total_points ?: $exam->total_calculated_points }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/10 border border-white/10 flex justify-between">
                        <span class="font-bold text-white/80">Questions</span>
                        <span class="font-black">{{ $exam->questions()->count() }}</span>
                    </div>

                    <div class="p-4 rounded-2xl bg-white/10 border border-white/10 flex justify-between">
                        <span class="font-bold text-white/80">Créé le</span>
                        <span class="font-black">{{ $exam->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-7 space-y-4">
                <h3 class="text-xl font-black text-gray-950">Randomisation</h3>

                <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] cursor-pointer">
                    <span class="font-black text-gray-700">Mélanger les questions</span>
                    <input type="checkbox" name="shuffle_questions" {{ $exam->shuffle_questions ? 'checked' : '' }} class="w-5 h-5 accent-[#9A0002]">
                </label>

                <label class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] cursor-pointer">
                    <span class="font-black text-gray-700">Mélanger les réponses</span>
                    <input type="checkbox" name="shuffle_answers" {{ $exam->shuffle_answers ? 'checked' : '' }} class="w-5 h-5 accent-[#9A0002]">
                </label>

                <button type="submit"
                        class="w-full mt-4 px-6 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    Mettre à jour
                    <i data-lucide="save" class="w-5 h-5"></i>
                </button>

                <a href="{{ route('exams.index') }}"
                   class="w-full flex items-center justify-center px-6 py-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-600 font-black hover:text-[#9A0002] transition">
                    Annuler
                </a>
            </div>
        </div>
    </form>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
@endsection