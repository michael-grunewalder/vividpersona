<?php

use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Illuminate\Support\Facades\Notification;

it('logs in a verified user and redirects to the dashboard', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('redirects unverified users to the code entry page and sends a fresh code', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create(['password' => 'password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('verification.notice'));

    $this->assertAuthenticatedAs($user);

    Notification::assertSentTo($user, EmailVerificationCode::class);
});

it('redirects to the intended page after login', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->get('/teams')->assertRedirect(route('login'));

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/teams');
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs the user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
