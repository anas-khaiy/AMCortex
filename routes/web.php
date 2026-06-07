<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamScanController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\AIQuestionController;
use Illuminate\Support\Facades\App;

Route::get('/lang/{locale}', function ($locale) {
    if (!in_array($locale, ['fr', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    return back();
})->name('lang.switch');

Route::get('/', function () {
    return redirect()->route('login');
});

// --- Authentification ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/debug-lang', function () {
    return [
        'locale' => app()->getLocale(),
        'session_locale' => session('locale'),
        'bon_retour' => __('Bon retour'),
        'se_connecter' => __('Se connecter'),
    ];
});

// --- Réinitialisation de mot de passe ---
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
    ->name('password.request');

Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
    ->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.update');


// --- Réinitialisation de l'email ---
Route::get('/forgot-email', [AuthController::class, 'showForgotEmail'])
    ->name('forgot.email');

Route::post('/forgot-email', [AuthController::class, 'sendForgotEmail'])
    ->name('forgot.email.send');

// --- Espace Professeur ---
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // --- Analytics ---
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // --- Paramètres de compte ---
    Route::get('/settings', [AuthController::class, 'settings'])->name('settings');
    Route::put('/settings/profile', [AuthController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [AuthController::class, 'updatePassword'])->name('settings.password');

    /// --- Documentation ---
    Route::get('/documentation/pdf', [DocumentationController::class, 'downloadPdf'])
    ->name('documentation.pdf');

    // --- Gestion des examens ---
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');

    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])
    ->name('exams.destroy');

    // --- Importation d'examens via CSV --- 
    Route::get('/exams/import', [ExamController::class, 'importForm'])->name('exams.import.form');
    Route::post('/exams/import', [ExamController::class, 'import'])->name('exams.import');

    // --- AI Question Generator ---
    Route::get('/exams/{exam}/ai-local/questions', [AIQuestionController::class, 'create'])
    ->name('ai.local.questions');

    Route::post('/exams/{exam}/ai-local/questions/suggest', [AIQuestionController::class, 'suggest'])
    ->name('ai.local.questions.suggest');

    Route::post('/exams/{exam}/ai-local/questions/add', [AIQuestionController::class, 'add'])
    ->name('ai.local.questions.add');

    // --- Télécharger la liste de présence des étudiants en PDF ---
    Route::get('/exams/{exam}/students-presence-pdf', [ExamController::class, 'downloadStudentsPresencePdf'])
    ->name('exams.students.presence.pdf')
    ->middleware('auth');

    // --- Workflow Scan AMC ---
    Route::get('/exams/scan', [ExamScanController::class, 'scan'])->name('exams.scan.index');
    Route::get('/exams/scan/{exam}', [ExamScanController::class, 'scan'])->name('exams.scan.index.exam');

    Route::get('/exams/{exam}/scan/analyse', [ExamScanController::class, 'analysePage'])->name('exams.scan.analyse.page');
    Route::post('/exams/{exam}/scan/analyse', [ExamScanController::class, 'analyseScans'])->name('exams.scan.analyse');

    Route::get('/exams/{exam}/scan/association', [ExamScanController::class, 'associationPage'])->name('exams.scan.association.page');
    Route::post('/exams/{exam}/scan/association', [ExamScanController::class, 'associateScans'])->name('exams.scan.associate');

    Route::post('/exams/{exam}/scan/reset-association', [ExamScanController::class, 'resetAssociation'])
    ->name('exams.scan.reset-association');
    
    Route::get('/exams/{exam}/scan/manual-association', [ExamController::class, 'openAssociationGui'])
    ->name('exams.scan.manual');
    
    Route::get('/exams/{exam}/scan/manual-association', [ExamScanController::class, 'manualAssociationPage'])
    ->name('exams.scan.manual');

    Route::post('/exams/{exam}/scan/manual-association', [ExamScanController::class, 'saveManualAssociation'])
    ->name('exams.scan.manual.save');

    Route::get('/exams/{exam}/scan/image/{file}', [ExamScanController::class, 'showScanImage'])
    ->name('exams.scan.image');

    Route::get('/exams/{exam}/scan/notation', [ExamScanController::class, 'notationPage'])->name('exams.scan.notation.page');
    Route::post('/exams/{exam}/scan/notation', [ExamScanController::class, 'gradeScans'])->name('exams.scan.grade');

    Route::get('/exams/{exam}/scan/resultats', [ExamScanController::class, 'resultatsPage'])->name('exams.scan.resultats');
    Route::get('/exams/{exam}/scan/export', [ExamScanController::class, 'exportResults'])->name('exams.scan.export');

    Route::get('/results/session/{session}/corrected-copies', [ExamScanController::class, 'downloadCorrectedCopies'])
    ->name('results.session.corrected');

    Route::post('/exams/{exam}/scan/upload', [ExamScanController::class, 'uploadScans'])->name('exams.scan.upload');

    Route::post('/exams/{exam}/scan/reset-upload', [ExamScanController::class, 'resetUpload'])
    ->name('exams.scan.reset-upload');
    
    Route::prefix('exams/{exam}')->group(function () {
        Route::get('/', [ExamController::class, 'show'])->name('exams.show');
        Route::get('/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::put('/', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('/', [ExamController::class, 'destroy'])->name('exams.destroy');

        Route::get('/generate', [ExamController::class, 'generate'])->name('exams.generate');
        Route::post('/process-generation', [ExamController::class, 'processGeneration'])->name('exams.process-generation');

        Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');

        //Route::get('/results', [ExamController::class, 'resultats'])->name('exams.resultats');
        Route::get('/download-csv', [ExamController::class, 'downloadCsv'])->name('exams.downloadCsv');
    });

    Route::get('/download-installer', function () {
            return response()->download(base_path('install_amcortex.bat'));
        })->name('download.installer');

    

    // --- Banque de questions ---
    Route::get('/questions-bank', [QuestionController::class, 'globalIndex'])->name('questions.global');
    Route::resource('questions', QuestionController::class)->except(['create', 'store']);

    // --- Étudiants ---
    Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::resource('students', StudentController::class);

    // --- Résultats ---
    Route::get('/results', [ExamScanController::class, 'resultsIndex'])->name('results.index');
    Route::get('/results/exam/{exam}', [ExamScanController::class, 'resultsByExam'])->name('results.exam');
    Route::get('/results/session/{session}', [ExamScanController::class, 'resultsSession'])->name('results.session');
    Route::get('/results/session/{session}/download-csv', [ExamScanController::class, 'downloadSessionCsv'])
        ->name('results.session.download');
    
    // --- Importation de questions via CSV ---
    Route::post('/exams/{exam}/questions/import-csv', [QuestionController::class, 'importCsv'])
        ->name('questions.import.csv')
        ->middleware('auth');

    Route::post('/exams/{exam}/questions/import-text', [QuestionController::class, 'importText'])
        ->name('questions.import.text');

    // --- Admin ---
    Route::middleware(['admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [AdminController::class, 'dashboard'])
                ->name('dashboard');

            Route::get('/teachers', [AdminController::class, 'teachers'])
                ->name('teachers');

            Route::delete('/teachers/{user}', [AdminController::class, 'deleteTeacher'])
                ->name('teachers.delete');

    });


});




// --- Admin ---
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers');
        Route::get('/teachers/create', [AdminController::class, 'createTeacher'])->name('teachers.create');
        Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('teachers.store');
        Route::get('/teachers/{user}/edit', [AdminController::class, 'editTeacher'])->name('teachers.edit');
        Route::put('/teachers/{user}', [AdminController::class, 'updateTeacher'])->name('teachers.update');
        Route::delete('/teachers/{user}', [AdminController::class, 'deleteTeacher'])->name('teachers.delete');

        Route::get('/students', [AdminController::class, 'students'])->name('students');
        Route::get('/exams', [AdminController::class, 'exams'])->name('exams');

        Route::get('/statistics', [AdminController::class, 'statistics'])
            ->name('statistics');
});