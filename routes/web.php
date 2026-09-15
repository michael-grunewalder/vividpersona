<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\PersonaMediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('landing'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->middleware('throttle:verification')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');
});

Route::post('/locale', [LocaleController::class, 'update'])->middleware('throttle:6,1')->name('locale.update');

// Signed, short-lived links for private persona media — consumed by external
// APIs as image references, so no session/auth is required.
Route::get('/personas/{persona}/media/{path}', [PersonaMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('personas.media');

Route::middleware(['auth', 'verified', 'super-admin'])
    ->prefix('backend')
    ->name('backend.')
    ->group(function () {
        Route::resource('user', UserController::class);
    });

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified', 'team.context'])->name('dashboard');

Route::middleware(['auth', 'verified', 'team.context'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections/default-llm', [ConnectionController::class, 'updateDefaultLlm'])->name('connections.default-llm');
    Route::post('/connections/{service}', [ConnectionController::class, 'store'])->name('connections.store');
    Route::delete('/connections/{service}', [ConnectionController::class, 'disconnect'])->name('connections.disconnect');

    Route::get('/personas', [PersonaController::class, 'index'])->name('personas.index');
    Route::get('/personas/create', [PersonaController::class, 'create'])->name('personas.create');
    Route::post('/personas', [PersonaController::class, 'store'])->name('personas.store');
    Route::get('/personas/{persona}', [PersonaController::class, 'show'])->name('personas.show');
    Route::get('/personas/{persona}/status', [PersonaController::class, 'status'])->name('personas.status');
    Route::post('/personas/{persona}/sets', [PersonaController::class, 'generateSet'])->name('personas.generate-set');
    Route::post('/personas/{persona}/choose', [PersonaController::class, 'choose'])->name('personas.choose');
    Route::delete('/personas/{persona}', [PersonaController::class, 'destroy'])->name('personas.destroy');
});

Route::middleware(['auth', 'verified', 'team.context'])->group(function () {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::patch('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');

    Route::post('/teams/{team}/invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');
    Route::post('/invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('teams.invitations.accept');
    Route::post('/invitations/{invitation}/decline', [TeamInvitationController::class, 'decline'])->name('teams.invitations.decline');

    Route::patch('/teams/{team}/members/{member}', [TeamMemberController::class, 'update'])->name('teams.members.update');
    Route::delete('/teams/{team}/members/{member}', [TeamMemberController::class, 'destroy'])->name('teams.members.destroy');
});
