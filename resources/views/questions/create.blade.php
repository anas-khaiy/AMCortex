@extends('layouts.app')

@section('title', 'Ajouter Questions')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">

    {{-- Header avec retour --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('exams.index') }}"
                   class="group w-14 h-14 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-500 hover:bg-[#9A0002] hover:text-white transition-all hover:-translate-x-1">
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </a>

                <div>
                    <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Étape 2</p>
                    <h1 class="text-4xl font-black text-gray-950">Banque de Questions</h1>
                    <p class="text-gray-500 font-medium mt-1">
                        Examen : <span class="text-[#9A0002] font-black">{{ $exam->title }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">

                <a href="{{ route('ai.local.questions', $exam->id) }}"
                   class="px-6 py-4 rounded-[2rem] bg-[#111827] text-white font-black shadow-xl hover:bg-black hover:-translate-y-1 transition-all flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                    Suggestions IA
                </a>

                <div class="bg-[#9A0002] text-white px-7 py-4 rounded-[2rem] shadow-xl text-center">
                    <span class="block text-[10px] uppercase opacity-70 tracking-widest font-black">
                        Total Points
                    </span>

                    <span class="text-3xl font-black">
                        {{ $exam->total_points }} pts
                    </span>
                </div>

            </div>

        </div>
    </div>

    {{-- Barre de Progression (Étape 2 active) --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-lg border border-[#EFE6DE]">
        <div class="flex items-center justify-between max-w-2xl mx-auto relative">
            @php
                $steps = [
                    ['number' => 1, 'title' => 'Infos Exam', 'status' => 'completed'],
                    ['number' => 2, 'title' => 'Questions', 'status' => 'active'],
                    ['number' => 3, 'title' => 'Génération', 'status' => 'pending'],
                ];
            @endphp

            @foreach($steps as $index => $step)
                <div class="flex flex-col items-center relative z-10">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl font-black transition-all 
                        {{ $step['status'] === 'active' ? 'bg-[#9A0002] text-white shadow-xl scale-110' : ($step['status'] === 'completed' ? 'bg-green-500 text-white' : 'bg-[#FAF7F4] text-gray-400 border border-[#EFE6DE]') }}">
                        @if($step['status'] === 'completed')
                            <i data-lucide="check" class="h-6 w-6"></i>
                        @else
                            {{ $step['number'] }}
                        @endif
                    </div>

                    <span class="mt-3 text-xs font-black uppercase tracking-wider {{ $step['status'] === 'active' ? 'text-[#9A0002]' : 'text-gray-400' }}">
                        {{ $step['title'] }}
                    </span>
                </div>
            @endforeach

            <div class="absolute top-7 left-0 w-full h-1 bg-[#EFE6DE] rounded-full -z-0">
                <div class="h-full bg-[#9A0002] rounded-full transition-all" style="width:66%"></div>
            </div>
        </div>
    </div>

    {{-- Formulaire d'Ajout --}}
    <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-[#EFE6DE]">
        <h2 class="text-2xl font-black text-gray-950 mb-8 flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                <i data-lucide="help-circle" class="w-5 h-5"></i>
            </span>
            Nouvelle Question
        </h2>

        <div class="bg-[#FAF7F4] p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-[#EFE6DE] mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-black text-gray-950 flex items-center gap-2">
                    <i data-lucide="upload" class="w-5 h-5 text-[#9A0002]"></i>
                    Importer un fichier
                </h2>

                <button type="button"
                    onclick="toggleImportHelp()"
                    class="w-10 h-10 flex items-center justify-center rounded-2xl bg-white border border-[#EFE6DE] hover:bg-[#9A0002] hover:text-white text-gray-500 font-black transition-all">
                    ?
                </button>
            </div>

            <div id="importHelpBox" class="hidden mb-4 p-5 rounded-2xl bg-white border border-[#EFE6DE] text-sm text-gray-600">
                <p class="font-bold text-gray-950 mb-2">Format CSV attendu :</p>
                <p class="mb-2 text-xs text-gray-500">
                    Utilise un fichier CSV avec séparateur <strong>virgule ou point-virgule</strong>.
                </p>
                <code class="block text-xs break-all bg-[#FAF7F4] p-3 rounded-xl border border-[#EFE6DE]">
                    question_text,question_type,points_correct,points_penalty,answer_1,correct_1,answer_2,correct_2,answer_3,correct_3,answer_4,correct_4
                    ou 
                    question_text;question_type;points_correct;points_penalty;answer_1;correct_1;answer_2;correct_2;answer_3;correct_3;answer_4;correct_4
                </code>
                <p class="mt-3 text-xs text-gray-500">
                    Exemple :
                </p>
                <code class="block text-xs break-all bg-[#FAF7F4] p-3 rounded-xl border border-[#EFE6DE] mt-2">
                    Quelle est la capitale du Maroc ?,single,1,0,Rabat,1,Casa,0,Fès,0,Tanger,0,<br>
                    Python est compilé ?,boolean,1,0,Vrai,0,Faux,1,,,,
                    ou
                    Quelle est la capitale du Maroc ?;single;1;0;Rabat;1;Casa;0;Fès;0;Tanger;0<br>
                </code>
            </div>

            <form method="POST"
                  action="{{ route('questions.import.csv', $exam->id) }}"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf

                <input type="file"
                       name="questions_file"
                       accept=".csv,.txt"
                       required
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-white focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none transition">

                <button type="submit"
                        class="mt-2 px-6 py-3 bg-[#9A0002] text-white rounded-2xl font-black hover:bg-[#7A0001] hover:-translate-y-1 transition-all shadow-lg">
                    Importer le fichier
                </button>
            </form>
        </div>

        <form id="questionForm" action="{{ route('questions.store', $exam->id) }}" method="POST" class="space-y-8">
            @csrf

            {{-- Texte de la Question --}}
            <div class="space-y-2">
                <label class="text-sm font-black text-gray-700 ml-1 uppercase tracking-wide">Énoncé de la question *</label>
                <textarea name="question_text" rows="3" required
                          placeholder="Ex: Quelle est la capitale de la France ?" 
                          class="w-full px-6 py-5 rounded-[1.5rem] border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none transition-all text-lg font-medium"></textarea>
            </div>

            {{-- Config Type & Barème --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-[#9A0002]/5 rounded-[1.5rem] border border-[#9A0002]/10">
                <div class="space-y-2">
                    <label class="text-xs font-black text-[#9A0002] uppercase ml-1">Type</label>
                    <select name="question_type" id="questionType" class="w-full px-4 py-3 rounded-xl border border-[#EFE6DE] bg-white shadow-sm focus:border-[#9A0002] outline-none font-bold text-gray-700">
                        <option value="single">Unique</option>
                        <option value="multiple">Multiple</option>
                        <option value="boolean">Vrai / Faux</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-green-600 uppercase ml-1">Points si Juste</label>
                    <input type="number" step="0.25" name="points_correct" value="1" 
                           class="w-full px-4 py-3 rounded-xl border border-[#EFE6DE] bg-white shadow-sm focus:border-green-500 outline-none font-black text-center" min="0.25">
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-red-600 uppercase ml-1">Pénalité (Faux)</label>
                    <input type="number" step="0.25" name="points_penalty" value="0" placeholder="Ex: -0.5"
                           class="w-full px-4 py-3 rounded-xl border border-[#EFE6DE] bg-white shadow-sm focus:border-red-500 outline-none font-black text-center text-red-600" min="0">
                </div>

                <div class="flex items-center text-[10px] text-gray-500 font-medium italic pt-4 md:pt-6">
                    <i data-lucide="info" class="h-3 w-3 mr-1 text-[#9A0002]"></i>
                    La pénalité s'applique par mauvaise case cochée.
                </div>
            </div>

            {{-- Options de Réponses --}}
            <div class="space-y-6">
                <label class="block text-sm font-black text-gray-700 uppercase tracking-wide ml-1">Réponses possibles</label>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    @for($i=0; $i<8; $i++)
                    <div class="group relative flex items-center gap-3 p-3 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] hover:bg-white hover:border-[#9A0002]/30 hover:shadow-lg transition-all">
                        <div class="h-10 w-10 flex items-center justify-center rounded-xl bg-white text-gray-500 font-black group-hover:bg-[#9A0002] group-hover:text-white transition-colors shadow-sm">
                            {{ chr(65 + $i) }}
                        </div>
                        <input type="text" name="answers[{{$i}}]" placeholder="Option {{ chr(65 + $i) }}..." {{ $i < 2 ? 'required' : '' }}
                               class="flex-1 bg-transparent border-none focus:ring-0 outline-none font-medium">

                        <label class="relative flex items-center cursor-pointer">
                            <input type="checkbox" name="correct_answers[{{$i}}]" value="1" class="correct-input peer hidden">
                            <div class="px-4 py-2 rounded-xl bg-white text-gray-400 font-black text-[10px] uppercase tracking-tighter border border-[#EFE6DE] peer-checked:bg-green-500 peer-checked:text-white peer-checked:shadow-md peer-checked:border-green-500 transition-all">
                                Correct
                            </div>
                        </label>
                    </div>
                    @endfor
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex flex-col md:flex-row items-center gap-4 pt-8 border-t border-[#EFE6DE]">
                <button type="submit" name="action" value="add" 
                        class="w-full md:flex-1 px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                    Enregistrer et Suivante
                </button>
                <button type="submit" name="action" value="finish" 
                        class="w-full md:w-auto px-8 py-4 rounded-2xl bg-gray-950 text-white font-black hover:bg-black hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="check-check" class="h-5 w-5"></i>
                    Terminer la saisie
                </button>
            </div>
        </form>
    </div>

    {{-- Liste des Questions --}}
    @if($exam->questions->count() > 0)
    <div class="space-y-4">
        <h2 class="text-xl font-black text-gray-950 px-2">Questions déjà ajoutées ({{ $exam->questions->count() }})</h2>
        <div class="grid gap-4">
            @foreach($exam->questions as $question)
            <div class="bg-white p-5 rounded-3xl border border-[#EFE6DE] shadow-sm flex items-center justify-between group hover:border-[#9A0002]/20 hover:shadow-xl transition-all">
                <div class="flex items-center gap-4">
                    <div class="h-11 w-11 rounded-xl bg-[#9A0002]/10 flex items-center justify-center text-[#9A0002] font-black">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <p class="font-black text-gray-950 leading-tight">{{ $question->question_text }}</p>

                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                            {{ $question->points_correct }} Points •
                            {{
                                $question->question_type == 'single'
                                    ? 'Choix unique'
                                    : ($question->question_type == 'multiple' ? 'Choix multiple' : 'Vrai / Faux')
                            }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                    {{-- Bouton Modifier --}}
                    <a href="{{ route('questions.edit', $question->id) }}" class="p-2 rounded-xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-500 hover:bg-[#9A0002] hover:text-white transition-colors">
                        <i data-lucide="edit-3" class="h-5 w-5"></i>
                    </a>
                    {{-- Bouton Supprimer --}}
                    <form action="{{ route('questions.destroy', $question->id) }}" method="POST" onsubmit="return confirm('Voulez-vous supprimer cette question ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl bg-red-50 text-red-500 hover:text-white hover:bg-red-500 transition-colors">
                            <i data-lucide="trash-2" class="h-5 w-5"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('questionType');
    const correctInputs = document.querySelectorAll('.correct-input');
    const form = document.getElementById('questionForm');
    const answerInputs = document.querySelectorAll('input[name^="answers"]');
    const isArabicExam = @json($exam->exam_language === 'ar');

    function updateQuestionTypeUI() {
        const type = typeSelect.value;

        if (type === 'boolean') {
            answerInputs[0].closest('.group').style.display = 'flex';
            answerInputs[1].closest('.group').style.display = 'flex';

            const isArabicExam = @json($exam->exam_language === 'ar');

            answerInputs[0].value = isArabicExam ? 'صحيح' : 'Vrai';
            answerInputs[1].value = isArabicExam ? 'خطأ' : 'Faux';


            answerInputs[0].readOnly = true;
            answerInputs[1].readOnly = true;

            answerInputs[0].required = true;
            answerInputs[1].required = true;

            for (let i = 2; i < answerInputs.length; i++) {
                answerInputs[i].value = '';
                answerInputs[i].required = false;
                answerInputs[i].readOnly = false;
                answerInputs[i].closest('.group').style.display = 'none';
                correctInputs[i].checked = false;
            }
        } else {
            for (let i = 0; i < answerInputs.length; i++) {
                answerInputs[i].closest('.group').style.display = 'flex';
                answerInputs[i].required = i < 2;
            }

            if (typeSelect.dataset.previousType === 'boolean') {
                answerInputs[0].value = '';
                answerInputs[1].value = '';
                answerInputs[0].readOnly = false;
                answerInputs[1].readOnly = false;
            }
        }

        correctInputs.forEach(input => input.checked = false);
        typeSelect.dataset.previousType = type;
    }

    correctInputs.forEach(input => {
        input.addEventListener('change', function() {
            if ((typeSelect.value === 'single' || typeSelect.value === 'boolean') && this.checked) {
                correctInputs.forEach(other => {
                    if (other !== this) other.checked = false;
                });
            }
        });
    });

    form.addEventListener('submit', function(e) {
        const action = e.submitter ? e.submitter.value : null;

        if (action === 'finish') {
            return;
        }

        const checkedCount = Array.from(correctInputs).filter(input => input.checked).length;
        const type = typeSelect.value;

        if (type === 'single' && checkedCount !== 1) {
            e.preventDefault();
            alert("Erreur : Pour un choix unique, vous devez sélectionner exactement UNE réponse correcte.");
            return;
        }

        if (type === 'multiple' && checkedCount < 1) {
            e.preventDefault();
            alert("Erreur : Pour un choix multiple, vous devez sélectionner au moins UNE réponse correcte.");
            return;
        }

        if (type === 'boolean' && checkedCount !== 1) {
            e.preventDefault();
            alert("Pour Vrai/Faux, vous devez sélectionner UNE seule réponse correcte.");
            return;
        }
    });

    typeSelect.addEventListener('change', updateQuestionTypeUI);
    updateQuestionTypeUI();

    if (window.lucide) lucide.createIcons();
});

function toggleImportHelp() {
    const box = document.getElementById('importHelpBox');

    if (box) {
        box.classList.toggle('hidden');
    }
}
</script>
@endsection