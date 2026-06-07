<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIQuestionController extends Controller
{
    public function create(Exam $exam)
    {
        if ($exam->teacher_id !== auth()->id()) {
            abort(403);
        }

        return view('ai.local-questions', [
            'exam' => $exam,
            'suggestions' => [],
            'topic' => '',
        ]);
    }

    public function suggest(Request $request, Exam $exam)
    {
        if ($exam->teacher_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'topic' => 'required|string|max:500',
        ]);

        $dataset = storage_path('app/ai/question_bank.csv');
        $script = storage_path('app/ai/semantic_search.py');

        $language = $exam->exam_language === 'ar' ? 'Arabic' : 'French';

        $outputFile = storage_path('app/ai/results_' . $exam->id . '.json');

        $command = 'python ' . escapeshellarg($script) . ' ' .
            escapeshellarg($dataset) . ' ' .
            escapeshellarg($request->topic) . ' ' .
            escapeshellarg($language) . ' ' .
            escapeshellarg($outputFile);

        
        set_time_limit(30000); 
        exec($command, $output, $status);

        if ($status !== 0) {
            return back()->with('error', implode("\n", $output));
        }

        if (!file_exists($outputFile)) {
            return back()->with('error', 'Le fichier résultat IA n’a pas été créé.');
        }

        $json = file_get_contents($outputFile);

        $suggestions = json_decode($json, true);

        if (!is_array($suggestions)) {
            return back()->with('error', 'Réponse IA invalide : ' . $json);
        }

        session(['ai_suggestions_' . $exam->id => $suggestions]);

        return view('ai.local-questions', [
            'exam' => $exam,
            'suggestions' => $suggestions,
            'topic' => $request->topic,
        ]);
    }

    public function add(Request $request, Exam $exam)
    {
        if ($exam->teacher_id !== auth()->id()) {
            abort(403);
        }

        if ($exam->is_locked) {
            return back()->with('error', 'Impossible d’ajouter une question à un examen verrouillé.');
        }

        $request->validate([
            'index' => 'required|integer',
        ]);

        $suggestions = session('ai_suggestions_' . $exam->id, []);
        $item = $suggestions[$request->index] ?? null;

        if (!$item) {
            return back()->with('error', 'Suggestion introuvable.');
        }

        DB::transaction(function () use ($exam, $item) {
            $question = Question::create([
                'exam_id' => $exam->id,
                'question_text' => $item['question_text'],
                'question_type' => $item['question_type'] ?? 'single',
                'points_correct' => $item['points_correct'] ?? 1,
                'points_penalty' => $item['points_penalty'] ?? 0,
                'shuffle_answers' => 0,
                'explanation' => null,
            ]);

            foreach ($item['answers'] as $answer) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answer['answer_text'],
                    'is_correct' => !empty($answer['is_correct']) ? 1 : 0,
                ]);
            }

            $exam->update([
                'total_points' => $exam->questions()->sum('points_correct'),
            ]);
        });

        return redirect()
            ->route('questions.create', $exam->id)
            ->with('success', 'Question IA ajoutée avec succès.');
    }
}