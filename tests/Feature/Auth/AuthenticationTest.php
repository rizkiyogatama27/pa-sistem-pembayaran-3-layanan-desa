<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'user',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/user/dashboard');
});

test('admin can authenticate and is redirected to admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
        'role' => 'admin',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/admin/dashboard');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'role' => 'user',
    ]);

    $this->assertGuest();
});

test('admin account cannot login in user mode', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
        'role' => 'user',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
