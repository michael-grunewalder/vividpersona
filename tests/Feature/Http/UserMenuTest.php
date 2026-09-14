<?php

use App\Models\User;

it('shows the user menu for authenticated users', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Jane Doe')
        ->assertSee('Profile')
        ->assertSee('Teams')
        ->assertSee('Logout');
});

it('does not expose the user menu to guests', function () {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});
