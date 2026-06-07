@extends('layouts.app')
@section('title', 'Historique des résultats')
@section('content')
<div class="space-y-8">

    <div class="relative overflow-hidden rounded-[2.5rem] bg-[#111827] text-white p-8 shadow-xl">
        <div class="absolute -right-24 -top-24 w-72 h-72 bg-[#9A0002]/40 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-white/50">Historique</p>
                <h1 class="text-4xl font-black mt-2">Historique des résultats</h1>
                <p class="text-white/70 mt-2">
                    Examen : <span class="font-black text-white">{{ $exam->title }}</span>
                </p>
            </div>

            <a href="{{ route('results.index') }}"
               class="px-5 py-3 rounded-2xl bg-white text-gray-900 font-black hover:-translate-y-1 transition">
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @forelse($sessions as $session)
        @php
            $modeLabel = $session->mode === 'named' ? 'Nominatif' : 'Anonyme';
        @endphp

        <div class="group relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] p-6 shadow-lg hover:-translate-y-2 hover:shadow-2xl transition-all duration-300">

            <div class="absolute -right-16 -top-16 w-44 h-44 bg-[#9A0002]/10 rounded-full blur-3xl"></div>
            <div class="absolute left-0 top-0 h-full w-2 bg-[#9A0002]"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-[1.5rem] bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center group-hover:bg-[#9A0002] group-hover:text-white group-hover:rotate-6 transition">
                        <i data-lucide="clipboard-check" class="w-7 h-7"></i>
                    </div>

                    <div>
                        <h3 class="text-2xl font-black text-gray-950 group-hover:text-[#9A0002] transition">
                            {{ $session->label }}
                        </h3>
                        <p class="text-sm font-bold text-gray-400 mt-1">
                            {{ $session->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>

                <div class="w-11 h-11 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-400 group-hover:bg-[#9A0002] group-hover:text-white transition">
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </div>
            </div>

            <div class="relative grid grid-cols-2 gap-4 mt-6">
                <div class="rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] p-4">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Mode</p>
                    <p class="text-lg font-black text-[#9A0002] mt-1">{{ $modeLabel }}</p>
                </div>

                <div class="rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] p-4">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Copies</p>
                    <p class="text-lg font-black text-gray-950 mt-1">{{ $session->copies_count }}</p>
                </div>
            </div>
            <div class="relative mt-5 flex gap-3">
                <a href="{{ route('results.session', $session->id) }}"
                   class="flex-1 px-4 py-3 rounded-2xl bg-[#9A0002] text-white font-black text-center">
                    Voir les notes
                </a>

                <a href="{{ route('results.session.corrected', $session->id) }}"
                   class="flex-1 px-4 py-3 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-700 font-black text-center hover:text-[#9A0002]">
                    Copies corrigées
                </a>
            </div>
        </div>
    @empty
        <div class="lg:col-span-2 rounded-[2.5rem] bg-white border-2 border-dashed border-[#EFE6DE] p-14 text-center text-gray-500">
            Aucun historique pour cet examen.
        </div>
    @endforelse
</div>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
@endsection