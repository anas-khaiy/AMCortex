@extends('layouts.app')

@section('title', 'Résultats')

@section('content')
<div class="space-y-10">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-[#9A0002] text-white p-8 shadow-2xl">
        <div class="absolute -right-24 -top-24 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute left-10 bottom-0 w-56 h-56 bg-black/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-6">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.3em] text-white/60">Historique</p>
                <h1 class="text-5xl font-black mt-2">Résultats</h1>
                <p class="text-white/75 mt-3 font-medium">
                    Sélectionnez un examen pour voir tout son historique.
                </p>
            </div>

            <div class="hidden md:flex w-20 h-20 rounded-[2rem] bg-white text-[#9A0002] items-center justify-center shadow-xl">
                <i data-lucide="bar-chart-3" class="w-9 h-9"></i>
            </div>
        </div>
    </div>

    {{-- EXAMS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($exams as $exam)
            @php
                $styles = [
                    ['bg' => 'bg-white', 'accent' => 'bg-[#9A0002]', 'soft' => 'bg-[#9A0002]/10 text-[#9A0002]', 'shape' => 'rounded-[2.7rem]'],
                    ['bg' => 'bg-[#FFFCF8]', 'accent' => 'bg-gray-950', 'soft' => 'bg-gray-950/10 text-gray-950', 'shape' => 'rounded-tl-[3rem] rounded-tr-[1.5rem] rounded-br-[3rem] rounded-bl-[1.5rem]'],
                    ['bg' => 'bg-[#FAF7F4]', 'accent' => 'bg-[#9A0002]', 'soft' => 'bg-white text-[#9A0002]', 'shape' => 'rounded-tl-[1.5rem] rounded-tr-[3rem] rounded-br-[1.5rem] rounded-bl-[3rem]'],
                ];
                $s = $styles[$loop->index % count($styles)];
            @endphp

            <a href="{{ route('results.exam', $exam->id) }}"
               class="group relative overflow-hidden {{ $s['bg'] }} {{ $s['shape'] }} border border-[#EFE6DE] p-7 shadow-lg hover:-translate-y-2 hover:shadow-2xl transition-all duration-500">

                <div class="absolute -right-16 -top-16 w-44 h-44 {{ $s['accent'] }} opacity-10 rounded-full blur-3xl"></div>
                <div class="absolute left-0 bottom-0 h-2 w-full {{ $s['accent'] }}"></div>

                <div class="relative flex items-start justify-between">
                    <div class="w-16 h-16 rounded-[1.5rem] {{ $s['soft'] }} flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all">
                        <i data-lucide="clipboard-check" class="w-7 h-7"></i>
                    </div>

                    <span class="px-4 py-2 rounded-full bg-white border border-[#EFE6DE] text-gray-700 text-xs font-black shadow-sm">
                        {{ $exam->result_sessions_count }} session(s)
                    </span>
                </div>

                <div class="relative mt-7 min-h-[105px]">
                    <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">
                        {{ $exam->course_name }}
                    </p>

                    <h3 class="text-2xl font-black text-gray-950 mt-2 group-hover:text-[#9A0002] transition">
                        {{ $exam->title }}
                    </h3>
                </div>

                <div class="relative mt-6 flex items-center justify-between">
                    <span class="text-sm font-black text-gray-400">Voir historique</span>

                    <div class="w-12 h-12 rounded-2xl {{ $s['accent'] }} text-white flex items-center justify-center group-hover:translate-x-1 transition">
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-[3rem] bg-white border-2 border-dashed border-[#EFE6DE] p-16 text-center">
                <i data-lucide="folder-open" class="mx-auto w-16 h-16 text-gray-300 mb-4"></i>
                <p class="text-gray-500 font-black text-lg">Aucun résultat enregistré.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
@endsection