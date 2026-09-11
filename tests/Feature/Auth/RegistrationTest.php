<?php

use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('registers a user, creates a personal team and assigns the admin role', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Max Mustermann',
        'email' => 'max@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'max@example.com')->firstOrFail();

    expect($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, EmailVerificationCode::class);

    $team = $user->ownedTeams()->first();

    expect($team)->not->toBeNull()
        ->and($team->name)->toBe('Personal')
        ->and($user->belongsToTeam($team))->toBeTrue()
        ->and($user->hasRoleInTeam($team, 'admin'))->toBeTrue();
});

it('requires a unique email address', function () {
    User::factory()->create(['email' => 'max@example.com']);

    $this->post('/register', [
        'name' => 'Max Mustermann',
        'email' => 'max@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    expect(User::query()->count())->toBe(1);
});

it('requires the password confirmation to match', function () {
    $this->post('/register', [
        'name' => 'Max Mustermann',
        'email' => 'max@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    expect(User::query()->count())->toBe(0);
});
