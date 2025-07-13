<?php

use App\Models\Company;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $company = Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});