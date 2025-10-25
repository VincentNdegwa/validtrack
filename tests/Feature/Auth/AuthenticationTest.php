<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
