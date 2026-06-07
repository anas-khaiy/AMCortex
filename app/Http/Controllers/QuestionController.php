<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function create($exam_id)
    {
        $exam = Exam::with(['questions.answers'])->findOrFail($exam_id);
        if ($exam->teacher_id !== auth()->id()) {
            abort(403);
        }
        return view('questions.create', compact('exam'));
    }

    public function store(Request $request, $exam_id)
    {
        $exam = Exam::findOrFail($exam_id);

        if ($exam->is_locked) {
            return back()->with('error', 'Impossible d’ajouter des questions après génération des résultats.');
        }

        if ($request->action === 'finish' && empty($request->question_text)) {
            return redirect()->route('exams.index')
                ->with('success', 'Saisie terminée.');
        }


        // 1. Validation de base
        $request->validate([
            'question_text' => 'required',
            'question_type' => 'required',
            'points_correct' => 'required|numeric',
            'points_penalty' => 'required|numeric', 
            'answers' => 'required|array|min:2', // Au moins 2 choix possibles
        ]);

        // 2. Validation Logique pour AMC
        $correctAnswersCount = 0;
        if ($request->has('correct_answers')) {
            $correctAnswersCount = count($request->correct_answers);
        }

        $isMultiple = in_array($request->question_type, ['multiple', 'qcm', 'checkbox']);

        if (!$isMultiple && $correctAnswersCount !== 1) {
            return redirect()->back()->withInput()->with('error', 'Erreur : Une question à choix unique doit avoir exactement UNE bonne réponse.');
        }

        if ($isMultiple && $correctAnswersCount < 1) {
            return redirect()->back()->withInput()->with('error', 'Erreur : Un QCM doit avoir au moins une bonne réponse cochée.');
        }

        // 3. Création sécurisée (Transaction)
        DB::transaction(function () use ($request, $exam_id, $correctAnswersCount) {
            $question = Question::create([
                'exam_id' => $exam_id,
                'question_text' => $request->question_text,
                'question_type' => $request->question_type,
                'points_correct' => $request->points_correct,
                'points_penalty' => $request->points_penalty,
                'shuffle_answers' => $request->shuffle_answers ? 1 : 0,
                'explanation' => $request->explanation
            ]);

            foreach ($request->answers as $index => $answer_text) {
                if (!empty($answer_text)) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answer_text,
                        'is_correct' => isset($request->correct_answers[$index])
                    ]);
                }
            }
            
            $this->updateExamTotalPoints($exam_id);
        });

        if ($request->action == "finish") {
            return redirect()->route('exams.index');
        }

        return redirect()->back()->with('success', 'Question ajoutée avec succès.');
    }

    public function update(Request $request, Question $question)
    {
        if ($question->exam->is_locked) {
            return back()->with('error', 'Impossible de modifier une question après génération des résultats.');
        }

        $correctAnswersCount = $request->has('correct_answers') ? count($request->correct_answers) : 0;
        $isMultiple = in_array($request->question_type, ['multiple', 'qcm', 'checkbox']);

        if (!$isMultiple && $correctAnswersCount !== 1) {
            return redirect()->back()->with('error', 'Choix unique : 1 seule réponse correcte requise.');
        }

        DB::transaction(function () use ($request, $question) {
            $question->update([
                'question_text' => $request->question_text,
                'question_type' => $request->question_type,
                'points_correct' => $request->points_correct,
                'points_penalty' => $request->points_penalty,
                'explanation' => $request->explanation
            ]);

            // On rafraîchit les réponses
            $question->answers()->delete();
            foreach ($request->answers as $index => $answer_text) {
                if (!empty($answer_text)) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answer_text,
                        'is_correct' => isset($request->correct_answers[$index])
                    ]);
                }
            }
            
            $this->updateExamTotalPoints($question->exam_id);
        });

        return redirect()->route('questions.global')
        ->with('success', 'La question a été modifiée avec succès.');
    }

    public function destroy(Question $question)
    {
        if ($question->exam->is_locked) {
            return back()->with('error', 'Impossible de supprimer une question après génération des résultats.');
        }

        $exam_id = $question->exam_id;

        if ($question->exam->teacher_id !== auth()->id()) {
            abort(403);
        }

        $question->delete();
        $this->updateExamTotalPoints($exam_id);

        return back()->with('success', 'Question supprimée.');
    }

    public function edit(Question $question)
{
    // Charger la question avec ses réponses et l'examen associé
    $question->load('answers', 'exam');
    $exam = $question->exam;

    return view('questions.edit', compact('question', 'exam'));
}

    private function updateExamTotalPoints($exam_id)
    {
        $exam = Exam::findOrFail($exam_id);
        $exam->update([
            'total_points' => $exam->questions()->sum('points_correct')
        ]);
    }

    public function globalIndex(Request $request)
{
    $search = trim($request->search ?? '');
    $examId = $request->exam_id;

    $questions = Question::whereHas('exam', function ($query) use ($examId) {

        $query->where('teacher_id', auth()->id());

        if (!empty($examId)) {
            $query->where('id', $examId);
        }

    })
    ->with(['exam', 'answers'])

    ->when($search, function ($query) use ($search) {

        $query->where(function ($q) use ($search) {

            $q->where('question_text', 'like', "%{$search}%")

              ->orWhereHas('answers', function ($a) use ($search) {
                  $a->where('answer_text', 'like', "%{$search}%");
              })

              ->orWhereHas('exam', function ($e) use ($search) {
                  $e->where('title', 'like', "%{$search}%");
              });

        });

    })

    ->latest()
    ->paginate(6)
    ->withQueryString();

    return view('questions.global', compact(
        'questions',
        'search',
        'examId'
    ));
}


    public function importCsv(Request $request, $exam_id)
{
    $exam = Exam::findOrFail($exam_id);

    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $request->validate([
        'questions_file' => 'required|file|mimes:csv,txt|max:2048',
    ]);

    $file = $request->file('questions_file');

    if (($handle = fopen($file->getRealPath(), 'r')) === false) {
        return back()->with('error', 'Impossible de lire le fichier CSV.');
    }

    $delimiter = ',';
    $firstLine = fgets($handle);

    if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
        $delimiter = ';';
    }

    rewind($handle);

    $header = fgetcsv($handle, 0, $delimiter);

    

    if (!$header) {
        fclose($handle);
        return back()->with('error', 'Le fichier CSV est vide.');
    }

    $header = array_map(fn ($h) => trim((string) $h), $header);

    $requiredColumns = [
        'question_text',
        'question_type',
        'points_correct',
        'points_penalty',
        'answer_1',
        'correct_1',
        'answer_2',
        'correct_2',
    ];

    foreach ($requiredColumns as $col) {
        if (!in_array($col, $header)) {
            fclose($handle);
            return back()->with('error', "Colonne obligatoire manquante : {$col}");
        }
    }

    $imported = 0;
    $errors = [];

    DB::transaction(function () use ($handle, $header, $exam_id,&$delimiter, &$imported, &$errors) {
        $lineNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach ($header as $index => $column) {
                $data[$column] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }

            $questionText = $data['question_text'] ?? '';
            $questionType = $data['question_type'] ?? 'single';
            $pointsCorrect = $data['points_correct'] ?? 1;
            $pointsPenalty = $data['points_penalty'] ?? 0;
            $explanation = $data['explanation'] ?? null;

            $answers = [];
            $correctIndexes = [];

            for ($i = 1; $i <= 4; $i++) {
                $answerText = trim((string)($data["answer_{$i}"] ?? ''));
                $isCorrect = (string)($data["correct_{$i}"] ?? '0') === '1';

                if ($answerText !== '') {
                    $answers[] = [
                        'answer_text' => $answerText,
                        'is_correct' => $isCorrect,
                    ];

                    if ($isCorrect) {
                        $correctIndexes[] = $i;
                    }
                }
            }

            if ($questionText === '') {
                $errors[] = "Ligne {$lineNumber} : question vide.";
                continue;
            }

            if (!in_array($questionType, ['single', 'multiple', 'boolean'])) {
                $errors[] = "Ligne {$lineNumber} : type invalide ({$questionType}).";
                continue;
            }

            if (count($answers) < 2) {
                $errors[] = "Ligne {$lineNumber} : au moins 2 réponses sont requises.";
                continue;
            }

            if (in_array($questionType, ['single', 'boolean']) && count($correctIndexes) !== 1) {
                $errors[] = "Ligne {$lineNumber} : une question {$questionType} doit avoir exactement 1 bonne réponse.";
                continue;
            }

            if ($questionType === 'multiple' && count($correctIndexes) < 1) {
                $errors[] = "Ligne {$lineNumber} : une question multiple doit avoir au moins 1 bonne réponse.";
                continue;
            }

            $question = Question::create([
                'exam_id' => $exam_id,
                'question_text' => $questionText,
                'question_type' => $questionType,
                'points_correct' => (float)$pointsCorrect,
                'points_penalty' => (float)$pointsPenalty,
                'shuffle_answers' => 0,
                'explanation' => $explanation,
            ]);

            foreach ($answers as $answer) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answer['answer_text'],
                    'is_correct' => $answer['is_correct'],
                ]);
            }

            $imported++;
        }

        fclose($handle);

        $this->updateExamTotalPoints($exam_id);
    });

    if ($imported === 0) {
        return back()->with('error', 'Aucune question importée. ' . implode(' | ', $errors));
    }

    $message = "{$imported} question(s) importée(s) avec succès.";

    if (!empty($errors)) {
        $message .= ' Quelques lignes ont été ignorées : ' . implode(' | ', array_slice($errors, 0, 3));
    }

    return back()->with('success', $message);
}

