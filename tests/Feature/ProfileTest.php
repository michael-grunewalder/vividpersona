<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

it('redirects guests to the login page', function () {
    $this->get('/profile')
        ->assertRedirect(route('login'));
});

it('shows the profile form', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('Jane Doe');
});

it('updates the name and email', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@example.com');
});

it('rejects an email already used by another user', function () {
    $user = User::factory()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('changes the password with the correct current password', function () {
    $user = User::factory()->create(['password' => 'current-pass']);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'current-pass',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('profile.edit'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('rejects a wrong current password', function () {
    $user = User::factory()->create(['password' => 'current-pass']);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-pass',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('current-pass', $user->fresh()->password))->toBeTrue();
});

it('requires the current password when changing the password', function () {
    $user = User::factory()->create(['password' => 'current-pass']);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('current-pass', $user->fresh()->password))->toBeTrue();
});

it('uploads an avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();

    Storage::disk('public')->assertExists($user->avatar_path);
});

it('removes an avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create(['avatar_path' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'fake');

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'remove_avatar' => 1,
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->avatar_path)->toBeNull();

    Storage::disk('public')->assertMissing('avatars/old.jpg');
});

it('uses the uploaded avatar URL when set', function () {
    Storage::fake('public');

    $user = User::factory()->create(['avatar_path' => 'avatars/x.jpg']);

    expect($user->avatarUrl())->toBe(Storage::disk('public')->url('avatars/x.jpg'));
});

it('falls back to gravatar when no avatar is uploaded', function () {
    $user = User::factory()->create(['email' => 'TEST@Example.com', 'avatar_path' => null]);

    expect($user->avatarUrl())->toBe('https://www.gravatar.com/avatar/'.md5('test@example.com').'?s=96&d=404');
});
