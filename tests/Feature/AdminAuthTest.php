<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

it('redirects /admin/login to /login', function () {
    $response = $this->get('/admin/login');

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

it('ignores a stale public intended url and sends admin to dashboard', function () {
    $admin = adminUser();

    // Simulate a stale `url.intended` left over from a prior guest visit
    // to the public site (Laravel stores the intended path in session
    // before redirecting a guest to the login page).
    session(['url.intended' => '/']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin');
});

it('preserves an admin intended url after login', function () {
    $admin = adminUser();

    session(['url.intended' => '/admin/bookings']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin/bookings');
});

it('ignores a stale /administrator intended url', function () {
    $admin = adminUser();

    // `/administrator` shares the `/admin` prefix but is not part of the
    // admin area — a stale session value must not be honored.
    session(['url.intended' => '/administrator']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin');
});

it('ignores a stale /admin-public intended url', function () {
    $admin = adminUser();

    // Same trap as above — `/admin-public` starts with `/admin` but is not
    // an admin path; login must fall back to the dashboard.
    session(['url.intended' => '/admin-public']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin');
});

it('ignores a cross-host intended url', function () {
    $admin = adminUser();

    // An absolute URL pointing to a different host must never be honored
    // (open-redirect guard), even when the path looks admin-shaped.
    session(['url.intended' => 'https://evil.example.com/admin/users']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin');
});

it('preserves a same-host absolute admin intended url', function () {
    $admin = adminUser();

    // Match the test env's APP_URL (http://localhost:8000) so the stored
    // intended URL is treated as same-host by the controller.
    session(['url.intended' => 'http://localhost:8000/admin/bookings']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/admin/bookings');
});