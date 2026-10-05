<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class ExamController extends Controller
{
    private function examFolderName(Exam $exam): string
{
    $title = trim($exam->title ?? 'exam');

    $slug = \Illuminate\Support\Str::slug($title, '-');

    if ($slug === '') {
        $slug = 'exam';
    }

    return $slug . '_' . $exam->id;
}

    // Création d'un examen
    public function create()
    {
        return view('exams.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'course_name' => 'required',
            'exam_language' => 'required',
        ]);
        $exam = Exam::create([
            'title' => $request->title,
            'description' => $request->description,
            'course_name' => $request->course_name,
            'teacher_name' => $request->teacher_name,
            'duration' => $request->duration,
            'total_points' => 0,
            'page_format' => $request->page_format,
            'exam_language' => $request->exam_language ?? 'fr',
            'copies_number' => 1,
            'student_id_length' => $request->student_id_length,
            'instructions' => $request->instructions,
            'shuffle_questions' => $request->shuffle_questions ? 1 : 0,
            'shuffle_answers' => $request->shuffle_answers ? 1 : 0,
            'teacher_id' => auth()->id(),
        ]);
        return redirect()->route('questions.create', $exam->id);
    }


    public function index(Request $request)
{
    $search = trim($request->get('search', ''));
    $status = $request->get('status', '');

    $query = Exam::where('teacher_id', auth()->id())
        ->withCount('questions');

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('course_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($status === 'published') {
        $query->has('questions');
    } elseif ($status === 'draft') {
        $query->doesntHave('questions');
    }

    $exams = $query->latest()->paginate(6)->withQueryString();

    return view('exams.index', compact('exams', 'search', 'status'));
}

    public function show(Exam $exam)
    {
        $exam->load('questions.answers');
        return view('exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        if ($exam->teacher_id !== auth()->id()) { abort(403); }
        return view('exams.edit', compact('exam'));
    }


    public function update(Request $request, Exam $exam){
        if ($exam->is_locked) {
            return back()->with('error', 'Cet examen est verrouillé car les résultats ont déjà été générés.');
        }

        $request->validate(['title' => 'required', 'course_name' => 'required']);
        $data = $request->except('total_points');
        $data['shuffle_questions'] = $request->has('shuffle_questions');
        $data['shuffle_answers'] = $request->has('shuffle_answers');
        $exam->update($data);
        return redirect()->route('exams.index')->with('success', 'Examen mis à jour !');
    }

    public function destroy(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403, 'Action non autorisée.');
    }

    $exam->delete();

    return redirect()
        ->route('exams.index')
        ->with('success', 'Examen supprimé avec succès.');
}

    public function generate(Exam $exam){
    if ($exam->teacher_id !== auth()->id()) { abort(403); }
    $exam->load('questions.answers');
    
    $questionCount = $exam->questions->count();
    $longQuestions = 0;
    foreach ($exam->questions as $q) {
        if (mb_strlen(trim(strip_tags($q->question_text))) > 150) {
            $longQuestions++;
        }
        
    }
    $recommendedFormat = ($questionCount > 20 || $longQuestions > 8) ? 'A3' : 'A4';
    $students = Student::where('teacher_id', auth()->id())
        ->where('student_code', 'not like', 'TMP%')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();
    return view('exams.generate', compact('exam', 'students', 'recommendedFormat'));
}

