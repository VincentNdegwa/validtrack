<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $response = $this
        ->actingAs($user)
        ->from('/settings/password')
        ->put('/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/password');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $company = \App\Models\Company::factory()->create([
        'owner_id' => null,
    ]);

    $user = User::factory()
        ->forCompany($company)
        ->create();

    $company->owner_id = $user->id;
    $company->save();
    $response = $this
        ->actingAs($user)
        ->from('/settings/password')
        ->put('/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/settings/password');
});