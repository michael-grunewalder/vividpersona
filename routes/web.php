<?php

use App\Http\Controllers\Admin\ApiProviderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

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

Route::middleware(['auth', 'verified', 'super-admin'])
    ->prefix('backend')
    ->name('backend.')
    ->group(function () {
        Route::resource('api-provider', ApiProviderController::class);
        Route::resource('user', UserController::class);
    });

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified', 'team.context'])->name('dashboard');

Route::middleware(['auth', 'verified', 'team.context'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
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
