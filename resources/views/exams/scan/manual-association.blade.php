@extends('layouts.app')

@section('title', 'Association manuelle')

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
                <h1 class="text-4xl font-black mt-2">Association manuelle</h1>
                <p class="mt-2 text-white/70">
                    Examen :
                    <span class="font-black text-white">{{ $exam->title }}</span>
                </p>
            </div>

            <a href="{{ route('exams.scan.index.exam', $exam->id) }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-white text-gray-900 rounded-2xl font-black hover:-translate-y-1 transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Retour au workflow
            </a>
        </div>
    </div>

    {{-- Info --}}
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 text-amber-800">
        <div class="flex gap-3">
            <i data-lucide="info" class="w-5 h-5 mt-0.5"></i>
            <div>
                <p class="font-black">Association manuelle requise</p>
                <p class="text-sm mt-1">
                    Pour chaque copie affichée à gauche, choisissez l’étudiant correspondant à droite.
                    Si une copie ne correspond à aucun étudiant, laissez-la sur “Non associé”.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('exams.scan.manual.save', $exam->id) }}">
        @csrf

        <div class="space-y-6">
            @forelse($images as $image)
                @php
                    $current = $associations[$image['file_name']]->student_id ?? null;
                @endphp

                <div class="rounded-[2rem] bg-white border border-[#EFE6DE] shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-[#EFE6DE] flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#111827] text-white flex items-center justify-center">
                                <i data-lucide="file-image" class="w-6 h-6"></i>
                            </div>

                            <div>
                                <h3 class="text-lg font-black text-gray-950">
                                    {{ $image['file_name'] }}
                                </h3>

                                @if(($image['pages_count'] ?? 1) > 1)
                                    <span class="inline-flex mt-1 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-black">
                                        {{ $image['pages_count'] }} pages
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($current)
                            <span class="text-green-600 font-black text-sm flex items-center gap-1">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                Association enregistrée
                            </span>
                        @endif
                    </div>

                    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="rounded-2xl overflow-hidden border border-[#EFE6DE] bg-[#FAF7F4]">
                            <img src="{{ $image['file_url'] }}"
                                 alt="{{ $image['file_name'] }}"
                                 class="w-full">
                        </div>

                        <div class="flex flex-col ">
                            <label class="text-sm font-black text-gray-700 mb-3">
                                Choisir l'étudiant correspondant
                            </label>

                            <select name="associations[{{ $image['file_name'] }}]"
                                    class="w-full rounded-2xl border border-[#EFE6DE] px-4 py-3 font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#9A0002]">
                                <option value="">-- Non associé --</option>

                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                        {{ (string)$current === (string)$student->id ? 'selected' : '' }}>
                                        {{ $student->first_name }} {{ $student->last_name }}
                                        @if(!empty($student->student_code))
                                            - {{ $student->student_code }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            <p class="mt-3 text-sm text-gray-500">
                                Sélectionnez l’étudiant qui correspond à cette copie scannée.
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-dashed border-[#EFE6DE] rounded-[2rem] p-10 text-center text-gray-500">
                    Aucune image de scan trouvée.
                </div>
            @endforelse
        </div>

        @if(count($images))
            <div class="mt-8 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition shadow-lg">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Enregistrer les associations
                </button>
            </div>
        @endif
    </form>
</div>

<script>
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
@endsection