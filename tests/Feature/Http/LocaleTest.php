<?php

it('switches the locale and renders translated text', function () {
    $this->get('/register')->assertSee('Create your account');

    $this->post('/locale', ['locale' => 'de'])->assertRedirect();

    $this->get('/register')->assertSee('Konto erstellen')->assertDontSee('Create your account');
});

it('rejects an unsupported locale', function () {
    $this->post('/locale', ['locale' => 'fr'])
        ->assertSessionHasErrors('locale');
});

it('shows a german validation message when the locale is german', function () {
    $this->withSession(['locale' => 'de'])
        ->post('/login', [])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('E-Mail-Adresse');
});
