<?php

namespace App\Http\Controllers;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ScanAssociation;
use App\Models\ResultSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamScanController extends Controller
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


    public function scan(Exam $exam = null)
{
    $teacherId = auth()->id();
    
    // Si un examen est sélectionné, on récupère ses statistiques réelles
    $stats = [
        'total' => 0,
        'completed' => 0,
        'errors' => 0
    ];

    if ($exam) {
        // Sécurité : Vérifier que l'examen appartient bien au prof
        if ($exam->teacher_id !== $teacherId) { abort(403); }
        
        [$workDir, $wslPath] = $this->getExamPaths($exam->id);
        $scanPath = $workDir . DIRECTORY_SEPARATOR . 'scans';

        $pdfFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
        $jpgFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.jpg') ?: [];
        $pngFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.png') ?: [];

        $imagesCount = count($jpgFiles) + count($pngFiles);
        $pdfCount = count($pdfFiles);

        $stats['total'] = max($imagesCount, $pdfCount);

        $stats['completed'] = in_array($exam->scan_status, [
            'uploaded',
            'analysed',
            'associated',
            'graded'
        ]) ? $stats['total'] : 0;

        $stats['errors'] = $exam->scan_status === 'error' ? 1 : 0;
    }

    // Récupérer tous les examens du prof pour la liste de sélection
    $exams = Exam::where('teacher_id', $teacherId)->get();

    return view('exams.scan.index', compact('exam', 'exams', 'stats'));
}

public function uploadScans(Request $request, Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $request->validate([
        'scans' => 'required|array',
        'scans.*' => 'required|mimes:pdf,jpg,jpeg,png|max:51200',
    ]);

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $scanPath = $workDir . DIRECTORY_SEPARATOR . 'scans';

    $this->prepareAmcProject($workDir, $wslPath);

    // reset messages
    $exam->update([
        'scan_status' => 'pending',
        'last_amc_log' => null,
        'last_amc_error' => null,
    ]);

    // Nettoyage ancien contenu
    foreach (glob($scanPath . DIRECTORY_SEPARATOR . '*') as $f) {
        if (is_file($f)) {
            @unlink($f);
        }
    }


    $savedFiles = [];

    // Upload
    foreach ($request->file('scans') as $file) {
        if (!$file->isValid()) {
            return back()->with('error', 'Un des fichiers sélectionnés est invalide.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $fileName = uniqid('scan_') . '.' . $ext;
        $destination = $scanPath . DIRECTORY_SEPARATOR . $fileName;

        $file->move($scanPath, $fileName);
        
        clearstatcache(true, $destination);

        if (!file_exists($destination)) {
            $exam->update([
                'scan_status' => 'error',
                'last_amc_error' => "Fichier non sauvegardé : {$fileName}",
            ]);

            return back()->with('error', "Le fichier {$fileName} n'a pas été sauvegardé.");
        }

        if (filesize($destination) === 0) {
            $exam->update([
                'scan_status' => 'error',
                'last_amc_error' => "Fichier vide après upload : {$fileName}",
            ]);

            return back()->with('error', "Le fichier {$fileName} est vide après upload.");
        }

        @chmod($destination, 0777);
        $savedFiles[] = $destination;
    }

    // Rechercher les fichiers
    $pdfFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
    $jpgFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.jpg') ?: [];
    $pngFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.png') ?: [];

    Log::info('Scan upload - fichiers reçus', [
        'exam_id' => $exam->id,
        'saved_files' => $savedFiles,
        'pdf_files' => $pdfFiles,
        'jpg_files' => $jpgFiles,
        'png_files' => $pngFiles,
    ]);

    // Attendre que chaque PDF soit vraiment lisible côté WSL
    foreach ($pdfFiles as $pdf) {
        $pdfWsl = $this->toWslPath($pdf);

        $ready = false;
        $checksOutput = [];

        for ($i = 0; $i < 10; $i++) {
            $checksOutput = [];
            $checkCmd = "wsl bash -c " . escapeshellarg(
                "pdfinfo " . escapeshellarg($pdfWsl) . " >/dev/null 2>&1"
            );

            exec($checkCmd, $checksOutput, $checkRet);

            if ($checkRet === 0) {
                $ready = true;
                break;
            }

            usleep(300000); // 300 ms
            clearstatcache(true, $pdf);
        }

        if (!$ready) {
            $exam->update([
                'scan_status' => 'error',
                'last_amc_error' => 'Le PDF n’est pas encore lisible par WSL : ' . basename($pdf),
            ]);

            Log::error('PDF non lisible côté WSL après upload', [
                'exam_id' => $exam->id,
                'pdf_php' => $pdf,
                'pdf_wsl' => $pdfWsl,
            ]);

            return back()->with('error', 'Le fichier PDF n’est pas encore accessible pour le traitement. : ' . basename($pdf));
        }
    }

    // Conversion PDF -> JPG
    sleep(1);
    if (!empty($pdfFiles)) {
     foreach ($pdfFiles as $pdf) {
        clearstatcache(true, $pdf);

        if (!file_exists($pdf) || filesize($pdf) === 0) {
            $exam->update([
                'scan_status' => 'error',
                'last_amc_error' => 'PDF vide ou introuvable : ' . basename($pdf),
            ]);

            return back()->with('error', 'PDF vide ou introuvable : ' . basename($pdf));
        }

        $pdfWsl = $this->toWslPath($pdf);
        $baseName = pathinfo($pdf, PATHINFO_FILENAME);
        $baseNameEscaped = escapeshellarg($baseName);
        $pdfWslEscaped = escapeshellarg($pdfWsl);
        $scanDirWslEscaped = escapeshellarg($wslPath . '/scans');

        $output = [];

        $cmd = "cd {$scanDirWslEscaped} && " .
               "pdfinfo {$pdfWslEscaped} && " .
               "pdftoppm -r 300 -jpeg {$pdfWslEscaped} {$baseNameEscaped}";

        exec("wsl bash -c " . escapeshellarg($cmd) . " 2>&1", $output, $ret);

        Log::info('Conversion PDF -> JPG', [
            'exam_id' => $exam->id,
            'pdf_php' => $pdf,
            'pdf_wsl' => $pdfWsl,
            'command' => $cmd,
            'output' => $output,
            'return_code' => $ret,
        ]);

        if ($ret !== 0) {
            $exam->update([
                'scan_status' => 'error',
                'last_amc_error' => implode("\n", $output),
            ]);

            return back()->with('error', 'La conversion du PDF en images a échoué : ' . (implode(' | ', $output) ?: 'aucun détail retourné par WSL'));
        }
     }
    }

    // Recharger les images après conversion
    $jpgFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.jpg') ?: [];
    $pngFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.png') ?: [];
    $allImages = array_merge($jpgFiles, $pngFiles);

    Log::info('Images après conversion', [
       'exam_id' => $exam->id,
       'jpg_files' => $jpgFiles,
       'png_files' => $pngFiles,
    ]);

    if (empty($allImages)) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => 'Aucune image JPG/PNG trouvée après conversion.',
        ]);

        Log::error('Aucune image exploitable n’a été générée après le traitement', [
            'exam_id' => $exam->id,
            'scanPath' => $scanPath,
            'files' => glob($scanPath . DIRECTORY_SEPARATOR . '*'),
        ]);

        return back()->with('error', 'Upload fait, mais aucune image exploitable n’a été générée.');
    }


    $exam->update([
        'scan_status' => 'uploaded',
        'last_amc_error' => null,
        'last_amc_log' => 'Upload + conversion OK',
    ]);

    return back()->with('success',
        'Les scans ont été téléversés et traités avec succès. Vérifiez que les copies importées correspondent bien à cet examen avant de lancer l’analyse.'
    );
}

