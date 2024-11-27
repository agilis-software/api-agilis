<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

// Avatar Tests
it('sets the organization avatar successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);
    Sanctum::actingAs($user);

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(200);
});

it('fails to set the organization avatar with invalid data', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => 'not-an-image',
    ]);

    $response->assertStatus(422);
});

it('removes the organization avatar successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/{$organization->id}/avatar");

    $response->assertStatus(200);
});

it('fails to set the organization avatar when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->post("/api/organizations/{$organization->id}/avatar", [
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
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
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertStatus(422);
});

// Organization Tests
it('retrieves a list of organizations successfully', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $organization = Organization::factory()->create();
    $organization2 = Organization::factory()->create();

    $organization->users()->attach($user->id);
    $organization2->users()->attach($user->id);

    $response = $this->getJson('/api/organizations');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
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

it('deletes an organization successfully', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/delete", [
        'password' => 'password123',
        'password_confirmation' => 'password123',
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
        'password' => 'password123',
    ]);
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/delete", [
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422);
});

it('fails to delete an organization with invalid data', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/delete", [
        'password' => 'password123',
        'password_confirmation' => 'invalid-password',
    ]);

    $response->assertStatus(422);
});

// Invite Tests
it('adds a user to an organization successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);
    $userToAdd = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/invite", [
        'email' => $userToAdd->email,
    ]);

    $response->assertStatus(200);
});

it('fails to add a user to an organization when not authenticated', function () {
    $organization = Organization::factory()->create();
    $userToAdd = User::factory()->create();

    $response = $this->postJson("/api/organizations/{$organization->id}/invite", [
        'email' => $userToAdd->email,
    ]);

    $response->assertStatus(401);
});

it('fails to add a user to an organization when the user is not the owner', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $userToAdd = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/invite", [
        'email' => $userToAdd->email,
    ]);

    $response->assertStatus(422);
});

it('fails to add a user to an organization with invalid data', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/invite", [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422);
});

it('fails to add a user to an organization with an invalid email', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/invite", [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422);
});

it('fails to add a user to an organization with an email that is already a member', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);
    $userToAdd = User::factory()->create();
    $organization->users()->attach($userToAdd->id);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/invite", [
        'email' => $userToAdd->email,
    ]);

    $response->assertStatus(422);
});

// Kick Tests
it('kicks a user from an organization successfully', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $owner->id]);
    $userToKick = User::factory()->create();
    $organization->users()->attach($userToKick->id);
    Sanctum::actingAs($owner);

    $response = $this->postJson("/api/organizations/{$organization->id}/users/{$userToKick->id}/kick");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $userToKick->id,
    ]);
});

it('fails to kick a user from an organization when the user is not a member', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $owner->id]);
    $userToKick = User::factory()->create();
    Sanctum::actingAs($owner);

    $response = $this->postJson("/api/organizations/{$organization->id}/users/{$userToKick->id}/kick");

    $response->assertStatus(404);
});

it('fails to kick a user from an organization when not authenticated', function () {
    $organization = Organization::factory()->create();
    $userToKick = User::factory()->create();
    $organization->users()->attach($userToKick->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/users/{$userToKick->id}/kick");

    $response->assertStatus(401);
});

it('fails to kick a user from an organization when the user is not the owner', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $owner->id]);
    $user = User::factory()->create();
    $userToKick = User::factory()->create();
    $organization->users()->attach([$user->id, $userToKick->id]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/users/{$userToKick->id}/kick");

    $response->assertStatus(422);
});

// Leave Tests
it('leaves an organization successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->users()->attach($user->id);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/leave");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);
});

it('fails to leave an organization when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->postJson("/api/organizations/{$organization->id}/leave");

    $response->assertStatus(401);
});

it('fails to leave an organization when the user is the owner', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/leave");

    $response->assertStatus(422);
});

it('fails to leave an organization when the user is not a member', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/{$organization->id}/leave");

    $response->assertStatus(404);
});

// Organization User Tests
it('lists all users of an organization', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $organization->users()->attach([$user2->id, $user3->id]);
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/{$organization->id}/users");

    $response->assertStatus(200);

    $response->assertJsonCount(3, 'data');
});

it('get a specific user from an organization', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);

    $user2 = User::factory()->create();
    $organization->users()->attach($user2->id);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/{$organization->id}/users/{$user2->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure(['data' => ['id', 'name', 'birth_date', 'avatar_url', 'is_owner']]);
});

it('fails to lists all users from an organization when is not authenticated', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);

    $user2 = User::factory()->create();
    $organization->users()->attach($user2->id);

    $response = $this->getJson("/api/organizations/{$organization->id}/users");
    $response->assertStatus(401);
});

it('fails to lists all users from an organization when organization not exists', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);

    $user2 = User::factory()->create();
    $organization->users()->attach($user2->id);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/organizations/0/users');
    $response->assertStatus(404);
});

it('fails to get a specific user from organization when is not authenticated', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);

    $user2 = User::factory()->create();
    $organization->users()->attach($user2->id);

    $response = $this->getJson("/api/organizations/{$organization->id}/users/{$user2->id}");
    $response->assertStatus(401);
});

it('fails to get a specific user from organization when organization not exists', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);

    $user2 = User::factory()->create();
    $organization->users()->attach($user2->id);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/0/users/{$user2->id}");
    $response->assertStatus(404);
});

it('fails to get a specific user from organization when user not exists', function () {
    $user = User::factory()->create();

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/{$organization->id}/users/0");
    $response->assertStatus(404);
});
