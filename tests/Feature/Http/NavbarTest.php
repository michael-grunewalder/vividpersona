<?php

use App\Models\User;

it('shows sign in and register links to guests', function () {
    $this->get('/')
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false)
        ->assertDontSee('Sign out');
});

it('shows the dashboard and sign out links to authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertSee(route('logout'), false)
        ->assertSee(route('dashboard'), false)
        ->assertDontSee(route('register'));
});
