<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Question;
use App\Models\ResultSession;
use App\Models\ResultRow;

class AnalyticsController extends Controller
{
    public function index()
    {
        $teacherId = auth()->id();

        $palette = [
            '#9A0002', 
            '#111827', 
            '#D97706', 
            '#16A34A', 
            '#7C3AED', 
            '#0891B2', 
            '#DB2777', 
            '#475569', 
        ];

        $exams = Exam::where('teacher_id', $teacherId)
            ->withCount(['questions', 'resultSessions'])
            ->latest()
            ->get();

        $totalExams = $exams->count();

        $totalStudents = Student::where('teacher_id', $teacherId)
            ->where('student_code', 'not like', 'TMP%')
            ->count();

        $totalQuestions = Question::whereHas('exam', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->count();

        $totalSessions = ResultSession::whereHas('exam', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->count();

        $rows = ResultRow::whereHas('session.exam', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->with('session.exam')
            ->get();

        $percentNotes = $rows->map(function ($row) {
            $raw = (float) str_replace(',', '.', $row->note);

            $exam = $row->session->exam ?? null;
            $total = $exam ? ($exam->total_points ?: $exam->total_calculated_points ?: 20) : 20;

            return $total > 0 ? round(($raw / $total) * 100, 2) : 0;
        });

        $average = $percentNotes->count() ? round($percentNotes->avg(), 2) : 0;

        $successRate = $percentNotes->count()
            ? round(($percentNotes->filter(fn ($n) => $n >= 50)->count() / $percentNotes->count()) * 100, 1)
            : 0;

        $excellentRate = $percentNotes->count()
            ? round(($percentNotes->filter(fn ($n) => $n >= 75)->count() / $percentNotes->count()) * 100, 1)
            : 0;

        $weakRate = $percentNotes->count()
            ? round(($percentNotes->filter(fn ($n) => $n < 50)->count() / $percentNotes->count()) * 100, 1)
            : 0;

        $examDetails = [];
        $examPerformance = collect();

        foreach ($exams as $examIndex => $exam) {
            $examColor = $palette[$examIndex % count($palette)];

            $rowsExam = ResultRow::whereHas('session.exam', function ($q) use ($exam) {
                    $q->where('id', $exam->id);
                })
                ->with('session')
                ->get();

            $total = $exam->total_points ?: $exam->total_calculated_points ?: 20;

            $details = $rowsExam->values()->map(function ($row, $rowIndex) use ($total, $examColor, $palette) {
                $raw = (float) str_replace(',', '.', $row->note);
                $percent = $total > 0 ? round(($raw / $total) * 100, 2) : 0;

                $studentName = trim($row->nom ?? '');
                if ($studentName === '') {
                    $studentName = 'Copie ' . ($row->copie ?? ($rowIndex + 1));
                }

                $sessionLabel = $row->session->label ?? 'Correction';

                $mode = $row->session->mode ?? 'anonymous';

                $sessionMode = match ($mode) {
                    'named', 'nominative' => 'nominative',
                    'anonymous'           => 'anonyme',
                    default               => ucfirst($mode),
                };

                return [
                    'label' => $studentName . ' — ' . $sessionLabel . ' (' . $sessionMode . ')',
                    'student' => $studentName,
                    'session' => $sessionLabel,
                    'mode' => $sessionMode,
                    'note' => $percent,
                    'raw' => $raw,

                    'color' => $sessionMode === 'nominative'
                        ? '#def067' 
                        : '#17dffa',
                ];
            });

            $examNotes = $details->pluck('note');

            $examDetails[$exam->id] = [
                'title' => $exam->title,
                'mainColor' => $examColor,
                'labels' => $details->pluck('label')->values(),
                'notes' => $details->pluck('note')->values(),
                'rawNotes' => $details->pluck('raw')->values(),
                'colors' => $details->pluck('color')->values(),
            ];

            $examPerformance->push([
                'id' => $exam->id,
                'title' => $exam->title,
                'color' => $examColor,
                'average' => $examNotes->count() ? round($examNotes->avg(), 2) : 0,
                'success_rate' => $examNotes->count()
                    ? round(($examNotes->filter(fn ($n) => $n >= 50)->count() / $examNotes->count()) * 100, 1)
                    : 0,
                'excellent_rate' => $examNotes->count()
                    ? round(($examNotes->filter(fn ($n) => $n >= 75)->count() / $examNotes->count()) * 100, 1)
                    : 0,
                'weak_rate' => $examNotes->count()
                    ? round(($examNotes->filter(fn ($n) => $n < 50)->count() / $examNotes->count()) * 100, 1)
                    : 0,
                'copies' => $rowsExam->count(),
                'questions' => $exam->questions_count,
                'sessions' => $exam->result_sessions_count,
            ]);
        }

        $bestExams = $examPerformance
            ->filter(fn ($exam) => $exam['copies'] > 0)
            ->sortByDesc('average')
            ->take(5)
            ->values();

        $riskExams = $examPerformance
            ->filter(fn ($exam) => $exam['copies'] > 0)
            ->sortBy('average')
            ->take(5)
            ->values();

        $studentAttempts = $rows
            ->groupBy(function ($row) {
                $examTitle = $row->session->exam->title ?? 'Examen';
                $studentName = trim($row->nom ?? '');

                if ($studentName === '') {
                    $studentName = 'Copie ' . ($row->copie ?? '');
                }

                return $examTitle . '|' . $studentName;
            })
            ->map(function ($items, $key) {
                [$examTitle, $studentName] = explode('|', $key);

                return [
                    'name' => $studentName,
                    'exam' => $examTitle,
                    'label' => $studentName . ' — ' . $examTitle,
                    'attempts' => $items->count(),
                ];
            })
            ->filter(fn ($item) => $item['attempts'] > 1)
            ->sortByDesc('attempts')
            ->take(10)
            ->values();

        return view('analytics.index', compact(
            'totalExams',
            'totalStudents',
            'totalQuestions',
            'totalSessions',
            'average',
            'successRate',
            'excellentRate',
            'weakRate',
            'examPerformance',
            'examDetails',
            'bestExams',
            'riskExams',
            'studentAttempts'
        ));
    }
}