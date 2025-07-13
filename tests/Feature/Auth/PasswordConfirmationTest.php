<?php

use App\Models\User;

test('confirm password screen can be rendered', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertStatus(200);
});

test('password can be confirmed', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('password is not confirmed with invalid password', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
});