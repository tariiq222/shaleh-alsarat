<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

it('allows admin to log in with valid credentials', function () {
    $admin = adminUser();

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin');
});

it('rejects invalid credentials', function () {
    $admin = adminUser();

    $response = $this->from('/login')->post('/login', [
        'email' => $admin->email,
        'password' => 'wrong-password',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});