@extends('layouts.app')

@section('content')
<div class="w-full px-8 lg:px-16 space-y-10">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-6">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Examen</p>
                <h1 class="text-4xl font-black text-gray-950 mt-1">{{ $exam->title }}</h1>
                <p class="text-gray-500 font-medium mt-2">
                    {{ $exam->course_name }} 
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('questions.create', $exam->id) }}"
                   class="px-6 py-3 rounded-2xl bg-[#9A0002] text-white font-bold shadow hover:bg-[#7A0001] transition">
                    + Ajouter question
                </a>

                <a href="{{ route('exams.index') }}"
                   class="px-6 py-3 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-600 font-bold hover:text-[#9A0002] transition">
                    Retour
                </a>
            </div>
        </div>
    </div>
    {{-- EXAM PAPER PREVIEW --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white border border-[#EFE6DE] shadow-xl rounded-[2.5rem] p-8">
            <div class="border-2 border-[#EFE6DE] rounded-[2rem] p-8 bg-[#FFFCF8]">

                <div class="text-center border-b border-[#EFE6DE] pb-6">
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-[#9A0002]">
                        Informations d’examen
                    </p>
                    <h2 class="text-3xl font-black text-gray-950 mt-3">
                        {{ $exam->title }}
                    </h2>
                    <p class="text-gray-500 font-semibold mt-1">
                        {{ $exam->course_name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 text-sm">
                    <div class="p-4 rounded-2xl bg-white border border-[#EFE6DE]">
                        <p class="text-gray-400 font-black uppercase text-xs">Enseignant</p>
                        <p class="font-black text-gray-900 mt-1">{{ $exam->teacher_name ?? '—' }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white border border-[#EFE6DE]">
                        <p class="text-gray-400 font-black uppercase text-xs">Durée</p>
                        <p class="font-black text-gray-900 mt-1">{{ $exam->duration ?? '—' }} minutes</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white border border-[#EFE6DE]">
                        <p class="text-gray-400 font-black uppercase text-xs">Langue</p>
                        <p class="font-black text-gray-900 mt-1">
                            @if($exam->exam_language === 'ar')
                                Arabe
                            @elseif($exam->exam_language === 'en')
                                Anglais
                            @else
                                Français
                            @endif
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-white border border-[#EFE6DE]">
                        <p class="text-gray-400 font-black uppercase text-xs">Format</p>
                        <p class="font-black text-gray-900 mt-1">{{ $exam->page_format ?? 'A4' }}</p>
                    </div>
                </div>

                <div class="mt-6 p-5 rounded-2xl bg-white border border-[#EFE6DE]">
                    <p class="text-gray-400 font-black uppercase text-xs mb-2">Instructions</p>
                    <p class="text-gray-700 font-semibold leading-relaxed">
                        {{ $exam->instructions ?: 'Aucune instruction ajoutée.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-[#9A0002] text-white rounded-[2.5rem] p-8 shadow-xl">
            <p class="text-white/70 font-black uppercase text-xs">Résumé rapide</p>

            <div class="mt-6 space-y-4">
                <div class="flex justify-between items-center p-4 rounded-2xl bg-white/10">
                    <span class="font-bold">Questions</span>
                    <span class="text-2xl font-black">{{ $exam->questions->count() }}</span>
                </div>

                <div class="flex justify-between items-center p-4 rounded-2xl bg-white/10">
                    <span class="font-bold">Total points</span>
                    <span class="text-2xl font-black">{{ $exam->total_calculated_points }}</span>
                </div>

                <div class="flex justify-between items-center p-4 rounded-2xl bg-white/10">
                    <span class="font-bold">ID étudiant</span>
                    <span class="text-2xl font-black">{{ $exam->student_id_length }}</span>
                </div>

                <div class="flex justify-between items-center p-4 rounded-2xl bg-white/10">
                    <span class="font-bold">Créé le</span>
                    <span class="text-sm font-black">{{ $exam->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

    </div>

</div>

    {{-- QUESTIONS LIST --}}
    <div class="space-y-8 w-full">

        <h3 class="text-3xl font-black text-gray-900">Questions enregistrées</h3>

        @forelse($exam->questions as $question)

            <div class="group w-full p-10 rounded-[2rem] bg-white border border-[#EFE6DE] shadow-sm hover:shadow-xl transition-all">

                <div class="flex justify-between items-start gap-6 flex-wrap">

                    <p class="font-bold text-gray-900 text-xl leading-relaxed flex-1">
                        {{ $loop->iteration }}. {{ $question->question_text }}
                    </p>

                    <span class="px-4 py-2 text-xs font-bold rounded-xl bg-[#9A0002]/10 text-[#9A0002] whitespace-nowrap">
                        {{ $question->points_correct ?? 1 }} pts
                    </span>

                </div>

                {{-- ANSWERS --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-3">

                    @foreach($question->answers as $answer)

                        <div class="px-4 py-3 rounded-2xl border text-sm font-semibold transition
                            {{ $answer->is_correct 
                                ? 'bg-green-50 border-green-200 text-green-700 shadow-sm' 
                                : 'bg-[#FAF7F4] border-[#EFE6DE] text-gray-600 group-hover:bg-white' }}">

                            {{ $answer->answer_text }}

                            @if($answer->is_correct)
                                <span class="ml-2 text-xs font-bold">✔</span>
                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        @empty

            <div class="p-10 text-center rounded-3xl border border-dashed border-[#EFE6DE] bg-white text-gray-400">
                Aucune question ajoutée pour cet examen.
            </div>

        @endforelse

    </div>

</div>
@endsection