private function cleanAmcTxt($text)
{
    if (empty($text)) return "";

    // 1. Supprimer les balises HTML éventuelles
    $text = strip_tags($text);

    // 2. Décoder les entités (ex: &eacute; devient é)
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // 3. Remplacer les retours à la ligne par des espaces 
    //$text = str_replace(['*', '+', '-', '[' , ']'], ' ', $text);

    // on enlève seulement les retours ligne/tabulations
    $text = preg_replace('/[\r\n\t]+/', ' ', $text);

     // éviter les espaces multiples
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim($text);
}

    public function processGeneration(Request $request, Exam $exam)
{
    $format = $request->input('format', 'pdf');
    $formatOverride = $request->input('format_override');

    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    if ($formatOverride && in_array($formatOverride, ['A4', 'A3']) && $exam->page_format !== $formatOverride) {
        $exam->update(['page_format' => $formatOverride]);
        $exam->refresh();
    }

    $mode = $request->input('print_mode', 'anonymous');
    $selectedStudentIds = $request->input('student_ids', []);

    if ($format === 'presence_pdf') {
        $selectedStudents = collect();

        if (!empty($selectedStudentIds)) {
            $selectedStudents = Student::where('teacher_id', auth()->id())
                ->whereIn('student_code', $selectedStudentIds)
                ->where('student_code', 'not like', 'TMP%')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        $extraNamedCopies = max(0, (int) $request->input('extra_named_copies', 0));
        $texts = $this->getExamTexts($exam->exam_language ?? 'fr');

        $extraStudents = collect();

        for ($i = 1; $i <= $extraNamedCopies; $i++) {
            $letter = $this->isArabicExam($exam)
                ? (string) $i
                : chr(64 + (($i - 1) % 26) + 1);

            $extraStudents->push((object) [
                'student_code' => 'TMP' . $exam->id . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'first_name' => $texts['temporary_student_prefix'],
                'last_name' => $letter,
            ]);
        }

        $students = $selectedStudents->concat($extraStudents)->values();

        $html = view('exams.students-presence-pdf', [
            'exam' => $exam,
            'students' => $students,
        ])->render();

        $pdfPath = storage_path('app/liste_presence_' . $exam->id . '.pdf');

        Browsershot::html($html)
            ->setChromePath('/usr/bin/chromium')
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'])
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->savePdf($pdfPath);

        return response()
            ->download($pdfPath, 'liste_presence_' . str_replace(' ', '_', $exam->title) . '.pdf')
            ->deleteFileAfterSend(true);

    }

    // Bloquer si mode nominatif sans étudiants
    if ($mode === 'named' && empty($selectedStudentIds)) {
        return back()->with('error', 'Vous devez sélectionner au moins un étudiant pour le mode nominatif.');
    }

    $layoutMode = $request->input('layout_mode', 'ensemble');

    $workDir = storage_path("app/amc/" . $this->examFolderName($exam));
    $wslPath = PHP_OS_FAMILY === 'Windows' ? "/mnt/c" . str_replace(['C:', '\\'], ['', '/'], $workDir) : str_replace('\\', '/', $workDir);

    $this->prepareAmcProject($workDir, $wslPath);

    //  CLEANUP AVANT de recréer source.txt
    $cleanupCommand = (PHP_OS_FAMILY === 'Windows' ? "wsl bash -c " : "bash -c ") . escapeshellarg(
        "cd $wslPath && " .
        "rm -f DOC-*.pdf DOC-*.xy DOC-*.tex DOC-*.latex DOC-*.amc DOC-*.aux DOC-*.log state.html saved-*.zip amc-compiled.* source_filtered.tex && " .
        "rm -f data/layout.sqlite data/report.sqlite data/capture.sqlite data/scoring.sqlite"
    );

    exec($cleanupCommand . " 2>&1", $cleanupOutput, $cleanupReturn);

    clearstatcache();

    // ÉTUDIANTS / COPIES 
    $selectedStudentIds = $request->input('student_ids', []);
    $selectedStudents = collect();

    if (!empty($selectedStudentIds)) {
        $selectedStudents = Student::where('teacher_id', auth()->id())
            ->whereIn('student_code', $selectedStudentIds)
            ->get();
    }

    // fichier méta pour la session
    $sessionMeta = [
        'mode' => $mode,
        'student_codes' => $selectedStudents->pluck('student_code')->values()->all(),
        'saved_at' => now()->toDateTimeString(),
    ];

    file_put_contents(
        $workDir . "/session_meta.json",
        json_encode($sessionMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    // important : si on est en anonyme, supprimer l'ancien students_list.csv
    if ($mode === 'anonymous') {
        if (file_exists($workDir . "/students_list.csv")) {
            @unlink($workDir . "/students_list.csv");
        }

        $copies = max(1, (int) $request->input('copies', 1));
        $exam->update(['copies_number' => $copies]);
    }

    // mode nominatif
    if ($mode === 'named') {
        $extraNamedCopies = max(0, (int) $request->input('extra_named_copies', 0));

        $generatedExtras = collect();

        $texts = $this->getExamTexts($exam->exam_language ?? 'fr');

        for ($i = 1; $i <= $extraNamedCopies; $i++) {
            if ($this->isArabicExam($exam)) {
                //$arLetters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح', 'ط', 'ي'];
                //$letter = $arLetters[($i - 1) % count($arLetters)];
                $letter = (string) $i;
            } else {
                $letter = chr(64 + (($i - 1) % 26) + 1);
            }
            $tmpCode = 'TMP' . $exam->id . str_pad((string)$i, 4, '0', STR_PAD_LEFT);

            $tmpStudent = Student::updateOrCreate(
                [
                    'teacher_id' => auth()->id(),
                    'student_code' => $tmpCode,
                ],
                [
                    'first_name' => $texts['temporary_student_prefix'],
                    'last_name' => $letter,
                ]
            );

            $generatedExtras->push($tmpStudent);
        }

        $allNamedStudents = $selectedStudents->concat($generatedExtras)->values();

        $copies = max(1, $allNamedStudents->count());
        $exam->update(['copies_number' => $copies]);

        // $csvClean = "\xEF\xBB\xBFcode,name,surname\n";
        // $csvAmc   = "\xEF\xBB\xBFcode,name,surname\n";

        $csvAssociation = "code,name,surname\n";
        $csvPdf = "code,name,surname\n";
        $csvExcel = "\xEF\xBB\xBFcode,name,surname\n";

        foreach ($allNamedStudents as $s) {
            // 1) Pour association automatique AMC : sans BOM, sans LaTeX
            //$csvAssociation .= "{$s->student_code},{$s->first_name},{$s->last_name}\n";
            $displayName = $s->first_name . ' ' . $s->last_name . ' - ' . $s->student_code;

            $csvAssociation .= "{$s->student_code},{$displayName},\n";

            // 2) Pour affichage PDF arabe AMC : sans BOM, avec arabicfont
            //if ($this->isArabicExam($exam)) {
              //  $firstNamePdf = "{\\arabicfont " . $s->first_name . "}";
               // $lastNamePdf  = "{\\arabicfont " . $s->last_name . "}";
            // } else {
               // $firstNamePdf = $s->first_name;
               // $lastNamePdf  = $s->last_name;
            //}

            // $csvPdf .= "{$s->student_code},{$firstNamePdf},{$lastNamePdf}\n";

            if ($this->isArabicExam($exam)) {
    $displayNamePdf =
        "{\\arabicfont " .
        $s->first_name . " " .
        $s->last_name . " - " .
        $s->student_code .
        "}";

    $csvPdf .= "{$s->student_code},{$displayNamePdf},\n";
} else {
    $displayNamePdf =
        $s->first_name . ' ' .
        $s->last_name . ' - ' .
        $s->student_code;

    $csvPdf .= "{$s->student_code},{$displayNamePdf},\n";
}

            // 3) Pour Excel seulement : avec BOM, sans LaTeX
            $csvExcel .= "{$s->student_code},{$s->first_name},{$s->last_name}\n";
        }

        file_put_contents($workDir . "/students_list.csv", $csvAssociation);
        file_put_contents($workDir . "/students_list_amc.csv", $csvPdf);
        file_put_contents($workDir . "/students_list_excel.csv", $csvExcel);

        //file_put_contents($workDir . "/students_list.csv", $csvClean);
        //file_put_contents($workDir . "/students_list_amc.csv", $csvAmc);
        $sessionMeta = [
            'mode' => $mode,
            'student_codes' => $selectedStudents->pluck('student_code')->values()->all(),
            'temporary_students' => $generatedExtras->map(function ($s) {
                return [
                    'student_code' => $s->student_code,
                    'first_name' => $s->first_name,
                    'last_name' => $s->last_name,
                ];
            })->values()->all(),
            'saved_at' => now()->toDateTimeString(),
        ];

        file_put_contents(
            $workDir . "/session_meta.json",
            json_encode($sessionMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $selectedStudents = $allNamedStudents;
    }

    

    // recréer options + source après nettoyage
    $this->createAmcOptionsFile($workDir, $exam ,$copies);

    // GÉNÉRATION DU SOURCE.TXT
    $amcTxtContent = $this->buildAmcTxt($exam, $copies, $layoutMode, $exam->shuffle_questions, $exam->student_id_length, $mode, $selectedStudents);
    file_put_contents($workDir . "/source.txt", $amcTxtContent);
    //file_put_contents($workDir . "/source.txt", "\xEF\xBB\xBF" . mb_convert_encoding($amcTxtContent, 'UTF-8'));
    if ($format === 'tex') {
        return response()->download($workDir . "/source.txt", "AMC_Source_{$exam->title}.txt");
    }

    // PIPELINE AMC COMPLET (Simule l'appui sur "Imprimer les copies" dans le GUI)
    $timestamp = date('Y-m-d-H-i-s');
    $zipName = "saved-" . $timestamp . ".zip";
    
    if (!file_exists($workDir . "/source.txt")) {
         return back()->with('error', 'source.txt introuvable juste avant la génération AMC.');
    
    }
    if (!isset($copies) || $copies < 1) {
        return back()->with('error', 'Nombre de copies invalide.');
    }

    $isArabic = $this->isArabicExam($exam);

    if ($isArabic) {
        $command = $this->buildArabicGenerationCommand($wslPath, $copies, $zipName);
    } else {
        $command = (PHP_OS_FAMILY === 'Windows' ? "wsl bash -c " : "bash -c ") . escapeshellarg(
        "cd $wslPath && " .
        "export LC_ALL=C.UTF-8 && " .
        "rm -f state.html && " .

        // 1) sujet + fichier de calage
        "/usr/bin/auto-multiple-choice prepare --mode s --with xelatex --filter plain --prefix DOC- --data ./data --n-copies $copies --out-sujet DOC-sujet.pdf --out-corrige DOC-corrige.pdf --out-calage DOC-calage.xy source.txt && " .

        // 2) import du calage vers layout.sqlite  <<< IMPORTANT
        "/usr/bin/auto-multiple-choice meptex --src DOC-calage.xy --data ./data && " .

        // 3) scoring / report
        "/usr/bin/auto-multiple-choice prepare --mode b --with xelatex --filter plain --prefix DOC- --data ./data --out-corrige DOC-corrige.pdf --out-catalog DOC-catalog.pdf source.txt --with-correction-sheet && " .

        // 4) debug sqlite
        "sqlite3 data/layout.sqlite 'select count(*) from layout_page;' && " .
        "sqlite3 data/layout.sqlite 'select count(*) from layout_mark;' && " .
        "sqlite3 data/layout.sqlite 'select count(*) from layout_box;' && " .
        "sqlite3 data/layout.sqlite 'select count(*) from layout_zone;' && " .

        // 5) state
        "echo '<html><body><h1>AMC Project State</h1><p>Prepared via Laravel</p></body></html>' > state.html && " .

        // 6) droits
        "chmod -R 777 . && " .

        // 7) zip
        "zip -r $zipName " .
            "DOC-calage.xy " .
            "DOC-catalog.pdf " .
            "DOC-corrige.pdf " .
            "DOC-sujet.pdf " .
            "source.txt " .
            "state.html " .
            "data/layout.sqlite " .
            "data/report.sqlite"
        );
    }
    set_time_limit(300);
    exec($command . " 2>&1", $output, $returnVar);
    clearstatcache();
    

    $layoutFile = $workDir . "/data/layout.sqlite";

/*
if (!$this->amcLayoutIsValid($layoutFile)) {
    Log::error('AMC generation: layout invalid', [
        'exam_id' => $exam->id,
        'output' => $output,
        'options' => @file_get_contents($workDir . '/options.xml'),
        'source' => @file_get_contents($workDir . '/source.txt'),
    ]);

    return back()->with('error', 'Le PDF est généré mais AMC n’a pas créé un layout valide pour le scan.');
}
*/

    if (file_exists($layoutFile)) {
        try {
            $pdo = new \PDO('sqlite:' . $layoutFile);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $pageCount = (int) $pdo->query("SELECT COUNT(*) FROM layout_page")->fetchColumn();
            $markCount = (int) $pdo->query("SELECT COUNT(*) FROM layout_mark")->fetchColumn();
            $boxCount  = (int) $pdo->query("SELECT COUNT(*) FROM layout_box")->fetchColumn();

            Log::info('AMC layout counts after generation', [
                'pageCount' => $pageCount,
                'markCount' => $markCount,
                'boxCount'  => $boxCount,
            ]);
        } catch (\Throwable $e) {
            Log::error('Lecture layout.sqlite impossible', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    $pdfFile = $workDir . "/DOC-sujet.pdf";
    $layoutFile = $workDir . "/data/layout.sqlite";

    // 1. Si AMC a échoué ou si le PDF n'existe pas => erreur
    // if ($returnVar !== 0 || !file_exists($pdfFile)) {
    if (!file_exists($pdfFile)) {
        Log::error("AMC Prep Failed", [
            'exam_id' => $exam->id,
            'output' => $output,
            'return_var' => $returnVar,
        ]);

        return back()->with('error', "Erreur AMC : " . implode(' | ', $output));
    }

    // 2. Donner les droits au PDF
    exec((PHP_OS_FAMILY === 'Windows' ? "wsl chmod " : "chmod ") . "666 $wslPath/DOC-sujet.pdf");
    @chmod($pdfFile, 0777);

    // 3. Logger si le layout est invalide, MAIS ne pas bloquer le téléchargement
    if (!file_exists($layoutFile) || !$this->amcLayoutIsValid($layoutFile)) {
        Log::error('AMC generation: layout vide ou invalide', [
            'exam_id' => $exam->id,
            'layout_file' => $layoutFile,
            'output' => $output,
        ]);
    }

    session()->flash(
        'success',
        'PDF généré avec succès. Ne modifiez pas l’examen après correction des copies.'
    );


    // 4. Télécharger le PDF quoi qu'il arrive si le PDF existe
    return response()->download(
        $pdfFile,
        "Examen_" . str_replace(' ', '_', $exam->title) . ".pdf",
        ['Content-Type' => 'application/pdf']
    )
    ->deleteFileAfterSend(false);;
    
}

     private function buildAmcTxt(Exam $exam, $copies, $layoutMode, $shuffle, $idLength, $mode = 'anonymous', $selectedStudents = null)
    {
       // dd([
    //'mode' => $mode,
    //'nb_students' => $selectedStudents?->count(),
    //'students' => $selectedStudents?->take(3),
//]);
        $lines = [];
    
        
        // 1. ENTÊTE AMC-TXT
        //$lines[] = "Lang: FR";
        $isArabic = $this->isArabicExam($exam);

        $lang = strtoupper($exam->exam_language ?? 'fr');

        if ($isArabic) {
           $lang = 'AR';
        }
        
        if (!in_array($lang, ['FR', 'EN', 'AR'])) {
            $lang = 'FR';
        }

        /*
        $lines[] = "Lang: " . $lang;
        if ($isArabic) {
            $lines[] = "ArabicFont: Amiri";
            //$lines[] = "LaTeX: 1";
            //$lines[] = "LaTeX-Preambule: \\let\\XeTeX\\undefined";
            //$lines[] = "LaTeX-Preambule: \\let\\XeLaTeX\\undefined";
        }
        */

        //ARABIC
        if ($isArabic) {
            return $this->buildArabicAmcTxt($exam, $layoutMode, $idLength, $mode, $selectedStudents);
        }

        $lines[] = "Lang: " . $lang;
        //$lines[] = "Footer: Page \\thepage";
        
        // $lines[] = "Title: " . $exam->course_name . " - " . $exam->title;
        $title = $exam->course_name . " - " . $exam->title;

        if ($isArabic) {
            $title = $this->arabicWrapForPlainAmc($title);
        }

        $lines[] = "Title: " . $title;

        // $lines[] = "Presentation: " . ($exam->instructions ?? "Veuillez répondre aux questions.");
        $texts = $this->getExamTexts($exam->exam_language ?? 'fr');

        if ($lang === 'EN') {
            $presentation =
                "Duration : {$exam->duration} minutes\n" .
                "Date : " . now()->format('d/m/Y') . "\n\n" .
                ($exam->instructions ?? $texts['default_instructions']);
        } else {
            $presentation =
                "Durée : {$exam->duration} minutes\n" .
                "Date : " . now()->format('d/m/Y') . "\n\n" .
                ($exam->instructions ?? $texts['default_instructions']);
        }

        $lines[] = "Presentation: " . $presentation;


        if ($layoutMode === 'separate') {
            $lines[] = "SeparateAnswerSheet: 1";
            $lines[] = "AnswerSheetTitle: " . $texts['answer_sheet_title'];
        }
        $lines[] = "CompleteMulti: 0";
        $lines[] = "ShuffleQuestions: " . ($exam->shuffle_questions ? "1" : "0");
    
        // Forcer A4 si layoutMode = separate
        $isA3 = strtoupper($exam->page_format) === 'A3' && $layoutMode !== 'separate';

        if ($isA3) {
            $lines[] = "PaperSize: A3";
            $lines[] = "Columns: 2";
        } else {
            $lines[] = "PaperSize: A4";
        }

        // if ($layoutMode === 'separate') {
          //  $lines[] = "SeparateAnswerSheet: 1";
           // $lines[] = "AnswerSheetTitle: Feuille de réponses";
        // }

        // 2. IDENTIFICATION
        if ($mode === 'named' && $selectedStudents && $selectedStudents->count() > 0) {
            $lines[] = "PreAssociation: students_list.csv";
            $lines[] = "PreAssociationKey: code";
            $lines[] = "Code: code";

            $lines[] = "";

            //$lines[] = "Code étudiant : _________________________________________________";
            //$lines[] = "Nom et prénom : _________________________________________________";
            $lines[] = "";
        } else {
            $lines[] = "code: " . max(1, $exam->student_id_length);
            if ($isArabic) {
                $lines[] = "";
            }

            $lines[] = "";
        }

        $lines[] = ""; 

        // 3. QUESTIONS
        foreach ($exam->questions as $question) {
            $text = trim(strip_tags($question->question_text));
            if ($text === '') continue;
            $cleanQuestionText = $this->cleanAmcTxt($question->question_text);
            if ($isArabic) {
                $cleanQuestionText = $this->arabicWrapForPlainAmc($cleanQuestionText);
            }
            if (empty($cleanQuestionText)) continue;

            if ($question->answers->count() === 0) {
                continue;
            }

            if ($question->answers->where('is_correct', 1)->count() === 0) {
                continue; // ignore question invalide
            }

            // --- 1. TYPE DE QUESTION (* simple, ** multiple) ---
            $isMultiple = in_array($question->question_type, ['multiple', 'qcm', 'checkbox']);
            $lineStart = $isMultiple ? "**" : "*";

            // --- 2. OPTIONS [horiz,shuffle] ---
            $options = [];
    
            // Détection auto du mode horizontal (si réponses courtes)
            $isShort = true;
            foreach ($question->answers as $answer) {
                if (mb_strlen(trim($answer->answer_text)) > 10) {
                    $isShort = false;
                    break;
                }
            }
            if ($isShort) $options[] = "horiz";
            if ($exam->shuffle_answers) $options[] = "shuffle";

            // Construction de la chaîne d'options : [horiz,shuffle]
            $optionsStr = !empty($options) ? "[" . implode(",", $options) . "]" : "";

            // --- 3. BARÈME {b=1,m=0} ---
            $b = (float) ($question->points_correct ?? 1);
            $m = -(float) ($question->points_penalty ?? 0);

            if ($isMultiple) {
                $baremeStr = "{formula=(NMC>0 ? 0 : NBC/NB*$b)}";
            } else {
                $baremeStr = "{b=$b,m=$m}";
            }

            // --- 4. ASSEMBLAGE SANS ESPACES ENTRE LES BLOCS ---
            // La syntaxe doit être EXACTEMENT : *[options]{bareme} Enoncé
            $lines[] = $lineStart . $optionsStr . $baremeStr . " " . $cleanQuestionText;

            // --- 5. RÉPONSES ---
            foreach ($question->answers as $answer) {
                $symbol = $answer->is_correct ? "+" : "-";
                $cleanAnswerText = $this->cleanAmcTxt($answer->answer_text);
                if ($isArabic) {
                    $cleanAnswerText = $this->arabicWrapForPlainAmc($cleanAnswerText);
                }
                // Pas d'espace avant le symbole (+ ou -), un espace après.
                $lines[] = $symbol . " " . $cleanAnswerText;
            }


            $lines[] = ""; // Espace entre les questions
        }
    
     return implode("\n", $lines);
}


    private function cleanLatex($text, bool $isArabic = false) {
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    if ($isArabic) {
        $text = preg_replace('/[\r\n\t]+/u', ' ', $text);
        return trim($text);
    }

    $map = [
        '\\' => '\textbackslash{}', '%' => '\%', '$' => '\$', '&' => '\&',
        '#' => '\#', '_' => '\_', '{' => '\{', '}' => '\}',
        '~' => '\textasciitilde{}', '^' => '\textasciicircum{}',
        'é' => "{\'e}", 'è' => "{\`e}", 'ê' => "{\^e}", 'ë' => "{\"e}",
        'à' => "{\`a}", 'â' => "{\^a}", 'î' => "{\^\i}", 'ï' => "{\"\i}",
        'ô' => "{\^o}", 'û' => "{\^u}", 'ù' => "{\`u}", 'ç' => "{\c c}",
        'É' => "{\'E}", 'È' => "{\`E}", 'À' => "{\`A}", 'Ç' => "{\c C}"
    ];

    return strtr($text, $map);
}

    private function addExamContent(&$lines, $exam, $shuffle, $isA3, $layoutMode, $mode, $student = null, $idLength = 0) {
     
        dd($student);

        $validQuestions = $exam->questions->filter(fn($q) => trim($q->question_text) !== '');
        $questionCount = $validQuestions->count();

        // --- PARTIE 1 : FEUILLE DE QUESTIONS ---
        $lines[] = '\par \vspace{3mm}'; 
        $isArabic = $this->isArabicExam($exam);
        if (!empty($exam->instructions)) {
           $lines[] = '\noindent \textbf{Consignes :} \textit{' . $this->cleanLatex($exam->instructions, $isArabic) . '}\par \vspace{2mm}';
        }
        $lines[] = '\hrule \vspace{4mm}';

        if ($shuffle) { 
           $lines[] = '\shufflegroup{tout}';
        }

        if ($isA3) {
            if ($mode === 'named') {
                // Bloc A3 nominatif
                $lines[] = '\pdfpagewidth=42cm \pdfpageheight=29.7cm';
                $lines[] = '\paperwidth=42cm \paperheight=29.7cm';
                $lines[] = '\newgeometry{hmargin=2cm,top=2cm,bottom=2cm}';

                if ($student) {
                    $lines[] = '\noindent \textbf{Nom :} ' . $this->cleanLatex($student->first_name . ' ' . $student->last_name, $isArabic) . ' \quad ';
                    $lines[] = '\textbf{Code :} ' . $this->cleanLatex($student->student_code) . '\par \vspace{4mm}';
                }

                $isNamed = ($mode === 'named' && $student);

                if ($isNamed) {
                    $texts = $this->getExamTexts($exam->exam_language ?? 'fr');

                    $lines[] = '\noindent \textbf{' .
                        $this->cleanLatex($texts['student_name_label'], $isArabic)
                        . '} ' .
                        $this->cleanLatex(
                            $student->first_name . ' ' . $student->last_name,
                            $isArabic
                        ) . '\\';

                    $lines[] = '\textbf{Code :} ' . $student->student_code . '\par \vspace{4mm}';

                }

                // --- Ligne verticale A3 entre colonnes ---
                $lines[] = '\noindent';
                $lines[] = '\begin{minipage}[t]{0.49\linewidth}'; // colonne gauche
                $lines[] = '\insertgroup{tout}';
                $lines[] = '\end{minipage}';
                $lines[] = '\hfill';
                $lines[] = '\begin{minipage}[t]{0.02\linewidth}'; // barre verticale
                $lines[] = '\vspace*{-1cm}\rule{0.4pt}{28cm}'; // 28cm = hauteur A3 portrait (ou paysage)
                $lines[] = '\end{minipage}';
                $lines[] = '\hfill';
                $lines[] = '\begin{minipage}[t]{0.49\linewidth}'; // colonne droite
                $lines[] = '\insertgroup{tout}';
                $lines[] = '\end{minipage}';

                $lines[] = '\clearpage';
                $lines[] = '\restoregeometry';
                return; // on sort car on a déjà ajouté tout le contenu A3
            }

            if ($mode === 'anonymous') {
                // Bloc A3 anonyme
                $lines[] = '\pdfpagewidth=42cm \pdfpageheight=29.7cm';
                $lines[] = '\paperwidth=42cm \paperheight=29.7cm';
                $lines[] = '\newgeometry{hmargin=2cm,top=2cm,bottom=2cm}';

                // Bloc code étudiant
                $lines[] = '\noindent\begin{minipage}{\linewidth}';
                $lines[] = '  \begin{minipage}[b]{20cm}';
                $lines[] = '    \framebox[\linewidth]{\begin{minipage}{19.5cm} \vspace{2mm} \textbf{NOM - PRÉNOM :}\vspace*{8mm} \end{minipage}}';
                $lines[] = '  \end{minipage}\hfill';
                $lines[] = '\vspace{5mm}';
                $lines[] = '  \begin{minipage}[b]{7cm}';
                $lines[] = '    \centering \textbf{Code :}\newline';
                $lines[] = '\vspace{2mm}';
                $lines[] = '    {\AMCboxDimensions{size=3ex}\AMCcodeH{etu}{' . $idLength . '}}';
                $lines[] = '  \end{minipage}';
                $lines[] = '\end{minipage}';
                $lines[] = '\vspace{6mm}';

                // --- Ligne verticale A3 entre colonnes ---
                $lines[] = '\noindent';
                $lines[] = '\begin{minipage}[t]{0.49\linewidth}'; // colonne gauche
                $lines[] = '\insertgroup{tout}';
                $lines[] = '\end{minipage}';
                $lines[] = '\hfill';
                $lines[] = '\begin{minipage}[t]{0.02\linewidth}'; // barre verticale
                $lines[] = '\vspace*{-1cm}\rule{0.4pt}{28cm}'; // 28cm = hauteur A3 portrait (ou paysage)
                $lines[] = '\end{minipage}';
                $lines[] = '\hfill';
                $lines[] = '\begin{minipage}[t]{0.49\linewidth}'; // colonne droite
                $lines[] = '\insertgroup{tout}';
                $lines[] = '\end{minipage}';

                $lines[] = '\clearpage';
                $lines[] = '\restoregeometry';
                return; // on sort car on a déjà ajouté tout le contenu A3
            }
        }

         // --- ÉTAPE 1 : ON INSÈRE LES QUESTIONS D'ABORD ---
        // Si layoutMode est separate, on ignore le multicols A3 pour rester sur une structure A4 simple
        if ($isA3 && $layoutMode !== 'separate') {
            $lines[] = '\begin{multicols}{2}';
            $lines[] = '\insertgroup{tout}';
            $lines[] = '\end{multicols}';
        } else {
            $lines[] = '\insertgroup{tout}';
        }
        
    }
        
    

    
 

public function resultats(Exam $exam)
{
    $exportFile = storage_path("app/amc/exam_{$exam->id}/exports/notes.csv");
    $results = [];

    if (file_exists($exportFile)) {
        if (($handle = fopen($exportFile, "r")) !== FALSE) {
            // On détecte l'entête
            $header = fgetcsv($handle, 1000, ";"); 
            
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (empty($data[0])) continue;
                
                // On mappe les données selon votre exemple CSV
                // "Copie";"A:code";"Nom";"Note";"Q001"...
                $results[] = [
                    'copie'  => $data[0],
                    'code'   => $data[1],
                    'nom'    => $data[2],
                    'note'   => $data[3],
                    // On pourrait aussi récupérer les réponses aux questions ici
                ];
            }
            fclose($handle);
        }
    }
    return view('exams.resultats', compact('exam', 'results'));
}



public function downloadCsv(Exam $exam) {
    $path = storage_path("app/amc/exam_{$exam->id}/exports/notes.csv");
    if (file_exists($path)) {
        return response()->download($path, "Notes_{$exam->title}.csv");
    }
    return back()->with('error', "Le fichier de notes n'a pas encore été généré.");
}



    private function createAmcOptionsFile($workDir, $exam, $copies)
{
    $studentsList = file_exists($workDir . '/students_list.csv')
        ? '%PROJET/students_list.csv'
        : '';

    $isArabic = strtolower((string) ($exam->exam_language ?? 'fr')) === 'ar';

    $texCommand = $isArabic ? 'xelatex' : 'xelatex';
    $arabicFontConfig = $isArabic
    ? '<tex_command>xelatex</tex_command>'
    : '<tex_command>xelatex</tex_command>';

    $xmlContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<project>
    <add_corrected/>
    <after_export>file</after_export>
    <allocate_ids/>
    <annote_position>marges</annote_position>
    <annote_rtl/>
    <assoc_code><preassoc></assoc_code>
    <auto_capture_mode>0</auto_capture_mode>
    <build_dir>.</build_dir>
    <code_examen/>
    <cr>cr</cr>
    <data>data</data>
    <doc_catalog>DOC-catalog.pdf</doc_catalog>
    <doc_indiv_solution>DOC-indiv-solution.pdf</doc_indiv_solution>
    <doc_question>DOC-sujet.pdf</doc_question>
    <doc_setting>DOC-calage.xy</doc_setting>
    <doc_solution>DOC-corrige.pdf</doc_solution>
    <encodage_csv>UTF-8</encodage_csv>
    <encodage_liste>UTF-8</encodage_liste>
    <export_csv_columns>student.copy,student.key,student.name</export_csv_columns>
    <export_csv_separateur>;</export_csv_separateur>
    <export_csv_ticked/>
    <export_include_abs/>
    <export_ncols>2</export_ncols>
    <export_ods_columns>student.copy,student.key,student.name</export_ods_columns>
    <export_ods_group>0</export_ods_group>
    <export_ods_groupsep>.</export_ods_groupsep>
    <export_ods_stats/>
    <export_ods_statsindic/>
    <export_pagesize>' . strtolower($exam->page_format) . '</export_pagesize>
    <export_sort>n</export_sort>
    <filter>plain</filter>
    <filtered_source>DOC-filtered.tex</filtered_source>
    <format_export>CSV</format_export>
    <liste_key>code</liste_key>
    <listeetudiants>' . $studentsList . '</listeetudiants>
    <maj_bareme>1</maj_bareme>
    <modele_regroupement/>
    <moteur_latex_b>xelatex</moteur_latex_b>
    <multi_scan_mode>strict</multi_scan_mode>
    <name_field_type/>
    <nom_examen>' . htmlspecialchars($exam->title, ENT_XML1, 'UTF-8') . '</nom_examen>
    <nombre_copies>' . (int)$copies . '</nombre_copies>
    <note_arrondi>inf</note_arrondi>
    <note_grain>0.5</note_grain>
    <note_max>' . (int) ($exam->total_points ?: $exam->total_calculated_points) . '</note_max>
    <note_max_plafond>1</note_max_plafond>
    <note_min>0</note_min>
    <note_null>0</note_null>
    <notes>notes.xml</notes>
    <pdf_password/>
    <pdf_password_key/>
    <pdf_password_use/>
    <pdfform>0</pdfform>
    <postcorrect_copy>0</postcorrect_copy>
    <postcorrect_set_multiple/>
    <postcorrect_student>0</postcorrect_student>
    <regroupement_compose/>
    <regroupement_copies>ALL</regroupement_copies>
    <regroupement_type>STUDENTS</regroupement_type>
    <seuil>0.15</seuil>
    <seuil_up>1</seuil_up>
    <texsrc>%PROJET/source.txt</texsrc>
    <verdict>%(ID) Mark: %s/%m (total score: %S/%M)</verdict>
    <verdict_q>"%s/%m"</verdict_q>
    <verdict_qc>"X"</verdict_qc>
</project>';

    file_put_contents($workDir . '/options.xml', $xmlContent);
}

private function prepareAmcProject($workDir, $wslPath)
{
    $dirs = [
        $workDir,
        $workDir . DIRECTORY_SEPARATOR . "data",
        $workDir . DIRECTORY_SEPARATOR . "scans",
        $workDir . DIRECTORY_SEPARATOR . "cr",
        $workDir . DIRECTORY_SEPARATOR . "cr" . DIRECTORY_SEPARATOR . "corrections",
        $workDir . DIRECTORY_SEPARATOR . "cr" . DIRECTORY_SEPARATOR . "corrections" . DIRECTORY_SEPARATOR . "jpg",
        $workDir . DIRECTORY_SEPARATOR . "cr" . DIRECTORY_SEPARATOR . "corrections" . DIRECTORY_SEPARATOR . "pdf",
        $workDir . DIRECTORY_SEPARATOR . "cr" . DIRECTORY_SEPARATOR . "diagnostic",
        $workDir . DIRECTORY_SEPARATOR . "cr" . DIRECTORY_SEPARATOR . "zooms",
        $workDir . DIRECTORY_SEPARATOR . "exports",
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    exec((PHP_OS_FAMILY === 'Windows' ? "wsl chmod " : "chmod ") . "-R 777 " . escapeshellarg($wslPath));
}

public function openInAmcGui(Exam $exam) {
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    exec((PHP_OS_FAMILY === 'Windows' ? "wsl DISPLAY=:0 auto-multiple-choice " : "auto-multiple-choice ") . "gui " . escapeshellarg($wslPath) . " > /dev/null 2>&1 &");
    return back()->with('success', "L'interface AMC est en cours d'ouverture...");
}


public function calculateLayout(Exam $exam) {
    $workDir = storage_path("app/amc/" . $this->examFolderName($exam));
    $wslPath = PHP_OS_FAMILY === 'Windows' ? "/mnt/c" . str_replace('\\','/', substr($workDir, 2)) : str_replace('\\', '/', $workDir);

    $command = (PHP_OS_FAMILY === 'Windows' ? "wsl bash -c " : "bash -c ") . escapeshellarg(
        "cd $wslPath && auto-multiple-choice prepare --mode f --filter plain --prefix DOC- --data ./data source.txt"
    );

    exec($command . " 2>&1", $output, $returnVar);

    if ($returnVar === 0) {
        return back()->with('success', 'Mises en page calculées avec succès (layout.sqlite mis à jour).');
    }
    return back()->with('error', 'Erreur lors du calcul.');
}

public function downloadArchive(Exam $exam)
{
    $workDir = storage_path("app/amc/" . $this->examFolderName($exam));
    // Trouve le dernier zip créé
    $files = glob($workDir . "/saved-*.zip");
    if (!empty($files)) {
        $lastZip = end($files);
        return response()->download($lastZip);
    }
    return back()->with('error', "Archive non trouvée.");
}

public function openAssociationGui(Exam $exam) {
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    exec((PHP_OS_FAMILY === 'Windows' ? "wsl DISPLAY=:0 auto-multiple-choice " : "auto-multiple-choice ") . "gui " . escapeshellarg($wslPath) . " > /dev/null 2>&1 &");
    return back()->with('success', "L'interface AMC s'ouvre pour associer manuellement les copies.");
}

private function getExamPaths(int $examId): array
{
    $exam = Exam::findOrFail($examId);
    $folderName = $this->examFolderName($exam);
    $workDir = $this->normalizePhpPath(storage_path("app/amc/{$folderName}"));

    if (preg_match('/^[A-Za-z]:\\\\/', $workDir)) {
        $drive = strtolower($workDir[0]);
        $rest = str_replace('\\', '/', substr($workDir, 2));
        $wslPath = "/mnt/{$drive}{$rest}";
    } else {
        $wslPath = str_replace('\\', '/', $workDir);
    }

    return [$workDir, rtrim($wslPath, '/')];
}

private function listImageFiles(string $scanPath): array
{
    $scanPath = $this->normalizePhpPath($scanPath);

    if (!is_dir($scanPath)) {
        return [];
    }

    $result = [];
    $items = @scandir($scanPath);

    if ($items === false) {
        return [];
    }

    foreach ($items as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }

        $full = $scanPath . DIRECTORY_SEPARATOR . $name;

        if (!file_exists($full) || !is_file($full)) {
            continue;
        }

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $result[] = $full;
        }
    }

    sort($result);

    return $result;
}

private function normalizePhpPath(string $path): string
{
    return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

private function amcLayoutIsValid(string $layoutFile): bool
{
    if (!file_exists($layoutFile) || filesize($layoutFile) === 0) {
        return false;
    }

    try {
        $pdo = new \PDO('sqlite:' . $layoutFile);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $neededTables = [
            'layout_page',
            'layout_mark',
            'layout_zone',
            'layout_box',
            'layout_question'
        ];

        foreach ($neededTables as $table) {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            if ($count === 0) {
                return false;
            }
        }

        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

private function getExamTexts(string $lang = 'fr'): array
{
    $lang = strtolower($lang);

    return match ($lang) {
        'en' => [
            'default_instructions' => 'Please answer the questions.',
            'answer_sheet_title' => 'Answer Sheet',
            'student_name_label' => 'Name and surname:',
            'student_code_label' => 'Student code:',
            'student_code_help_1' => 'Please code your student number',
            'student_code_help_2' => 'in the boxes below.',
            'temporary_student_prefix' => 'Student',
        ],
        'ar' => [
            'default_instructions' => 'يرجى الإجابة على الأسئلة.',
            'answer_sheet_title' => 'ورقة الإجابة',
            'student_name_label' => 'الاسم والنسب:',
            'student_code_label' => 'رقم الطالب:',
            'student_code_help_1' => 'يرجى ترميز رقم الطالب',
            'student_code_help_2' => 'في الخانات أسفله.',
            'temporary_student_prefix' => 'طالب',
        ],
        default => [
            'default_instructions' => 'Veuillez répondre aux questions.',
            'answer_sheet_title' => 'Feuille de réponses',
            'student_name_label' => 'Nom et prénom :',
            'student_code_label' => 'Code étudiant :',
            'student_code_help_1' => 'Veuillez coder votre numéro étudiant',
            'student_code_help_2' => 'dans les cases ci-dessous.',
            'temporary_student_prefix' => 'Etudiant',
        ],
    };
}

private function isArabicExam(Exam $exam): bool
{
    return strtolower((string) ($exam->exam_language ?? 'fr')) === 'ar';
}

private function arabicWrapForPlainAmc(string $text): string
{
    return trim($text);
}


private function buildArabicAmcTxt(Exam $exam, $layoutMode, $idLength, $mode = 'anonymous', $selectedStudents = null): string
{
    $paper = strtoupper($exam->page_format ?? 'A4');

    $lines[] = "PaperSize: " . $paper;
    $lines[] = "Lang: AR";
    $lines[] = "ArabicFont: Amiri";
    

    if ($paper === 'A3' && $layoutMode !== 'separate') {
        $lines[] = "Columns: 2";
    }

    if ($layoutMode === 'separate') {
        $lines[] = "SeparateAnswerSheet: 1";
        $lines[] = "AnswerSheetTitle: ورقة الإجابة";
        $lines[] = "AnswerSheetPresentation: املأ مربعات الإجابة الصحيحة.";
        $lines[] = "AnswerSheetColumns: " . ($paper === 'A3' ? "2" : "1");
    }

    if ($mode === 'named' && $selectedStudents && $selectedStudents->count() > 0) {
        $lines[] = "PreAssociation: students_list_amc.csv";
        $lines[] = "PreAssociationKey: code";
        $lines[] = "Code: code";
    } else {
        $lines[] = "CodeDigitsDirection: horizontal";
        $lines[] = "Code: " . max(1, $idLength);
    }

    if ($mode === 'named' && $selectedStudents && $selectedStudents->count() > 0) {
        $lines[] = "";
        $lines[] = "NameFieldWidth: 7cm";
        $lines[] = "NameFieldLines: 1";

    } else {
        $lines[] = "L-Student: يرجى ترميز رقم الطالب وكتابة الاسم الكامل.";
        $lines[] = "";
        $lines[] = "NameFieldWidth: 7cm";
        $lines[] = "NameFieldLines: 2";
        $lines[] = "L-Name: الاسم الكامل";
    }
    $lines[] = "";
    
    // $lines[] = "L-Name: .";
    //$lines[] = "L-Name: الاسم الكامل";
    $lines[] = "L-Question: س";
    $lines[] = "PackageOptions: outsidebox";
    $lines[] = "CompleteMulti: 0";
    $lines[] = "QuestionBlocks: 1";
    $lines[] = "DefaultScoringS: b=1,m=0,v=0,e=-0.5";
    $lines[] = "DefaultScoringM: formula=(NBC>NMC ? NBC-0.25*NMC : 0)";
    $lines[] = "";

    $title = trim($exam->course_name . " - " . $exam->title);
    $presentation =
        "المدة : {$exam->duration} دقيقة\n" .
        "التاريخ : " . now()->format('d/m/Y') . "\n\n" .
        ($exam->instructions ?: 'يرجى ترميز رقم الطالب وكتابة الاسم الكامل ثم الإجابة على الأسئلة.');


    $lines[] = "Title: " . $title;
    $lines[] = "Presentation: " . $presentation;
    $lines[] = "ShuffleQuestions: " . ($exam->shuffle_questions ? "1" : "0");
    $lines[] = "";

    foreach ($exam->questions as $question) {
        $questionText = $this->cleanAmcTxt($question->question_text);

        if ($questionText === '' || $question->answers->count() === 0) {
            continue;
        }

        if ($question->answers->where('is_correct', 1)->count() === 0) {
            continue;
        }

        $isMultiple = in_array($question->question_type, ['multiple', 'qcm', 'checkbox']);
        $lineStart = $isMultiple ? "**" : "*";

        $options = [];
        $isCompact = true;

        foreach ($question->answers as $answer) {
            $text = trim($answer->answer_text);

            // Compter les mots (option arabic)
            $words = preg_split('/\s+/u', $text);

            if (count($words) > 1 || mb_strlen($text) > 12) {
                $isCompact = false;
                break;
            }
        }

        if ($isCompact) {
            $options[] = "horiz";
        }

        if ($exam->shuffle_answers) {
            $options[] = "shuffle";
        }

        $optionsStr = !empty($options) ? "[" . implode(",", $options) . "]" : "";

        $b = (float) ($question->points_correct ?? 1);
        $m = -(float) ($question->points_penalty ?? 0);

        if ($isMultiple) {
            //$baremeStr = "{formula=(NMC>0 ? 0 : NBC/NB*$b)}";
            $penalty = (float) ($question->points_penalty ?? 0);
            $baremeStr = "{formula=((NBC/NB*$b)-(NMC*$penalty)>0 ? (NBC/NB*$b)-(NMC*$penalty) : 0)}";
        } else {
            $baremeStr = "{b=$b,m=$m}";
        }

        $lines[] = $lineStart . $optionsStr . $baremeStr . " " . $questionText;

        foreach ($question->answers as $answer) {
            $symbol = $answer->is_correct ? "+" : "-";
            $answerText = $this->cleanAmcTxt($answer->answer_text);
            $lines[] = $symbol . " " . $answerText;
        }

        $lines[] = "";
    }

    return implode("\n", $lines);
}


private function buildArabicGenerationCommand(string $wslPath, int $copies, string $zipName): string
{
    return (PHP_OS_FAMILY === 'Windows' ? "wsl bash -c " : "bash -c ") . escapeshellarg(
        "cd $wslPath && " .
        "export LC_ALL=C.UTF-8 && " .
        "rm -f state.html && " .

        "(/usr/bin/auto-multiple-choice prepare --mode s --with xelatex --filter plain --prefix DOC- --data ./data --n-copies $copies --out-sujet DOC-sujet.pdf --out-corrige DOC-corrige.pdf --out-calage DOC-calage.xy source.txt || true) && " .

        "if [ ! -f DOC-sujet.pdf ] && [ -f amc-compiled.pdf ]; then cp amc-compiled.pdf DOC-sujet.pdf; fi && " .
        "test -f DOC-sujet.pdf && " .

        "if [ -f DOC-calage.xy ]; then /usr/bin/auto-multiple-choice meptex --src DOC-calage.xy --data ./data || true; fi && " .

        "(/usr/bin/auto-multiple-choice prepare --mode b --with xelatex --filter plain --prefix DOC- --data ./data --out-corrige DOC-corrige.pdf --out-catalog DOC-catalog.pdf source.txt --with-correction-sheet || true) && " .

        "if [ ! -f DOC-corrige.pdf ] && [ -f amc-compiled.pdf ]; then cp amc-compiled.pdf DOC-corrige.pdf; fi && " .
        "if [ ! -f DOC-catalog.pdf ] && [ -f DOC-corrige.pdf ]; then cp DOC-corrige.pdf DOC-catalog.pdf; fi && " .

        "echo '<html><body><h1>AMC Arabic Project</h1></body></html>' > state.html && " .
        "chmod -R 777 . && " .

        "zip -r $zipName " .
        "DOC-sujet.pdf source.txt state.html " .
        "$(test -f DOC-calage.xy && echo DOC-calage.xy) " .
        "$(test -f DOC-corrige.pdf && echo DOC-corrige.pdf) " .
        "$(test -f DOC-catalog.pdf && echo DOC-catalog.pdf) " .
        "$(test -f data/layout.sqlite && echo data/layout.sqlite) " .
        "$(test -f data/report.sqlite && echo data/report.sqlite)"
    );
}

public function importForm()
{
    return view('exams.import');
}

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:json,csv,txt'
    ]);

    $file = $request->file('file');
    $extension = strtolower($file->getClientOriginalExtension());
    $content = file_get_contents($file->getRealPath());

    if ($extension === 'json') {
        $data = json_decode($content, true);

        if (!$data || !isset($data['questions']) || !is_array($data['questions'])) {
            return back()->with('error', 'Fichier JSON invalide.');
        }
    } else {
        $data = $this->parseExamCsv($file->getRealPath());

        if (!$data || empty($data['questions'])) {
            return back()->with('error', 'Fichier CSV invalide. Utilisez le séparateur point-virgule (;).');
        }
    }

    $exam = Exam::create([
        'title' => $data['title'] ?? 'Examen importé',
        'description' => $data['description'] ?? null,
        'course_name' => $data['course_name'] ?? 'Cours',
        'teacher_name' => $data['teacher_name'] ?? auth()->user()->full_name,
        'duration' => $data['duration'] ?? 60,
        'total_points' => 0,
        'page_format' => $data['page_format'] ?? 'A4',
        'exam_language' => $data['exam_language'] ?? 'fr',
        'copies_number' => $data['copies_number'] ?? 1,
        'student_id_length' => $data['student_id_length'] ?? 6,
        'instructions' => $data['instructions'] ?? null,
        'shuffle_questions' => $data['shuffle_questions'] ?? 1,
        'shuffle_answers' => $data['shuffle_answers'] ?? 1,
        'teacher_id' => auth()->id(),
    ]);

    $totalPoints = 0;

    foreach ($data['questions'] as $q) {
        if (empty($q['question_text']) || empty($q['answers'])) {
            continue;
        }

        $question = $exam->questions()->create([
            'question_text' => $q['question_text'],
            'question_type' => $q['question_type'] ?? 'single',
            'points_correct' => $q['points_correct'] ?? 1,
            'points_penalty' => $q['points_penalty'] ?? 0,
        ]);

        $totalPoints += (float) ($q['points_correct'] ?? 1);

        foreach ($q['answers'] as $a) {
            $question->answers()->create([
                'answer_text' => $a['answer_text'],
                'is_correct' => (bool) $a['is_correct'],
            ]);
        }
    }

    $exam->update(['total_points' => $totalPoints]);

    return redirect()->route('exams.show', $exam->id)
        ->with('success', 'Examen importé avec succès.');
}



public function downloadStudentsPresencePdf(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $students = Student::where('teacher_id', auth()->id())
        ->where('student_code', 'not like', 'TMP%')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    $html = view('exams.students-presence-pdf', compact('exam', 'students'))->render();

    $pdfPath = storage_path('app/liste_presence_' . $exam->id . '.pdf');

    Browsershot::html($html)
        ->setChromePath('/usr/bin/chromium')
        ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'])
        ->format('A4')
        ->margins(10, 10, 10, 10)
        ->showBackground()
        ->savePdf($pdfPath);

    return response()->download($pdfPath, 'liste_presence.pdf')->deleteFileAfterSend(true);
}

private function parseExamCsv(string $path): array
{
    $rows = [];

    if (($handle = fopen($path, 'r')) !== false) {
        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            return [];
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            if (count(array_filter($line)) === 0) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($handle);
    }

    if (empty($rows)) {
        return [];
    }

    $first = $rows[0];

    $questions = [];

    foreach ($rows as $row) {
        $answers = [];

        for ($i = 1; $i <= 6; $i++) {
            $answer = trim($row["answer_$i"] ?? '');

            if ($answer === '') {
                continue;
            }

            $answers[] = [
                'answer_text' => $answer,
                'is_correct' => (string)($row["correct_$i"] ?? '0') === '1',
            ];
        }

        $questions[] = [
            'question_text' => $row['question_text'] ?? '',
            'question_type' => $row['question_type'] ?? 'single',
            'points_correct' => $row['points_correct'] ?? 1,
            'points_penalty' => $row['points_penalty'] ?? 0,
            'answers' => $answers,
        ];
    }

    return [
        'title' => $first['title'] ?? 'Examen importé',
        'course_name' => $first['course_name'] ?? 'Cours',
        'description' => $first['description'] ?? null,
        'duration' => $first['duration'] ?? 60,
        'exam_language' => $first['exam_language'] ?? 'fr',
        'page_format' => $first['page_format'] ?? 'A4',
        'student_id_length' => $first['student_id_length'] ?? 6,
        'instructions' => $first['instructions'] ?? null,
        'questions' => $questions,
    ];
}



}
