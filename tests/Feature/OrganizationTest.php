<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

it('sets the organization avatar successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);
    Sanctum::actingAs($user);

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => UploadedFile::fake()->image('avatar.jpg')
    ]);

    $response->assertStatus(200);
});

it('fails to set the organization avatar with invalid data', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => 'not-an-image'
    ]);

    $response->assertStatus(422);
});

it('removes the organization avatar successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/{$organization->id}/avatar");

    $response->assertStatus(200);
});

it('retrieves a list of organizations successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    Organization::factory()->count(5)->create();

    $response = $this->getJson('/api/organizations');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data');
});

it('retrieves a specific organization successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/{$organization->id}");

    $response->assertStatus(200)
        ->assertJson(function ($json) use ($organization) {
            $json->where('data.id', $organization->id)
                ->where('data.name', $organization->name);
        });
});

it('fails to retrieve a specific organization when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->getJson("/api/organizations/{$organization->id}");

    $response->assertStatus(401);
});

it('fails to set the organization avatar when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => UploadedFile::fake()->image('avatar.jpg')
    ]);

    $response->assertStatus(401);
});

it('fails to remove the organization avatar when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->deleteJson("/api/organizations/{$organization->id}/avatar");

    $response->assertStatus(401);
});

it('fails to remove the organization avatar when the user is not the owner', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/{$organization->id}/avatar");

    $response->assertStatus(422);
});

it('fails to set the organization avatar when the user is not the owner', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => UploadedFile::fake()->image('avatar.jpg')
    ]);

    $response->assertStatus(422);
});

it('deletes an organization successfully', function () {
    $user = User::factory()->create([
        'password' => 'password123'
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/delete", [
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(204);
});

it('fails to delete an organization when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->postJson("/api/organizations/{$organization->id}/delete");

    $response->assertStatus(401);
});

it('fails to delete an organization when the user is not the owner', function () {
    $user = User::factory()->create([
        'password' => 'password123'
    ]);
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/delete", [
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422);
});
