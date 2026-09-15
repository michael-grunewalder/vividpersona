<?php

use App\Models\User;

it('shows sign in and register links to guests', function () {
    $this->get('/')
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false)
        ->assertDontSee('Sign out');
});

it('redirects authenticated users to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