public function analyseScans(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $layoutFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'layout.sqlite';
    $reportFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';
    $scanPath   = $workDir . DIRECTORY_SEPARATOR . 'scans';

    if (!file_exists($layoutFile)) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => 'layout.sqlite introuvable',
        ]);

        return back()->with('error', 'AMC : layout.sqlite introuvable. Regénère d’abord l’examen.');
    }

    if (!file_exists($reportFile)) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => 'report.sqlite introuvable',
        ]);

        return back()->with('error', 'AMC : report.sqlite introuvable. Regénère d’abord l’examen.');
    }

    $jpgFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.jpg') ?: [];
    $pngFiles = glob($scanPath . DIRECTORY_SEPARATOR . '*.png') ?: [];
    $images   = array_merge($jpgFiles, $pngFiles);

    if (empty($images)) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => 'Aucune image trouvée dans scans/',
        ]);

        return back()->with('error', 'Aucune image trouvée pour l’analyse.');
    }

    $imagesWsl = array_map(function ($file) {
        return escapeshellarg($this->toWslPath($file));
    }, $images);

    $imagesArg = implode(' ', $imagesWsl);

    $cmd = "cd " . escapeshellarg($wslPath) . " && " .
           "[ -f ./data/layout.sqlite ] && " .
           "[ -f ./data/report.sqlite ] && " .
           "auto-multiple-choice analyse " .
           "--data ./data --projet . --multiple $imagesArg";

    $output = [];
    $fullCommand = "wsl bash -c " . escapeshellarg($cmd) . " 2>&1";

    exec($fullCommand, $output, $ret);

    Log::info('AMC analyse command', [
        'exam_id' => $exam->id,
        'command' => $fullCommand,
        'output' => $output,
        'images' => $images,
        'layout_exists' => file_exists($layoutFile),
        'report_exists' => file_exists($reportFile),
    ]);

    if ($ret !== 0) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => implode("\n", $output),
        ]);

        return back()->with('error', 'Une erreur est survenue lors de l’analyse des copies : ' . implode(' | ', $output));
    }

    $exam->update([
        'scan_status' => 'analysed',
        'last_amc_error' => null,
        'last_amc_log' => 'Analyse AMC OK',
    ]);

    return back()->with('success', 'L’analyse des copies a été effectuée avec succès.');
}


