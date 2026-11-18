<?php

use App\Http\Controllers\Admin\AnnonceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HoraireController;
use App\Http\Controllers\Admin\IntentionController;
use App\Http\Controllers\Admin\LiturgieController;
use App\Http\Controllers\Admin\ParoisseSettingsController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicIntentionController;
use App\Http\Controllers\PublicParoisseController;
use App\Http\Controllers\Super\ParoisseController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false, // Inscription désactivée
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/p/{paroisse}', [PublicParoisseController::class, 'show'])->name('paroisse.show');
Route::get('/p/{paroisse}/annonces', [PublicParoisseController::class, 'annonces'])->name('paroisse.annonces.index');
Route::get('/p/{paroisse}/annonces/{annonce}', [PublicParoisseController::class, 'annonce'])->name('paroisse.annonces.show');

Route::get('/p/{paroisse}/intentions/nouvelle', [PublicIntentionController::class, 'create'])->name('paroisse.intentions.nouvelle');
Route::post('/p/{paroisse}/intentions', [PublicIntentionController::class, 'store'])
    ->middleware('throttle:intentions.creation')
    ->name('paroisse.intentions.store');
Route::get('/p/{paroisse}/intentions/confirmation', [PublicIntentionController::class, 'confirmation'])->name('paroisse.intentions.confirmation');
Route::get('/p/{paroisse}/intentions/suivi', [PublicIntentionController::class, 'suiviForm'])->name('paroisse.intentions.suivi');
Route::post('/p/{paroisse}/intentions/suivi', [PublicIntentionController::class, 'suivi'])
    ->middleware('throttle:intentions.suivi')
    ->name('paroisse.intentions.suivi.rechercher');

Route::middleware(['auth', 'paroisse.isolation'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/paroisse', [ParoisseSettingsController::class, 'edit'])->name('paroisse.edit');
    Route::put('/paroisse', [ParoisseSettingsController::class, 'update'])->name('paroisse.update');

    Route::get('/equipe', [TeamController::class, 'index'])->name('equipe.index');
    Route::post('/equipe', [TeamController::class, 'store'])->name('equipe.store');
    Route::put('/equipe/{user}', [TeamController::class, 'update'])->name('equipe.update');
    Route::post('/equipe/{user}/reset-password', [TeamController::class, 'resetPassword'])->name('equipe.reset-password');
    Route::patch('/equipe/{user}/toggle-actif', [TeamController::class, 'toggleActif'])->name('equipe.toggle-actif');

    Route::resource('horaires', HoraireController::class)->except(['show', 'create', 'edit']);
    Route::resource('annonces', AnnonceController::class)->except(['show']);
    Route::patch('/annonces/{annonce}/archive', [AnnonceController::class, 'toggleArchive'])->name('annonces.toggle-archive');

    Route::get('/liturgie', [LiturgieController::class, 'index'])->name('liturgie.index');
    Route::post('/liturgie', [LiturgieController::class, 'store'])->name('liturgie.store');
    Route::delete('/liturgie/{couleurExceptionnelle}', [LiturgieController::class, 'destroy'])->name('liturgie.destroy');

    Route::get('/intentions', [IntentionController::class, 'index'])->name('intentions.index');
    Route::get('/intentions/feuille', [IntentionController::class, 'feuille'])->name('intentions.feuille');
    Route::get('/intentions/{intention}', [IntentionController::class, 'show'])->name('intentions.show');
    Route::patch('/intentions/{intention}/statut', [IntentionController::class, 'updateStatut'])->name('intentions.statut');
});
Route::middleware(['auth', 'can:manage-paroisses'])
    ->prefix('super')->name('super.')->group(function () {
        Route::get('/paroisses', [ParoisseController::class, 'index'])->name('paroisses.index');
        Route::get('/paroisses/create', [ParoisseController::class, 'create'])->name('paroisses.create');
        Route::post('/paroisses', [ParoisseController::class, 'store'])->name('paroisses.store');
    });

require __DIR__.'/auth.php';
