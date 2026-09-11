<?php

use App\Enums\Plan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects guests to the login page', function () {
    $this->get('/backend/user')
        ->assertRedirect(route('login'));
});

it('forbids non-super-admins from managing users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/backend/user')
        ->assertForbidden();
});

it('allows a super admin to list users', function () {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get('/backend/user')
        ->assertOk()
        ->assertSee('Users');
});

it('creates an auto-verified user with the given settings', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/user', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'plan' => 'pro',
            'max_teams' => 5,
            'is_super_admin' => 1,
        ])
        ->assertRedirect(route('backend.user.index'));

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->is_super_admin)->toBeTrue()
        ->and($user->meta->currentPlan)->toBe(Plan::Pro)
        ->and($user->meta->maxTeams)->toBe(5);
});

it('creates a regular free user by default', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/user', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'plan' => 'free',
            'max_teams' => 1,
        ])
        ->assertRedirect(route('backend.user.index'));

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->is_super_admin)->toBeFalse()
        ->and($user->meta->currentPlan)->toBe(Plan::Free);
});

it('rejects a duplicate email address', function () {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->create(['email' => 'jane@example.com']);

    $this->actingAs($admin)
        ->post('/backend/user', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'plan' => 'free',
            'max_teams' => 1,
        ])
        ->assertSessionHasErrors('email');
});

it('rejects an invalid plan', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->post('/backend/user', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'plan' => 'enterprise',
            'max_teams' => 1,
        ])
        ->assertSessionHasErrors('plan');
});

it('updates a user and their settings', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['meta' => ['current_plan' => 'free', 'max_teams' => 1]]);

    $this->actingAs($admin)
        ->put("/backend/user/{$user->getKey()}", [
            'name' => 'Jane Updated',
            'email' => $user->email,
            'password' => '',
            'plan' => 'premium',
            'max_teams' => 10,
        ])
        ->assertRedirect(route('backend.user.index'));

    $user->refresh();

    expect($user->name)->toBe('Jane Updated')
        ->and($user->meta->currentPlan)->toBe(Plan::Premium)
        ->and($user->meta->maxTeams)->toBe(10);
});

it('keeps the password when it is left blank', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['password' => 'original-password']);

    $this->actingAs($admin)
        ->put("/backend/user/{$user->getKey()}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'plan' => 'free',
            'max_teams' => 1,
        ])
        ->assertRedirect(route('backend.user.index'));

    expect(Hash::check('original-password', $user->fresh()->password))->toBeTrue();
});

it('updates the password when a new one is provided', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->put("/backend/user/{$user->getKey()}", [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'plan' => 'free',
            'max_teams' => 1,
        ])
        ->assertRedirect(route('backend.user.index'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('prevents lowering max teams below the owned team count', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create(['meta' => ['current_plan' => 'free', 'max_teams' => 5]]);
    Team::factory()->count(3)->create(['owner_id' => $user->getKey()]);

    $this->actingAs($admin)
        ->put("/backend/user/{$user->getKey()}", [
            'name' => $user->name,
            'email' => $user->email,
            'plan' => 'free',
            'max_teams' => 2,
        ])
        ->assertSessionHasErrors('max_teams');
});

it('prevents deleting a user who still owns teams', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();
    Team::factory()->create(['owner_id' => $user->getKey()]);

    $this->actingAs($admin)
        ->delete("/backend/user/{$user->getKey()}")
        ->assertSessionHasErrors('user');

    expect(User::query()->find($user->getKey()))->not->toBeNull();
});

it('deletes a user', function () {
    $admin = User::factory()->superAdmin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete("/backend/user/{$user->getKey()}")
        ->assertRedirect(route('backend.user.index'));

    expect(User::query()->find($user->getKey()))->toBeNull();
});

it('prevents deleting your own account', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete("/backend/user/{$admin->getKey()}")
        ->assertSessionHasErrors('user');

    expect(User::query()->find($admin->getKey()))->not->toBeNull();
});

it('prevents revoking your own super admin access', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->put("/backend/user/{$admin->getKey()}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'plan' => 'free',
            'max_teams' => 1,
            'is_super_admin' => 0,
        ])
        ->assertSessionHasErrors('is_super_admin');

    expect($admin->fresh()->is_super_admin)->toBeTrue();
});