public function associateScans(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $studentsCsv = $workDir . DIRECTORY_SEPARATOR . 'students_list.csv';
    $metaFile    = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';

    $mode = 'anonymous';
    $studentCodes = [];

    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if (is_array($meta)) {
            $mode = $meta['mode'] ?? 'anonymous';
            $studentCodes = $meta['student_codes'] ?? [];
        }
    }

    // CAS 1 : anonyme sans étudiants choisis => pas d'association
    if ($mode === 'anonymous' && empty($studentCodes)) {
        $exam->update([
            'scan_status' => 'associated',
            'last_amc_error' => null,
            'last_amc_log' => 'Association ignorée (mode anonyme sans liste)',
        ]);

        return back()->with('success', 'Examen anonyme sans liste d’étudiants : aucune association nécessaire.');
    }

    // CAS 2 : anonyme avec étudiants choisis => forcer association manuelle
    if ($mode === 'anonymous' && !empty($studentCodes)) {
        $exam->update([
            'scan_status' => 'analysed',
            'last_amc_error' => 'Association manuelle requise',
            'last_amc_log' => 'Mode anonyme avec liste : association manuelle requise',
        ]);

        return redirect()
            ->route('exams.scan.manual', $exam->id)
            ->with('error', 'Association automatique échouée. Veuillez associer chaque copie à son étudiant dans la page d’association manuelle.');
    }

    // CAS 3 : nominatif => association automatique amc
    if ($mode === 'named') {

        [$ok, $message, $count] = $this->tryAmcAutoAssociation($exam);

        if ($ok) {
            $exam->update([
                'scan_status' => 'associated',
                'last_amc_error' => null,
                'last_amc_log' => "Association automatique AMC OK ($count copies)",
            ]);

            return back()->with('success', "Association automatique réussie avec AMC ($count copies).");
        }

        $exam->update([
            'scan_status' => 'analysed',
            'last_amc_error' => $message,
            'last_amc_log' => 'Association automatique AMC échouée, association manuelle requise',
        ]);

        return redirect()
            ->route('exams.scan.manual', $exam->id)
            ->with('error', 'AMC n’a pas pu associer correctement toutes les copies. Veuillez faire l’association manuelle.');
    }
}

public function gradeScans(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $reportFile  = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';
    $captureFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'capture.sqlite';
    $scoringFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'scoring.sqlite';
    $exportFile  = $workDir . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'notes.csv';
    $metaFile    = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';

    if (!file_exists($reportFile)) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => 'report.sqlite introuvable avant la notation',
        ]);

        return back()->with('error', 'report.sqlite introuvable.');
    }

    $mode = 'anonymous';

    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if (is_array($meta) && !empty($meta['mode'])) {
            $mode = $meta['mode'];
        }
    }

    // En mode nominatif seulement : vérifier report_student
    if ($mode === 'named') {
        try {
            $pdo = new \PDO('sqlite:' . $reportFile);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $count = (int) $pdo->query("SELECT COUNT(*) FROM report_student")->fetchColumn();

            if ($count === 0) {
                $exam->update([
                    'scan_status' => 'error',
                    'last_amc_error' => 'Aucune association dans report_student avant notation',
                ]);

                return back()->with('error', 'Aucune association trouvée avant la notation.');
            }
        } catch (\Throwable $e) {
            $exam->update([
                'scan_status' => 'error',
                'last_amc_error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lecture report.sqlite : ' . $e->getMessage());
        }
    }

    $cmd = "cd " . escapeshellarg($wslPath) . " && " .
           "auto-multiple-choice note --data ./data --projet .";

    $out = [];
    $ret = 0;

    exec("wsl bash -c " . escapeshellarg($cmd) . " 2>&1", $out, $ret);

    Log::info('AMC grading command', [
        'exam_id' => $exam->id,
        'mode' => $mode,
        'command' => $cmd,
        'output' => $out,
        'return_code' => $ret,
    ]);

    if ($ret !== 0) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => implode("\n", $out),
        ]);

        return back()->with('error', 'Erreur AMC notation : ' . implode(' | ', $out));
    }

    [$ok, $message] = $this->buildFinalCsv($exam);
    if (!$ok) {
        $metaFile = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';
        $mode = 'anonymous';
        $studentCodes = [];

        if (file_exists($metaFile)) {
           $meta = json_decode(file_get_contents($metaFile), true);
            if (is_array($meta)) {
                $mode = $meta['mode'] ?? 'anonymous';
                $studentCodes = $meta['student_codes'] ?? [];
            }
        }

        $exam->update([
            'scan_status' => 'analysed',
            'last_amc_error' => $message,
        ]);

        if ($mode === 'anonymous' && !empty($studentCodes)) {
            return redirect()
                ->route('exams.scan.manual', $exam->id)
                ->with('error', 'Association manuelle requise avant la notation.');
        }

        return back()->with('error', 'Notation AMC OK, mais génération du CSV impossible : ' . $message);
    }

    if (!$ok) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => $message,
        ]);

        return back()->with('error', 'Notation AMC OK, mais génération du CSV impossible : ' . $message);
    }

    if (!file_exists($exportFile)) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => 'notes.csv non généré par Laravel',
        ]);

        return back()->with('error', 'Le CSV final n’a pas été généré.');
    }

    [$saved, $saveMessage] = $this->storeResultSession($exam);

    if (!$saved) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => $saveMessage,
        ]);

        return back()->with('error', 'Notes calculées, mais impossible de sauvegarder l’historique : ' . $saveMessage);
    }

    $session = ResultSession::where('exam_id', $exam->id)
        ->whereHas('exam', function ($q) {
            $q->where('teacher_id', auth()->id());
        })
        ->latest()
        ->first();

    if ($session) {
        $this->generateCorrectedCopiesZip($exam, $session);
    }

    $exam->update([
        'scan_status' => 'graded',
        'last_amc_error' => null,
        'last_amc_log' => 'Notation AMC OK + CSV Laravel généré',
        'is_locked' => true,
    ]);

    return redirect()
        ->route('results.exam', $exam->id)
        ->with('success', 'La correction et le calcul des notes ont été effectués avec succès.');
}



