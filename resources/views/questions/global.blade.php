@extends('layouts.app')

@section('title', 'Banque de Questions')

@section('content')
@if(session('success'))
<div id="success-alert" class="mb-6 flex items-center gap-4 rounded-[2rem] border border-green-100 bg-green-50 p-5 text-green-800 shadow-lg">
    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-600">
        <i data-lucide="check-circle" class="h-7 w-7"></i>
    </div>
    <div>
        <p class="font-black text-lg">Succès</p>
        <p class="text-sm font-semibold">{{ session('success') }}</p>
    </div>
</div>
@endif

<div class="space-y-8">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Banque pédagogique</p>
                <h1 class="text-4xl font-black text-gray-950 mt-1">Banque de questions</h1>
                <p class="text-gray-500 font-medium mt-2">Gérez, modifiez et réutilisez toutes vos questions.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button onclick="document.getElementById('addQuestionModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-2xl bg-[#9A0002] px-6 py-4 text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all">
                    <i data-lucide="plus-circle" class="h-5 w-5"></i>
                    Nouvelle question
                </button>

                <a href="{{ route('exams.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] px-6 py-4 text-gray-700 font-black hover:text-[#9A0002] transition">
                    <i data-lucide="layers" class="h-5 w-5"></i>
                    Examens
                </a>
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <form method="GET"
        action="{{ route('questions.global') }}"
        class="rounded-[2rem] bg-white p-5 shadow-lg border border-[#EFE6DE]">

        <div class="flex flex-col md:flex-row items-center gap-4">

            {{-- SEARCH --}}
            <div class="relative flex-1 w-full">
               <i data-lucide="search"
                   class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"></i>

                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Rechercher une question, réponse ou examen..."
                    class="w-full rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] pl-12 pr-4 py-4 font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
            </div>

            {{-- EXAM FILTER --}}
            <select name="exam_id"
                    class="w-full md:w-72 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] px-4 py-4 font-semibold outline-none focus:border-[#9A0002]">

                <option value="">Tous les examens</option>

                @foreach($questions->pluck('exam')->unique('id') as $exam)

                    <option value="{{ $exam->id }}"
                        {{ request('exam_id') == $exam->id ? 'selected' : '' }}>

                        {{ $exam->title }}

                    </option>

                @endforeach

            </select>

            {{-- BUTTON --}}
            <button type="submit"
                    class="px-6 py-4 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition whitespace-nowrap">

                Rechercher

            </button>

        </div>
    </form>

    {{-- GRID --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($questions as $question)
            <div class="group relative overflow-hidden rounded-[2.5rem] bg-white p-6 shadow-lg border border-[#EFE6DE] hover:-translate-y-2 hover:shadow-2xl transition-all duration-500">

                <div class="absolute inset-x-0 top-0 h-2 bg-[#9A0002]"></div>
                <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#9A0002]/10 blur-3xl"></div>

                <div class="relative flex items-start justify-between mb-5">
                    <div class="h-16 w-16 rounded-[1.5rem] bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center group-hover:bg-[#9A0002] group-hover:text-white group-hover:rotate-6 transition-all">
                        <i data-lucide="help-circle" class="h-7 w-7"></i>
                    </div>

                    <span class="rounded-full px-4 py-2 text-xs font-black bg-[#9A0002]/10 text-[#9A0002]">
                        {{ $question->points_correct }} PTS
                    </span>
                </div>

                <p class="relative text-[11px] font-black text-[#9A0002] uppercase tracking-widest mb-2">
                    {{ $question->exam->title }}
                </p>

                <h3 class="relative text-xl font-black text-gray-950 mb-5 line-clamp-2 min-h-[3.5rem] group-hover:text-[#9A0002] transition">
                    {{ $question->question_text }}
                </h3>

                <div class="relative space-y-2 mb-6">
                    @foreach($question->answers->take(4) as $answer)
                        <div class="flex items-center gap-3 p-3 rounded-2xl border transition
                            {{ $answer->is_correct
                                ? 'bg-green-50 border-green-100 text-green-700'
                                : 'bg-[#FAF7F4] border-[#EFE6DE] text-gray-600' }}">
                            @if($answer->is_correct)
                                <i data-lucide="check-circle-2" class="h-4 w-4 text-green-600"></i>
                                <span class="font-black text-sm line-clamp-1">{{ $answer->answer_text }}</span>
                            @else
                                <div class="h-2 w-2 rounded-full bg-gray-300"></div>
                                <span class="font-semibold text-sm line-clamp-1">{{ $answer->answer_text }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="relative flex gap-2 pt-5 border-t border-[#EFE6DE]">
                    <a href="{{ route('questions.edit', $question->id) }}"
                       class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] py-3 text-gray-700 hover:bg-[#9A0002] hover:text-white transition-all font-black text-sm">
                        <i data-lucide="edit-3" class="h-4 w-4"></i>
                        Modifier
                    </a>

                    <form action="{{ route('questions.destroy', $question->id) }}" method="POST" onsubmit="return confirm('Supprimer cette question ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-600 hover:bg-red-500 hover:text-white transition-all">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-[#EFE6DE] shadow-sm">
                <i data-lucide="database" class="h-16 w-16 text-gray-300 mx-auto mb-4"></i>
                <p class="text-gray-500 text-lg font-black">Aucune question dans la banque.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-12 flex justify-center">
        <div class="inline-flex items-center gap-2 rounded-[2rem] bg-white p-3 shadow-lg border border-[#EFE6DE]">
            {{ $questions->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="addQuestionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="relative overflow-hidden bg-white rounded-[2.5rem] w-full max-w-md p-10 shadow-2xl border border-[#EFE6DE]">
        <div class="absolute -right-16 -top-16 w-44 h-44 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <button onclick="document.getElementById('addQuestionModal').classList.add('hidden')"
                class="absolute top-6 right-6 text-gray-400 hover:text-[#9A0002]">
            <i data-lucide="x" class="h-6 w-6"></i>
        </button>

        <div class="relative text-center mb-8">
            <div class="h-16 w-16 bg-[#9A0002]/10 text-[#9A0002] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="help-circle" class="h-8 w-8"></i>
            </div>
            <h2 class="text-3xl font-black text-gray-950">Nouvelle question</h2>
            <p class="text-gray-500 font-medium">Choisissez l’examen associé.</p>
        </div>

        <form onsubmit="redirectToCreate(event)" class="relative space-y-6">
            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Sélectionner l’examen</label>
                <select id="exam_selector" required
                        class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-semibold outline-none focus:bg-white focus:border-[#9A0002]">
                    <option value="" disabled selected>Choisir un examen...</option>
                    @foreach(
                        \App\Models\Exam::where('teacher_id', auth()->id())
                            ->where('is_locked', false)
                            ->latest()
                            ->get()
                        as $exam
                    )

                        <option value="{{ $exam->id }}">{{ $exam->title }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="w-full py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all">
                Continuer vers l’ajout
            </button>
        </form>
    </div>
</div>

<script>
    function redirectToCreate(e) {
        e.preventDefault();
        const examId = document.getElementById('exam_selector').value;
        if (examId) window.location.href = `/exams/${examId}/questions/create`;
    }

    if (window.lucide) lucide.createIcons();
</script>
@endsection