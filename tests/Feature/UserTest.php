<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

it('retrieves the authenticated user profile successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/users/me');

    $response->assertStatus(200)
        ->assertJson(function ($json) use ($user) {
            $json->where('data.id', $user->id)
                ->where('data.name', $user->name)
                ->where('data.email', $user->email);
        });
});

it('fails to retrieve the profile when not authenticated', function () {
    $response = $this->getJson('/api/users/me');

    $response->assertStatus(401);
});

it('deletes the authenticated user profile successfully', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/users/me/delete', [
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(204);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('fails to delete the profile with incorrect password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/users/me/delete', [
        'password' => 'wrongpassword',
        'password_confirmation' => 'wrongpassword'
    ]);

    $response->assertStatus(422);
});

it('updates the authenticated user password successfully', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/users/me/password', [
        'password' => 'password123',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(204);
});

it('fails to update the password with incorrect current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/users/me/password', [
        'password' => 'wrongpassword',
        'new_password' => 'newpassword123',
        'new_password_confirmation' => 'newpassword123',
    ]);

    $response->assertStatus(422);
});

it('retrieves a list of users successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    User::factory()->count(4)->create();

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

it('retrieves a specific user successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/users/$user->id");

    $response->assertStatus(200)
        ->assertJson(function ($json) use ($user) {
            $json->where('data.id', $user->id)
                ->where('data.name', $user->name)
                ->where('data.birth_date', $user->birth_date);
        });
});

it('fails to retrieve a specific user when not authenticated', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/$user->id");

    $response->assertStatus(401);
});