public function exportResults(Exam $exam)
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $file = $workDir . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'notes.csv';

    if (!file_exists($file)) {
        return back()->with('error', 'Le fichier des résultats est introuvable.');
    }

    return response()->download($file, "notes_exam_{$exam->id}.csv");
}

private function getExamPaths(int $examId): array
{
    $exam = Exam::findOrFail($examId);

    $folderName = $this->examFolderName($exam);

    $workDir = storage_path("app/amc/{$folderName}");
    $workDir = rtrim($workDir, DIRECTORY_SEPARATOR);

    if (preg_match('/^[A-Za-z]:\\\\/', $workDir)) {
        $drive = strtolower($workDir[0]);
        $rest = str_replace('\\', '/', substr($workDir, 2));
        $wslPath = "/mnt/{$drive}{$rest}";
    } else {
        $wslPath = str_replace('\\', '/', $workDir);
    }

    return [$workDir, rtrim($wslPath, '/')];
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

    exec("wsl chmod -R 777 " . escapeshellarg($wslPath));
}

public function analysePage(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    return view('exams.scan.analyse', compact('exam'));
}

public function associationPage(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    return view('exams.scan.association', compact('exam'));
}

public function notationPage(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    return view('exams.scan.notation', compact('exam'));
}

public function resultatsPage(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $file = $workDir . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'notes.csv';
    $results = [];

    if (file_exists($file) && ($handle = fopen($file, 'r')) !== false) {
        $header = fgetcsv($handle, 1000, ';');

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            if (empty($row)) {
                continue;
            }

            $results[] = [
                'copie' => $row[0] ?? '',
                'code'  => $row[1] ?? '',
                'nom'   => $row[2] ?? '',
                'note'  => $row[3] ?? '',
            ];
        }

        fclose($handle);
    }

    return view('exams.scan.resultats', compact('exam', 'results'));
}

public function resetUpload(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $scanPath = $workDir . DIRECTORY_SEPARATOR . 'scans';
    $reportFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';

    foreach (glob($scanPath . DIRECTORY_SEPARATOR . '*') as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    // vider associations Laravel
    ScanAssociation::where('exam_id', $exam->id)->delete();

    // vider associations AMC
    if (file_exists($reportFile)) {
        try {
            $pdo = new \PDO('sqlite:' . $reportFile);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec("DELETE FROM report_student");
        } catch (\Throwable $e) {
            Log::error('Reset upload - impossible de vider report_student', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    $exam->update([
        'scan_status' => 'pending',
        'last_amc_error' => null,
        'last_amc_log' => 'Les fichiers de scan ont été réinitialisés',
    ]);

    return back()->with('success', 'Vous pouvez maintenant uploader de nouveaux scans.');
}


private function toWslPath(string $path): string
{
    $path = rtrim($path);

    if (str_starts_with($path, '/mnt/')) {
        return str_replace('\\', '/', $path);
    }

    if (preg_match('/^[A-Za-z]:\\\\/', $path) || preg_match('/^[A-Za-z]:\//', $path)) {
        $drive = strtolower($path[0]);
        $rest = str_replace('\\', '/', substr($path, 2));
        return "/mnt/{$drive}{$rest}";
    }

    return str_replace('\\', '/', $path);
}

public function resetAssociation(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $reportFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';

    try {
        if (file_exists($reportFile)) {
            $pdo = new \PDO('sqlite:' . $reportFile);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec("DELETE FROM report_student");
        }

        // vider les associations manuelles Laravel
        ScanAssociation::where('exam_id', $exam->id)->delete();

        $exam->update([
            'scan_status' => 'analysed',
            'last_amc_error' => null,
            'last_amc_log' => 'Association réinitialisée',
        ]);

        return back()->with('success', 'Association réinitialisée. Vous pouvez la relancer.');
    } catch (\Throwable $e) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => $e->getMessage(),
        ]);

        return back()->with('error', 'Impossible de réinitialiser l’association : ' . $e->getMessage());
    }
}


public function manualAssociationPage(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $scanPath = $workDir . DIRECTORY_SEPARATOR . 'scans';
    $captureFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'capture.sqlite';

    $images = collect();

    if (file_exists($captureFile)) {
        try {
            $pdo = new \PDO('sqlite:' . $captureFile);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $rows = $pdo->query("
                SELECT src, student, copy, page
                FROM capture_page
                ORDER BY student ASC, copy ASC, page ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $groups = collect($rows)->groupBy(function ($row) {
                return ($row['student'] ?? 0) . '_' . ($row['copy'] ?? 0);
            });

            foreach ($groups as $key => $pages) {
                $first = $pages->first();
                $fileName = basename($first['src']);

                $images->push([
                    'file_name' => $fileName,
                    'file_url' => route('exams.scan.image', [$exam->id, $fileName]),
                    'pages_count' => $pages->count(),
                    'copy_key' => $key,
                ]);
            }
        } catch (\Throwable $e) {
            $images = collect();
        }
    }

    if ($images->isEmpty()) {
        $images = collect(glob($scanPath . DIRECTORY_SEPARATOR . '*.jpg') ?: [])
            ->merge(glob($scanPath . DIRECTORY_SEPARATOR . '*.png') ?: [])
            ->map(function ($file) use ($exam) {
                $name = basename($file);

                return [
                    'file_name' => $name,
                    'file_url' => route('exams.scan.image', [$exam->id, $name]),
                    'pages_count' => 1,
                    'copy_key' => $name,
                ];
            })
            ->values();
    }

    $metaFile = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';

    $studentCodes = [];
    $temporaryStudents = [];

    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);

        if (is_array($meta)) {
            $studentCodes = $meta['student_codes'] ?? [];
            $temporaryStudents = $meta['temporary_students'] ?? [];
        }
    }

    $allCodes = collect($studentCodes)
        ->merge(collect($temporaryStudents)->pluck('student_code'))
        ->filter()
        ->unique()
        ->values();

    $students = Student::where('teacher_id', auth()->id())
        ->whereIn('student_code', $allCodes)
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    $associations = ScanAssociation::where('exam_id', $exam->id)
        ->get()
        ->keyBy('scan_file');

    return view('exams.scan.manual-association', compact('exam', 'images', 'students', 'associations'));
}

