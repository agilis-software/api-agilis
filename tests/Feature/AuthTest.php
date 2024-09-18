<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('registers a user successfully', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'birth_date' => '1990-01-01',
    ]);

    $response->assertStatus(201)
        ->assertJson(function ($json) {
            $json->has('data')
                ->has('data.access_token')
                ->where('data.token_type', 'Bearer');
        });

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);
});

it('fails to register with invalid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('logs in a user successfully', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJson(
            fn ($json) => $json->has('data')
                ->has('data.access_token')
                ->where('data.token_type', 'Bearer')
        );
});

it('fails to log in with incorrect credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'incorrectpassword',
    ]);

    $response->assertStatus(422)
        ->assertJson(
            fn ($json) => $json->where('message', 'The provided credentials are incorrect')
        );
});

it('logs out a user successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/logout');

    $response->assertStatus(204);
});
