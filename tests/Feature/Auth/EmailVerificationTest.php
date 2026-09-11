<?php

use App\Models\User;
use App\Notifications\EmailVerificationCode;
use Illuminate\Support\Facades\Notification;

it('renders the confirmation code in the email', function () {
    $user = User::factory()->unverified()->create();
    $code = '123456';

    $mail = (new EmailVerificationCode($code))->toMail($user);

    expect($mail->introLines)->toContain($code)
        ->and($mail->subject)->toBe('Verify your email address');
});

it('redirects unverified users to the code entry page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('verification.notice'));
});

it('activates the account with a valid code', function () {
    $user = User::factory()->unverified()->create();
    $code = $user->issueEmailConfirmationCode();

    $this->actingAs($user)
        ->post('/email/verify', ['code' => $code])
        ->assertRedirect(route('dashboard'));

    $user->refresh();

    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->email_confirmation_code)->toBeNull()
        ->and($user->email_confirmation_code_expires_at)->toBeNull();
});

it('rejects an invalid confirmation code', function () {
    $user = User::factory()->unverified()->create();
    $user->issueEmailConfirmationCode();

    $this->actingAs($user)
        ->post('/email/verify', ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects an expired confirmation code', function () {
    $user = User::factory()->unverified()->create();
    $code = $user->issueEmailConfirmationCode();
    $user->forceFill(['email_confirmation_code_expires_at' => now()->subMinute()])->save();

    $this->actingAs($user)
        ->post('/email/verify', ['code' => $code])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('resends a new confirmation code', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post('/email/verification-notification')
        ->assertSessionHas('status');

    $user->refresh();

    Notification::assertSentTo($user, EmailVerificationCode::class);

    expect($user->email_confirmation_code)->not->toBeNull();
});

it('redirects verified users away from the code entry page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/email/verify')
        ->assertRedirect(route('dashboard'));
});
