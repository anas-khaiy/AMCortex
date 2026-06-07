@extends('layouts.app')

@section('title', 'Générer les copies')

@section('content')
<div class="w-full space-y-8">
@if(session('success'))
    <div class="rounded-[2rem] border border-green-100 bg-green-50 p-5 shadow-lg flex items-start gap-4">

        <div class="w-14 h-14 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center shrink-0">
            <i data-lucide="badge-check" class="w-7 h-7"></i>
        </div>

        <div class="flex-1">
            <p class="text-xs font-black uppercase tracking-widest text-green-700">
                Génération AMC
            </p>

            <h3 class="text-xl font-black text-gray-950 mt-1">
                PDF généré avec succès
            </h3>

            <p class="text-gray-700 font-medium mt-2 leading-relaxed">
                {{ session('success') }}
            </p>
        </div>

    </div>
@endif
    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-24 -top-24 w-80 h-80 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-6">
            <div class="flex items-center gap-5">
                <a href="{{ route('questions.create', $exam->id) }}"
                   class="w-14 h-14 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] flex items-center justify-center text-gray-500 hover:bg-[#9A0002] hover:text-white hover:-translate-x-1 transition-all">
                    <i data-lucide="arrow-left" class="h-5 w-5"></i>
                </a>

                <div>
                    <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Étape finale</p>
                    <h1 class="text-4xl font-black text-gray-950 mt-1">Génération des feuilles</h1>
                    <p class="text-gray-500 font-medium mt-2">Préparez vos documents pour l'impression AMC.</p>
                </div>
            </div>

            <div class="hidden md:flex w-20 h-20 rounded-[2rem] bg-[#9A0002] text-white items-center justify-center shadow-xl">
                <i data-lucide="printer" class="w-9 h-9"></i>
            </div>
        </div>
    </div>

    {{-- PROGRESS --}}
    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-lg p-8">
        @php
            $steps = [
                ['number' => 1, 'title' => 'Infos Exam', 'status' => 'completed'],
                ['number' => 2, 'title' => 'Questions', 'status' => 'completed'],
                ['number' => 3, 'title' => 'Génération', 'status' => 'active'],
            ];
        @endphp

        <div class="relative max-w-3xl mx-auto">
            <div class="absolute top-7 left-10 right-10 h-1 bg-[#EFE6DE] rounded-full"></div>
            <div class="absolute top-7 left-10 right-10 h-1 bg-[#9A0002] rounded-full"></div>

            <div class="relative grid grid-cols-3 gap-4">
                @foreach($steps as $step)
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black shadow-lg
                            {{ $step['status'] === 'active'
                                ? 'bg-[#9A0002] text-white scale-110'
                                : ($step['status'] === 'completed'
                                    ? 'bg-green-500 text-white'
                                    : 'bg-[#FAF7F4] text-gray-400 border border-[#EFE6DE]') }}">
                            @if($step['status'] === 'completed')
                                <i data-lucide="check" class="h-6 w-6"></i>
                            @else
                                {{ $step['number'] }}
                            @endif
                        </div>

                        <span class="mt-4 text-xs font-black uppercase tracking-wider
                            {{ $step['status'] === 'active' ? 'text-[#9A0002]' : 'text-gray-400' }}">
                            {{ $step['title'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

        {{-- LEFT --}}
        <div class="xl:col-span-4 space-y-6">

            {{-- SUMMARY --}}
            <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border border-[#EFE6DE]">
                <h2 class="text-xl font-black text-gray-950 mb-5">Résumé de l'examen</h2>

                <div class="grid grid-cols-1 gap-4">
                    <div class="flex items-center gap-4 p-4 bg-[#FAF7F4] rounded-2xl border border-[#EFE6DE]">
                        <div class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                            <i data-lucide="file-text" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-gray-400">Questions</p>
                            <p class="text-sm font-black text-gray-950">{{ $exam->questions->count() }} enregistrées</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 bg-[#FAF7F4] rounded-2xl border border-[#EFE6DE]">
                        <div class="w-12 h-12 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                            <i data-lucide="clock" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-gray-400">Durée</p>
                            <p class="text-sm font-black text-gray-950">{{ $exam->duration }} minutes</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- OPTIONS --}}
            <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border border-[#EFE6DE]">
                <h2 class="text-xl font-black text-gray-950 mb-5">Options AMC</h2>

                <form action="{{ route('exams.process-generation', $exam->id) }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 ml-1 uppercase">Disposition des réponses</label>
                        <select name="layout_mode" id="layout_mode" onchange="handleLayoutChange()"
                                class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none font-bold">
                            <option value="ensemble" selected>Questions et Réponses liées</option>
                            <option value="separate">Feuille de réponses séparée (Fin)</option>
                        </select>
                        <p class="text-[10px] text-gray-400 px-1">
                            <i data-lucide="info" class="inline h-3 w-3"></i>
                            "Séparée" est idéal pour économiser du papier sur les gros examens.
                        </p>
                    </div>

                    <div id="format_selection_container" class="space-y-2">
                        <label class="text-xs font-black text-gray-500 ml-1 uppercase">Forcer un format de sortie</label>
                        <select name="format_override" id="format_override"
                                class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none font-bold">
                            <option value="A4" {{ $recommendedFormat == 'A4' ? 'selected' : '' }}>A4 (Portrait)</option>
                            <option value="A3" {{ $recommendedFormat == 'A3' ? 'selected' : '' }}>A3 (Style 2 colonnes)</option>
                        </select>

                        @if($recommendedFormat == 'A3' && $exam->page_format == 'A4')
                            <p class="text-[10px] text-amber-600 font-bold mt-1">
                                ⚠️ Attention : Beaucoup de questions, le A3 est fortement recommandé.
                            </p>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-500 ml-1 uppercase">Type de feuilles</label>
                        <select name="print_mode" id="print_mode" onchange="toggleStudentList()"
                                class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none font-bold">
                            <option value="anonymous">Copies Vierges (Anonyme) + liste des présents</option>
                            <option value="named">Copies Nominatives (Sélectionner Étudiants)</option>
                        </select>
                    </div>

                    <div id="student-selection" class="mt-4 p-5 bg-[#FAF7F4] rounded-[2rem] border border-[#EFE6DE]">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-black text-gray-950">Étudiants</h3>
                            <button type="button" onclick="toggleAll(this)"
                                    class="text-[10px] font-black text-[#9A0002] uppercase">
                                Tout décocher
                            </button>
                        </div>

                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                            @foreach($students as $student)
                                <label class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-[#EFE6DE] hover:border-[#9A0002] cursor-pointer transition">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->student_code }}" checked
                                           class="rounded text-[#9A0002] focus:ring-[#9A0002]">
                                    <span class="text-sm font-bold text-gray-700">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </span>
                                    <span class="text-gray-400 text-xs">#{{ $student->student_code }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="copies_container" class="space-y-2">
                        <label class="text-xs font-black text-gray-500 ml-1 uppercase">Nombre de copies</label>
                        <input type="number" name="copies" id="copies_input" value="{{ $exam->copies_number }}" min="1"
                               class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none font-bold">
                    </div>

                    <div id="extra_named_copies_container" class="space-y-2 hidden">
                        <label class="text-xs font-black text-gray-500 ml-1 uppercase">Copies supplémentaires nominatives</label>
                        <input type="number" name="extra_named_copies" id="extra_named_copies" value="0" min="0"
                               class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 outline-none font-bold">
                        <p class="text-[10px] text-gray-400 px-1">
                            Exemple : si 2 étudiants sont sélectionnés et vous mettez 3 ici, AMC générera 5 copies.
                        </p>
                    </div>

                    <div class="pt-4 space-y-3">
                        <button type="submit" name="format" value="pdf"
                                @if($students->isEmpty()) disabled @endif
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-[#9A0002] px-4 py-4 text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition-all">
                            <i data-lucide="download" class="h-5 w-5"></i>
                            Générer les copies PDF
                        </button>

                        @if($students->isEmpty())
                            <p class="text-sm text-red-500 mt-2">
                                ⚠️ Ajoutez des étudiants pour générer l'examen 
                            </p>
                        @endif

                        <button type="submit" name="format" value="tex"
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-gray-950 px-4 py-4 text-white font-black hover:bg-black hover:-translate-y-1 transition-all">
                            <i data-lucide="code" class="h-5 w-5"></i>
                            Télécharger Code Source (.txt)
                        </button>
                        
                        <button type="submit" name="format" value="presence_pdf"
                            class="w-full flex items-center justify-center gap-2 rounded-2xl bg-white border border-[#EFE6DE] px-4 py-4 text-[#9A0002] font-black hover:bg-[#FAF7F4] transition-all">
                            <i data-lucide="file-down" class="h-5 w-5"></i>
                            Télécharger liste présence PDF
                        </button>
                        

                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT PREVIEW --}}
        <div class="xl:col-span-8">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-[#EFE6DE] h-full">
                <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
                    <h2 class="text-2xl font-black text-gray-950 flex items-center gap-2">
                        <i data-lucide="eye" class="text-[#9A0002]"></i>
                        Aperçu de la copie
                    </h2>

                    <span class="px-4 py-2 {{ $recommendedFormat == 'A3' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }} rounded-full text-[10px] font-black uppercase tracking-widest">
                        Format {{ $exam->page_format }} (Conseillé : {{ $recommendedFormat }})
                    </span>
                </div>

                @php
                    $isArabicPreview = strtolower($exam->exam_language ?? 'fr') === 'ar';

                    $previewQuestions = $exam->questions->map(function ($q) {
                        return [
                            'text' => $q->question_text,
                            'type' => $q->question_type,
                            'answers' => $q->answers->pluck('answer_text')->values()->all(),
                        ];
                    })->values()->all();

                    $previewTexts = match(strtolower($exam->exam_language ?? 'fr')) {
                        'en' => [
                            'question' => 'Question',
                            'separate_answers' => 'Answers on separate answer sheet.',
                            'more_questions' => 'other question(s)',
                            'named' => 'Named',
                            'anonymous' => 'Anonymous',
                            'student_name' => 'Student name',
                            'blank_name' => '__________________',
                            'instructions_default' => 'Exam instructions.',
                            'separate_instruction' => 'Answers will be filled on a separate answer sheet.',
                            'code_l1' => 'Please code your student number',
                            'code_l2' => 'in the boxes below.',
                        ],
                        'ar' => [
                            'question' => 'السؤال',
                            'separate_answers' => 'الإجابات في ورقة منفصلة.',
                            'more_questions' => 'سؤال/أسئلة أخرى',
                            'named' => 'نسخ بأسماء',
                            'anonymous' => 'نسخ مجهولة',
                            'student_name' => 'اسم الطالب',
                            'blank_name' => '__________________',
                            'instructions_default' => 'تعليمات الامتحان.',
                            'separate_instruction' => 'سيتم وضع الإجابات في ورقة إجابة منفصلة.',
                            'code_l1' => 'يرجى ترميز رقم الطالب',
                            'code_l2' => 'في الخانات أسفله.',
                        ],
                        default => [
                            'question' => 'Question',
                            'separate_answers' => 'Réponses sur feuille séparée.',
                            'more_questions' => 'autre(s) question(s)',
                            'named' => 'Nominatif',
                            'anonymous' => 'Anonyme',
                            'student_name' => 'Nom et prénom',
                            'blank_name' => '__________________',
                            'instructions_default' => 'Consignes de l’examen.',
                            'separate_instruction' => 'Les réponses seront reportées sur une feuille séparée.',
                            'code_l1' => 'Veuillez coder votre numéro étudiant',
                            'code_l2' => 'dans les cases ci-dessous.',
                        ],
                    };
                @endphp

                <div class="rounded-[2.5rem] bg-[#FAF7F4] p-6 border border-[#EFE6DE] shadow-inner">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-black text-gray-950">Aperçu de la feuille</h3>
                            <p class="text-xs text-gray-500">Aperçu dynamique selon vos paramètres</p>
                        </div>

                        <span id="preview-badge"
                              class="px-4 py-2 bg-[#9A0002]/10 text-[#9A0002] rounded-full text-[10px] font-black uppercase tracking-widest">
                            A4 • Anonyme
                        </span>
                    </div>

                    <div class="overflow-auto rounded-[2rem] border border-[#EFE6DE] bg-[#EFE6DE] p-6">
                        <div id="sheet-preview"
                             dir="{{ $isArabicPreview ? 'rtl' : 'ltr' }}"
                             class="mx-auto bg-white shadow-[0_20px_60px_rgba(0,0,0,0.08)] relative origin-top transition-all duration-300 {{ $isArabicPreview ? 'text-right' : '' }}">

                            <div class="absolute top-5 left-5 w-3.5 h-3.5 rounded-full bg-black"></div>
                            <div class="absolute top-5 right-5 w-3.5 h-3.5 rounded-full bg-black"></div>
                            <div class="absolute bottom-5 left-5 w-3.5 h-3.5 rounded-full bg-black"></div>
                            <div class="absolute bottom-5 right-5 w-3.5 h-3.5 rounded-full bg-black"></div>

                            <div class="px-10 pt-8 pb-10">
                                <div class="flex items-start justify-between mb-6">
                                    <div id="preview-top-code" class="grid grid-cols-12 gap-[2px] w-32">
                                        @for($i=0; $i<12; $i++)
                                            <div class="h-3 border border-gray-400"></div>
                                        @endfor
                                    </div>

                                    <div class="text-[10px] text-gray-600 font-semibold mt-1">
                                        +1/1/60+
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-8 items-start mb-6 {{ $isArabicPreview ? 'text-right' : '' }}">
                                    <div>
                                        <h2 id="preview-course-title"
                                            dir="{{ $isArabicPreview ? 'rtl' : 'ltr' }}"
                                            class="text-[18px] font-bold text-gray-900 leading-tight">
                                            {{ $exam->course_name }} - {{ $exam->title }}
                                        </h2>

                                        <p id="preview-instructions" class="mt-5 text-[11px] text-gray-700 leading-relaxed">
                                            {{ $exam->instructions ?: 'Consignes de l’examen.' }}
                                        </p>
                                    </div>

                                    <div id="preview-name-box" class="border border-gray-500 p-3">
                                        <p class="text-[11px] text-gray-700 mb-2" dir="{{ $isArabicPreview ? 'rtl' : 'ltr' }}">
                                            {{ $previewTexts['student_name'] }} :
                                        </p>
                                        <p id="preview-student-name" class="text-[16px] font-bold text-gray-900">
                                            __________________
                                        </p>
                                    </div>
                                </div>

                                <div id="preview-code-zone" class="mb-6">
                                    <div class="flex items-start gap-4">
                                        <div id="preview-code-grid-vertical" class="space-y-1"></div>
                                        <div class="text-[10px] text-gray-500 leading-relaxed pt-1 {{ $isArabicPreview ? 'text-right' : '' }}">
                                            <p>{{ $previewTexts['code_l1'] }}</p>
                                            <p>{{ $previewTexts['code_l2'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-400 my-5"></div>

                                <div id="preview-questions-wrapper">
                                    <div id="preview-questions" class="space-y-4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection


<script>
const previewQuestions = @json($previewQuestions);
const previewTexts = @json($previewTexts);
const isArabicPreview = @json($isArabicPreview);
const previewInstructions = @json($exam->instructions ?: $previewTexts['instructions_default']);
function toggleAll(btn) {
    const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
    const allChecked = Array.from(checkboxes).every(c => c.checked);
    checkboxes.forEach(c => c.checked = !allChecked);
    btn.innerText = allChecked ? 'Tout cocher' : 'Tout décocher';
}

function buildPreviewCodeZone() {
    const container = document.getElementById('preview-code-grid-vertical');
    const length = {{ (int) $exam->student_id_length }};
    container.innerHTML = '';

    for (let row = 0; row < 4; row++) {
        const line = document.createElement('div');
        line.className = 'flex items-center gap-1';

        for (let i = 0; i < length; i++) {
            const box = document.createElement('div');
            box.className = 'w-4 h-4 border border-gray-500';
            line.appendChild(box);
        }

        container.appendChild(line);
    }
}

function buildPreviewQuestions() {
    const format = document.getElementById('format_override')?.value || 'A4';
    const layout = document.getElementById('layout_mode')?.value || 'ensemble';
    const container = document.getElementById('preview-questions');

    container.innerHTML = '';

    const isA3 = format === 'A3';

    if (isA3) {
        container.className = 'grid grid-cols-2 gap-x-10 gap-y-4';
    } else {
        container.className = 'space-y-4';
    }

    const limit = isA3 ? Math.min(previewQuestions.length, 30) : Math.min(previewQuestions.length, 8);

    for (let i = 0; i < limit; i++) {
        const q = previewQuestions[i];
        if (!q) continue;

        const questionBlock = document.createElement('div');
        questionBlock.className = 'break-inside-avoid space-y-2';

        const title = document.createElement('p');
        title.className = isA3
            ? 'text-[9px] text-gray-900 leading-snug'
            : 'text-[11px] text-gray-900 leading-snug';

        title.dir = isArabicPreview ? 'rtl' : 'ltr';
        title.classList.toggle('text-right', isArabicPreview);
        title.innerHTML = isArabicPreview
            ? `<span class="font-bold">${previewTexts.question} ${i + 1}</span> ${q.text}`
            : `<span class="font-bold">${previewTexts.question} ${i + 1}</span> ${q.text}`;

        questionBlock.appendChild(title);

        if (layout === 'separate') {
            const info = document.createElement('p');
            info.className = 'text-[10px] italic text-gray-400';
            info.textContent = previewTexts.separate_answers;
            info.dir = isArabicPreview ? 'rtl' : 'ltr';
            info.classList.toggle('text-right', isArabicPreview);

            questionBlock.appendChild(info);
        } else {
            const answers = document.createElement('div');
            answers.className = isA3
                ? 'grid grid-cols-2 gap-x-5 gap-y-2'
                : 'grid grid-cols-2 gap-x-8 gap-y-2';

            q.answers.slice(0, 4).forEach(answer => {
                const row = document.createElement('div');
                row.className = isA3
                    ? 'flex items-center gap-2 text-[9px] text-gray-700'
                    : 'flex items-center gap-2 text-[10px] text-gray-700';

                row.dir = isArabicPreview ? 'rtl' : 'ltr';
                row.classList.toggle('flex-row-reverse', isArabicPreview);
                row.classList.toggle('text-right', isArabicPreview);

                row.innerHTML = `
                    <span class="inline-block w-3.5 h-3.5 border border-gray-500 shrink-0"></span>
                    <span>${answer}</span>
                `;

                answers.appendChild(row);
            });

            questionBlock.appendChild(answers);
        }

        container.appendChild(questionBlock);
    }

    if (previewQuestions.length > limit) {
        const more = document.createElement('p');
        more.className = 'text-[10px] italic text-gray-400 col-span-2';
        more.textContent = isArabicPreview
            ? `... و ${previewQuestions.length - limit} ${previewTexts.more_questions}`
            : `... et ${previewQuestions.length - limit} ${previewTexts.more_questions}`;

        more.dir = isArabicPreview ? 'rtl' : 'ltr';
        more.classList.toggle('text-right', isArabicPreview);

        container.appendChild(more);
    }
}

function updateSheetPreview() {
    const format = document.getElementById('format_override')?.value || 'A4';
    const mode = document.getElementById('print_mode')?.value || 'anonymous';
    const layout = document.getElementById('layout_mode')?.value || 'ensemble';

    const sheet = document.getElementById('sheet-preview');
    const badge = document.getElementById('preview-badge');
    const nameBox = document.getElementById('preview-name-box');
    const studentName = document.getElementById('preview-student-name');
    const instructions = document.getElementById('preview-instructions');
    const codeZone = document.getElementById('preview-code-zone');
    const topCode = document.getElementById('preview-top-code');

    const isA3 = format === 'A3';
    const isNamed = mode === 'named';

    if (isA3) {
        sheet.style.width = '900px';
        sheet.style.minHeight = '1280px';
    } else {
        sheet.style.width = '560px';
        sheet.style.minHeight = '790px';
    }

    badge.textContent = `${format} • ${isNamed ? previewTexts.named : previewTexts.anonymous}`;

    instructions.textContent = layout === 'separate'
        ? previewTexts.separate_instruction
        : previewInstructions;

    instructions.dir = isArabicPreview ? 'rtl' : 'ltr';
    instructions.classList.toggle('text-right', isArabicPreview);


    if (isNamed) {
        nameBox.style.display = 'block';
        studentName.textContent = previewTexts.student_name;
        codeZone.style.display = 'none';
        topCode.style.visibility = 'hidden';
    } else {
        nameBox.style.display = 'block';
        studentName.textContent = previewTexts.blank_name;
        codeZone.style.display = 'block';
        topCode.style.visibility = 'visible';
        buildPreviewCodeZone();
    }

    buildPreviewQuestions();
}

function toggleStudentList() {
    const mode = document.getElementById('print_mode').value;
    const studentSelection = document.getElementById('student-selection');
    const copiesContainer = document.getElementById('copies_container');
    const extraNamedCopies = document.getElementById('extra_named_copies_container');

    if (mode === 'named') {
        studentSelection.classList.remove('hidden');
        extraNamedCopies.classList.remove('hidden');
        copiesContainer.classList.add('hidden');
    } else {
        studentSelection.classList.remove('hidden');
        extraNamedCopies.classList.add('hidden');
        copiesContainer.classList.remove('hidden');
    }

    updateSheetPreview();
}

function handleLayoutChange() {
    const layout = document.getElementById('layout_mode').value;
    const formatContainer = document.getElementById('format_selection_container');

    if (layout === 'separate') {
        formatContainer.classList.add('hidden');
        document.getElementById('format_override').value = 'A4';
    } else {
        formatContainer.classList.remove('hidden');
    }

    updateSheetPreview();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function(e) {
            const btn = e.submitter;
            if (btn && btn.value === 'pdf') {
                btn.innerHTML = '<i class="animate-spin mr-2" data-lucide="loader-2"></i> Génération...';
                lucide.createIcons();
            }
        });
    }

    handleLayoutChange();
    toggleStudentList();
    updateSheetPreview();

    ['format_override', 'print_mode', 'layout_mode'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', updateSheetPreview);
        }
    });

    if (window.lucide) lucide.createIcons();
});
</script>