public function saveManualAssociation(Request $request, Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $request->validate([
        'associations' => 'required|array',
        'associations.*' => 'nullable|integer',
    ]);

    $currentScanFiles = array_keys($request->associations);

    // supprimer les anciennes associations qui ne sont plus dans le scan actuel
    ScanAssociation::where('exam_id', $exam->id)
        ->whereNotIn('scan_file', $currentScanFiles)
        ->delete();

    foreach ($request->associations as $scanFile => $studentId) {
        if (empty($studentId)) {
            ScanAssociation::where('exam_id', $exam->id)
                ->where('scan_file', $scanFile)
                ->delete();
            continue;
        }

        $student = Student::where('teacher_id', auth()->id())
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            continue;
        }

        ScanAssociation::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'scan_file' => $scanFile,
            ],
            [
                'student_id' => $studentId,
            ]
        );
    }

    $count = ScanAssociation::where('exam_id', $exam->id)->count();

    if ($count > 0) {
        $exam->update([
            'scan_status' => 'associated',
            'last_amc_error' => null,
            'last_amc_log' => "Association manuelle OK ($count)",
        ]);
    }
    [$ok, $message] = $this->syncManualAssociationsToAmc($exam);

    if (!$ok) {
        $exam->update([
            'scan_status' => 'error',
            'last_amc_error' => $message,
        ]);
        return redirect()
            ->route('exams.scan.manual', $exam->id)
            ->with('error', 'Association enregistrée dans le site, mais pas synchronisée vers AMC : ' . $message);
    }

    $exam->update([
        'scan_status' => 'associated',
        'last_amc_error' => null,
        'last_amc_log' => 'Association manuelle enregistrée et synchronisée vers AMC',
    ]);

    return redirect()
        ->route('exams.scan.index.exam', $exam->id)
        ->with('success', 'Association manuelle enregistrée avec succès.');
}


public function showScanImage(Exam $exam, string $file)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $path = $workDir . DIRECTORY_SEPARATOR . 'scans' . DIRECTORY_SEPARATOR . basename($file);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
}


