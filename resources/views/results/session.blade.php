@extends('layouts.app')

@section('title', 'Historique de Correction')

@section('content')
@php
    $modeLabel = $session->mode === 'named' ? 'Nominatif' : 'Anonyme';
    $total = $session->exam->total_points ?: $session->exam->total_calculated_points;
@endphp

<div class="space-y-8">

    <div class="relative overflow-hidden rounded-[2.5rem] bg-[#111827] text-white p-8 shadow-xl">
        <div class="absolute -right-24 -top-24 w-72 h-72 bg-[#9A0002]/40 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-white/50">Session de correction</p>
                <h1 class="text-4xl font-black mt-2">{{ $session->label }}</h1>
                <p class="text-white/70 mt-2">
                    Examen : <span class="font-black text-white">{{ $session->exam->title }}</span>
                    • Mode : {{ $modeLabel }}
                    • Date : {{ $session->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('results.session.download', $session->id) }}"
                   class="px-5 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition">
                    Télécharger CSV
                </a>

                <a href="{{ route('results.exam', $session->exam->id) }}"
                   class="px-5 py-3 rounded-2xl bg-white text-gray-900 font-black hover:-translate-y-1 transition">
                    Retour
                </a>
            </div>
        </div>
    </div>

    @if($session->sessionStudents->count())
        <div class="bg-white rounded-[2rem] border border-[#EFE6DE] shadow-xl p-6">
            <h3 class="text-xl font-black text-gray-950 mb-4">Étudiants de cette session</h3>

            <div class="flex flex-wrap gap-2">
                @foreach($session->sessionStudents as $item)
                    <span class="px-4 py-2 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-700 text-sm font-black">
                        {{ $item->student->first_name }} {{ $item->student->last_name }}
                        @if($item->student->student_code)
                            - {{ $item->student->student_code }}
                        @endif
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-[#FAF7F4] border-b border-[#EFE6DE]">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-widest">
                    <th class="px-6 py-5 font-black">Copie</th>
                    <th class="px-6 py-5 font-black">Code</th>
                    <th class="px-6 py-5 font-black">Nom</th>
                    <th class="px-6 py-5 font-black">Note</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[#EFE6DE]">
                @forelse($session->rows as $row)
                    <tr class="hover:bg-[#FAF7F4] transition">
                        <td class="px-6 py-4 font-bold text-gray-700">{{ $row->copie }}</td>
                        <td class="px-6 py-4 font-mono font-black text-[#9A0002]">{{ $row->code }}</td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $row->nom }}</td>
                        <td class="px-6 py-4">
                            <span class="px-4 py-2 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] font-black">
                                {{ $row->note }} / {{ $total }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                            Aucun résultat dans cette session.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection