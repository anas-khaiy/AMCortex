@extends('layouts.app')

@section('title', 'Modifier la Question')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center gap-5">
            <a href="{{ route('questions.create', $question->exam_id) }}"
               class="w-14 h-14 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-600 hover:bg-[#9A0002] hover:text-white hover:-translate-x-1 transition-all">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>

            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Modification question</p>
                <h1 class="text-4xl font-black text-gray-950 mt-1">Modifier la question</h1>
                <p class="text-gray-500 font-medium mt-1">
                    Examen : <span class="text-[#9A0002] font-black">{{ $question->exam->title }}</span>
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('questions.update', $question->id) }}" method="POST" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        @csrf
        @method('PUT')

        {{-- MAIN FORM --}}
        <div class="xl:col-span-2 rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8 space-y-7">

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-950">Contenu de la question</h2>
                    <p class="text-sm text-gray-500">Modifiez l’énoncé et les choix de réponse.</p>
                </div>
            </div>

            {{-- QUESTION --}}
            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700">Texte de la question *</label>
                <textarea name="question_text"
                          rows="4"
                          required
                          class="w-full rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] px-6 py-4 font-semibold outline-none focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition text-lg">{{ old('question_text', $question->question_text) }}</textarea>
            </div>

            {{-- ANSWERS --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-black text-gray-700">Options de réponse</label>
                    <span class="text-xs font-black text-[#9A0002] bg-[#9A0002]/10 px-3 py-1 rounded-full">
                        Sélectionnez la bonne réponse
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-7">
                    @foreach($question->answers as $index => $answer)
                        <div class="group rounded-[1rem] bg-[#FAF7F4] border border-[#EFE6DE] p-4 hover:bg-white hover:shadow-lg transition-all">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
                                Option {{ chr(165 + $index) }}
                            </label>

                            <div class="flex gap-2">
                                <input type="text"
                                       name="answers[{{ $answer->id }}]"
                                       value="{{ old('answers.'.$answer->id, $answer->answer_text) }}"
                                       required
                                       class="flex-1 rounded-2xl border border-[#EFE6DE] bg-white px-5 py-4 font-semibold outline-none focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">

                                <label class="cursor-pointer">
                                    <input type="checkbox"
                                           name="correct_answers[{{ $answer->id }}]"
                                           value="1"
                                           class="correct-input peer hidden"
                                           {{ $answer->is_correct ? 'checked' : '' }}>

                                    <div class="h-full px-5 flex items-center justify-center rounded-2xl border border-[#EFE6DE] bg-white text-gray-400 font-black text-xs peer-checked:bg-green-100 peer-checked:text-green-700 peer-checked:border-green-200 transition-all">
                                        Correct
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SIDE PANEL --}}
        <div class="space-y-6">

            {{-- CONFIG --}}
            <div class="rounded-[2.5rem] bg-[#9A0002] text-white shadow-xl p-7 relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-44 h-44 bg-white/10 rounded-full blur-2xl"></div>

                <h3 class="relative text-2xl font-black">Configuration</h3>
                <p class="relative text-white/70 text-sm mt-1">Type de question et barème AMC.</p>

                <div class="relative mt-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-black text-white/90">Type de question</label>
                        <select name="question_type"
                                id="questionType"
                                class="w-full rounded-2xl bg-white text-gray-900 px-5 py-4 font-black outline-none">
                            <option value="single" {{ $question->question_type == 'single' ? 'selected' : '' }}>
                                Réponse unique
                            </option>
                            <option value="multiple" {{ $question->question_type == 'multiple' ? 'selected' : '' }}>
                                Réponses multiples
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <label class="text-sm font-black text-white/90">Points</label>
                            <input type="number"
                                   step="0.25"
                                   name="points_correct"
                                   value="{{ old('points_correct', $question->points_correct ?? 1) }}"
                                   min="0.25"
                                   class="w-full rounded-2xl bg-white text-green-700 px-5 py-4 font-black outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-black text-white/90">Pénalité</label>
                            <input type="number"
                                   step="0.25"
                                   name="points_penalty"
                                   value="{{ old('points_penalty', $question->points_penalty ?? 0) }}"
                                   min="0"
                                   class="w-full rounded-2xl bg-white text-red-700 px-5 py-4 font-black outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-7 space-y-4">
                <button type="submit"
                        class="w-full px-6 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Enregistrer
                </button>

                <a href="{{ route('questions.create', $question->exam_id) }}"
                   class="w-full flex items-center justify-center px-6 py-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-600 font-black hover:text-[#9A0002] transition">
                    Annuler
                </a>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('questionType');
        const correctInputs = document.querySelectorAll('.correct-input');

        function updateInputs() {
            if (typeSelect.value === 'single') {
                let alreadyChecked = false;
                correctInputs.forEach(input => {
                    if (input.checked && !alreadyChecked) {
                        alreadyChecked = true;
                    } else if (input.checked && alreadyChecked) {
                        input.checked = false;
                    }
                });
            }
        }

        correctInputs.forEach(input => {
            input.addEventListener('change', function () {
                if (typeSelect.value === 'single' && this.checked) {
                    correctInputs.forEach(other => {
                        if (other !== this) other.checked = false;
                    });
                }
            });
        });

        typeSelect.addEventListener('change', updateInputs);
        updateInputs();

        if (window.lucide) lucide.createIcons();
    });
</script>
@endsection