private function syncManualAssociationsToAmc(Exam $exam): array
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);
    $reportFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';

    if (!file_exists($reportFile)) {
        return [false, 'report.sqlite introuvable'];
    }

    $associations = ScanAssociation::with('student')
        ->where('exam_id', $exam->id)
        ->orderBy('scan_file')
        ->get();

    if ($associations->isEmpty()) {
        return [false, 'Aucune association manuelle enregistrée'];
    }

    try {
        $pdo = new \PDO('sqlite:' . $reportFile);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // on vide les anciennes associations AMC
        $pdo->exec("DELETE FROM report_student");

        $stmt = $pdo->prepare("
            INSERT INTO report_student (type, file, student, copy, timestamp, mail_status, mail_timestamp, mail_message)
            VALUES (:type, :file, :student, :copy, :timestamp, 0, 0, '')
        ");

        foreach ($associations as $association) {
            $stmt->execute([
                ':type' => 3,
                ':file' => $association->scan_file,
                ':student' => $association->student->id,
                ':copy' => 0,
                ':timestamp' => time(),
            ]);
        }

        return [true, 'Synchronisation AMC OK'];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}

private function buildFinalCsv(Exam $exam): array
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $exportDir   = $workDir . DIRECTORY_SEPARATOR . 'exports';
    $exportFile  = $exportDir . DIRECTORY_SEPARATOR . 'notes.csv';
    $reportFile  = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';
    $scoringFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'scoring.sqlite';
    $metaFile    = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';

    $temporaryStudentsByCode = collect();

    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);

        if (is_array($meta) && !empty($meta['temporary_students'])) {
            $temporaryStudentsByCode = collect($meta['temporary_students'])->keyBy('student_code');
        }
    }

    if (!is_dir($exportDir)) {
        mkdir($exportDir, 0777, true);
    }

    if (!file_exists($scoringFile)) {
        return [false, 'scoring.sqlite introuvable'];
    }

    $mode = 'anonymous';
    if (file_exists($metaFile)) {
        $meta = json_decode(file_get_contents($metaFile), true);
        if (is_array($meta) && !empty($meta['mode'])) {
            $mode = $meta['mode'];
        }
    }

    try {
        $pdoScoring = new \PDO('sqlite:' . $scoringFile);
        $pdoScoring->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $notesRows = $pdoScoring->query("
            SELECT *
            FROM scoring_mark
            ORDER BY rowid ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($notesRows)) {
            return [false, 'Aucune note trouvée dans scoring_mark'];
        }

        $studentsById = Student::query()->get()->keyBy('id');

        $manualAssociations = ScanAssociation::with('student')
            ->where('exam_id', $exam->id)
            ->orderBy('scan_file')
            ->get()
            ->values();

        $handle = fopen($exportFile, 'w');
        if (!$handle) {
            return [false, 'Impossible de créer notes.csv'];
        }

        fputcsv($handle, ['Copie', 'Code', 'Nom', 'Note'], ';');

        // CAS 1 : association manuelle web disponible
        if ($manualAssociations->count() > 0) {
            foreach ($manualAssociations as $index => $assoc) {
                $noteRow = $notesRows[$index] ?? null;
                $note = $noteRow['total'] ?? '';

                $student = $assoc->student;

                fputcsv($handle, [
                    $index + 1,
                    $student->student_code ?? '',
                    trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? '')),
                    $note,
                ], ';');
            }

            fclose($handle);
            return [true, 'CSV final généré avec associations manuelles'];
        }

        // CAS 2 : nominatif AMC
        if ($mode === 'named') {
            if (!file_exists($reportFile)) {
                return [false, 'report.sqlite introuvable'];
            }

            $pdoReport = new \PDO('sqlite:' . $reportFile);
            $pdoReport->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $reportRows = $pdoReport->query("
                SELECT student, copy, file
                FROM report_student
                ORDER BY rowid ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($reportRows)) {
                return [false, 'report_student est vide'];
            }

            foreach ($reportRows as $index => $r) {
                $studentId = (int)($r['student'] ?? 0);
                $student = $studentsById[$studentId] ?? null;
                $noteRow = $notesRows[$index] ?? null;
                $note = $noteRow['total'] ?? '';

                fputcsv($handle, [
                    $index + 1,
                    $student->student_code ?? '',
                    $student ? trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? '')) : '',
                    $note,
                ], ';');
            }

            fclose($handle);
            return [true, 'CSV final généré en mode nominatif'];
        }

        // CAS 3 : anonyme pur sans association
        foreach ($notesRows as $index => $row) {
            fputcsv($handle, [
                $index + 1,
                '',
                'Copie anonyme',
                $row['total'] ?? '',
            ], ';');
        }

        fclose($handle);
        return [true, 'CSV final généré en mode anonyme'];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}

private function storeResultSession(Exam $exam): array
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $currentCsvFile = $workDir . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'notes.csv';
    $metaFile = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';
    $historyDir = $workDir . DIRECTORY_SEPARATOR . 'history';

    if (!file_exists($currentCsvFile)) {
        return [false, 'notes.csv introuvable'];
    }

    try {
        $mode = 'anonymous';
        $studentCodes = [];

        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true);

            if (is_array($meta)) {
                $mode = $meta['mode'] ?? 'anonymous';
                $studentCodes = $meta['student_codes'] ?? [];
            }
        }

        $sessionCount = \App\Models\ResultSession::where('exam_id', $exam->id)->count() + 1;

        if (!is_dir($historyDir)) {
            mkdir($historyDir, 0777, true);
        }

        $historyFileName = 'session_' . $sessionCount . '_' . now()->format('Ymd_His') . '.csv';
        $historyFullPath = $historyDir . DIRECTORY_SEPARATOR . $historyFileName;

        copy($currentCsvFile, $historyFullPath);

        $session = \App\Models\ResultSession::create([
            'exam_id' => $exam->id,
            'mode' => $mode,
            'label' => 'Correction ' . $sessionCount,
            'csv_path' => 'amc/' . $this->examFolderName($exam) . '/history/' . $historyFileName,
            'copies_count' => 0,
        ]);

        if (!empty($studentCodes)) {
            $students = Student::whereIn('student_code', $studentCodes)->get();

            foreach ($students as $student) {
                $session->sessionStudents()->create([
                    'student_id' => $student->id,
                ]);
            }
        }

        $rowsCount = 0;

        if (($handle = fopen($historyFullPath, 'r')) !== false) {
            fgetcsv($handle, 1000, ';');

            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                if (empty($row)) {
                    continue;
                }

                $session->rows()->create([
                    'copie' => $row[0] ?? '',
                    'code'  => $row[1] ?? '',
                    'nom'   => $row[2] ?? '',
                    'note'  => $row[3] ?? '',
                ]);

                $rowsCount++;
            }

            fclose($handle);
        }

        $session->update([
            'copies_count' => $rowsCount,
        ]);

        return [true, 'Historique sauvegardé'];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}