public function importText(Request $request, $exam_id)
{
    $request->validate([
        'questions_text' => 'required|string'
    ]);

    $content = $request->input('questions_text');

    $blocks = preg_split('/---/', $content);

    foreach ($blocks as $block) {

        if (trim($block) === '') continue;

        $lines = array_map('trim', explode("\n", trim($block)));

        $questionText = '';
        $type = 'single';
        $points = 1;
        $penalty = 0;
        $answers = [];

        foreach ($lines as $line) {

            if (str_starts_with($line, 'question:')) {
                $questionText = trim(str_replace('question:', '', $line));
            }

            elseif (str_starts_with($line, 'type:')) {
                $type = trim(str_replace('type:', '', $line));
            }

            elseif (str_starts_with($line, 'points:')) {
                $points = (float) str_replace('points:', '', $line);
            }

            elseif (str_starts_with($line, 'penalty:')) {
                $penalty = (float) str_replace('penalty:', '', $line);
            }

            elseif (str_contains($line, '|')) {
                [$text, $correct] = explode('|', $line);

                $answers[] = [
                    'answer_text' => trim($text),
                    'is_correct' => trim($correct) == '1'
                ];
            }
        }

        if ($questionText && count($answers) >= 2) {

            $question = \App\Models\Question::create([
                'exam_id' => $exam_id,
                'question_text' => $questionText,
                'question_type' => $type,
                'points_correct' => $points,
                'points_penalty' => $penalty,
            ]);

            foreach ($answers as $a) {
                \App\Models\Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $a['answer_text'],
                    'is_correct' => $a['is_correct'],
                ]);
            }
        }
    }

    return back()->with('success', 'Questions importées avec succès !');
}

}