public function resultsIndex()
{
    $teacherId = auth()->id();

    $exams = Exam::where('teacher_id', $teacherId)
        ->withCount('resultSessions')
        ->latest()
        ->get();

    return view('results.index', compact('exams'));
}

public function resultsByExam(Exam $exam)
{
    if ($exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $sessions = $exam->resultSessions()
        ->latest()
        ->get();

    return view('results.exam', compact('exam', 'sessions'));
}

public function resultsSession(\App\Models\ResultSession $session)
{
    if ($session->exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $session->load('exam', 'rows', 'sessionStudents.student');

    return view('results.session', compact('session'));
}


public function downloadSessionCsv(\App\Models\ResultSession $session)
{
    if ($session->exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    $file = storage_path('app/' . $session->csv_path);

    if (!file_exists($file)) {
        return back()->with('error', 'Le fichier CSV de cette correction est introuvable.');
    }

    $examTitle = str_replace(' ', '_', $session->exam->title ?? 'exam');
    $label = str_replace(' ', '_', $session->label ?? ('session_' . $session->id));

    return response()->download(
        $file,
        "resultats_{$examTitle}_{$label}.csv"
    );
}

private function getStudentsListMap(string $csvPath): array
{
    $map = [];

    if (!file_exists($csvPath)) {
        return $map;
    }

    if (($handle = fopen($csvPath, 'r')) !== false) {
        fgetcsv($handle, 1000, ','); 

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $code = $row[0] ?? '';
            $name = $row[1] ?? '';
            $surname = $row[2] ?? '';

            if ($code !== '') {
                $map[$code] = [
                    'student_code' => $code,
                    'first_name' => $name,
                    'last_name' => $surname,
                ];
            }
        }

        fclose($handle);
    }

    return $map;
}


private function autoAssociateNamedFromMeta(Exam $exam): array
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $metaFile = $workDir . DIRECTORY_SEPARATOR . 'session_meta.json';
    $scanPath = $workDir . DIRECTORY_SEPARATOR . 'scans';

    if (!file_exists($metaFile)) {
        return [false, 'session_meta.json introuvable'];
    }

    $meta = json_decode(file_get_contents($metaFile), true);

    $codes = collect($meta['student_codes'] ?? [])
        ->merge(collect($meta['temporary_students'] ?? [])->pluck('student_code'))
        ->filter()
        ->values();

    if ($codes->isEmpty()) {
        return [false, 'Aucun étudiant nominatif trouvé'];
    }

    $students = Student::where('teacher_id', auth()->id())
        ->whereIn('student_code', $codes)
        ->get()
        ->keyBy('student_code');

    $images = collect(glob($scanPath . DIRECTORY_SEPARATOR . '*.jpg') ?: [])
        ->merge(glob($scanPath . DIRECTORY_SEPARATOR . '*.png') ?: [])
        ->sort()
        ->values();

    if ($images->isEmpty()) {
        return [false, 'Aucune image de scan trouvée'];
    }

    ScanAssociation::where('exam_id', $exam->id)->delete();

    foreach ($images as $index => $image) {
        $code = $codes[$index] ?? null;
        $student = $code ? ($students[$code] ?? null) : null;

        if (!$student) {
            continue;
        }

        ScanAssociation::create([
            'exam_id' => $exam->id,
            'scan_file' => basename($image),
            'student_id' => $student->id,
        ]);
    }

    return $this->syncManualAssociationsToAmc($exam);
}

private function tryAmcAutoAssociation(Exam $exam): array
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $reportFile  = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'report.sqlite';
    $captureFile = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'capture.sqlite';
    $layoutFile  = $workDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'layout.sqlite';

    if (!file_exists($reportFile)) {
        return [false, 'report.sqlite introuvable.', 0];
    }

    if (!file_exists($captureFile)) {
        return [false, 'capture.sqlite introuvable.', 0];
    }

    if (!file_exists($layoutFile)) {
        return [false, 'layout.sqlite introuvable.', 0];
    }

    try {
        $pdoLayout = new \PDO('sqlite:' . $layoutFile);
        $pdoLayout->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $layoutRows = $pdoLayout->query("
            SELECT student, id
            FROM layout_association
        ")->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($layoutRows)) {
            return [false, 'Aucune pré-association trouvée dans layout_association.', 0];
        }

        $codeByAmcStudent = [];

        foreach ($layoutRows as $row) {
            $codeByAmcStudent[(int) $row['student']] = trim($row['id']);
        }

        $pdoCapture = new \PDO('sqlite:' . $captureFile);
        $pdoCapture->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $captureRows = $pdoCapture->query("
            SELECT src, student, copy
            FROM capture_page
            ORDER BY student ASC, page ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($captureRows)) {
            return [false, 'Aucune page analysée dans capture_page.', 0];
        }

        $captureRows = collect($captureRows)
            ->groupBy(function ($row) {
                return ($row['student'] ?? 0) . '_' . ($row['copy'] ?? 0);
            })
            ->map(function ($pages) {
                return $pages->first();
            })
            ->values()
            ->all();


        $codes = array_values($codeByAmcStudent);

        $students = Student::where('teacher_id', auth()->id())
            ->whereIn('student_code', $codes)
            ->get()
            ->keyBy('student_code');

        $pdoReport = new \PDO('sqlite:' . $reportFile);
        $pdoReport->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdoReport->exec("DELETE FROM report_student");

        $stmt = $pdoReport->prepare("
            INSERT INTO report_student
            (type, file, student, copy, timestamp, mail_status, mail_timestamp, mail_message)
            VALUES
            (:type, :file, :student, :copy, :timestamp, 0, 0, '')
        ");

        $count = 0;

        foreach ($captureRows as $row) {
            $amcStudentNumber = (int) $row['student'];
            $studentCode = $codeByAmcStudent[$amcStudentNumber] ?? null;

            if (!$studentCode) {
                continue;
            }

            $student = $students[$studentCode] ?? null;

            if (!$student) {
                continue;
            }

            $stmt->execute([
                ':type' => 3,
                ':file' => basename($row['src']),
                ':student' => $student->id,
                ':copy' => (int) ($row['copy'] ?? 0),
                ':timestamp' => time(),
            ]);

            $count++;
        }

        if ($count === 0) {
            return [false, 'Impossible de créer les associations depuis AMC.', 0];
        }

        return [true, 'Association automatique AMC/Laravel OK', $count];

    } catch (\Throwable $e) {
        return [false, $e->getMessage(), 0];
    }
}

public function downloadCorrectedCopies(\App\Models\ResultSession $session)
{
    if ($session->exam->teacher_id !== auth()->id()) {
        abort(403);
    }

    if (!$session->corrected_zip_path) {
        return back()->with('error', 'Les copies corrigées ne sont pas disponibles pour cette correction.');
    }

    $file = storage_path('app/' . $session->corrected_zip_path);

    if (!file_exists($file)) {
        return back()->with('error', 'Le fichier des copies corrigées est introuvable.');
    }

    return response()->download($file);
}

private function generateCorrectedCopiesZip(Exam $exam, ResultSession $session): void
{
    [$workDir, $wslPath] = $this->getExamPaths($exam->id);

    $crDir = $workDir . DIRECTORY_SEPARATOR . 'cr';
    $pdfDir = $crDir . DIRECTORY_SEPARATOR . 'corrections' . DIRECTORY_SEPARATOR . 'pdf';
    $jpgDir = $crDir . DIRECTORY_SEPARATOR . 'corrections' . DIRECTORY_SEPARATOR . 'jpg';
    $historyDir = $workDir . DIRECTORY_SEPARATOR . 'history';

    if (!is_dir($historyDir)) {
        mkdir($historyDir, 0777, true);
    }

    foreach (glob($pdfDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
        if (is_file($file)) @unlink($file);
    }

    foreach (glob($jpgDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
        if (is_file($file)) @unlink($file);
    }

    $cmd = "cd " . escapeshellarg($wslPath) . " && " .
           "auto-multiple-choice annotate --data ./data --projet . --cr ./cr";

    exec("wsl bash -c " . escapeshellarg($cmd) . " 2>&1", $out, $ret);

    if ($ret !== 0) {
        \Log::error('AMC annotate error', [
            'exam_id' => $exam->id,
            'session_id' => $session->id,
            'output' => $out,
        ]);
        return;
    }

    $files = collect(glob($crDir . DIRECTORY_SEPARATOR . '**' . DIRECTORY_SEPARATOR . '*') ?: [])
        ->merge(glob($pdfDir . DIRECTORY_SEPARATOR . '*') ?: [])
        ->merge(glob($jpgDir . DIRECTORY_SEPARATOR . '*') ?: [])
        ->filter(fn ($file) => is_file($file) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['pdf', 'jpg', 'jpeg', 'png']))
        ->unique()
        ->values();

    if ($files->isEmpty()) {
        return;
    }

    $zipName = 'copies_corrigees_session_' . $session->id . '.zip';
    $zipFullPath = $historyDir . DIRECTORY_SEPARATOR . $zipName;

    $zip = new \ZipArchive();

    if ($zip->open($zipFullPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        return;
    }

    foreach ($files as $file) {
        $zip->addFile($file, basename($file));
    }

    $zip->close();

    $session->update([
        'corrected_zip_path' => 'amc/' . $this->examFolderName($exam) . '/history/' . $zipName,
    ]);
}

}
