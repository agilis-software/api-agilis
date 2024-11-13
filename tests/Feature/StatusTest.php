<?php

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// status api resource tests (index, store, show, update, destroy)

it('should return a list of statuses', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $project->statuses()->create(['name' => 'TO_DO']);

    $response = $this->getJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses");

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
            ],
        ],
    ]);
});

it('should create a new status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses", [
        'name' => 'TO_DO',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
        ],
    ]);
});

it('should return a status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $status = $project->statuses()->create(['name' => 'TO_DO']);

    $response = $this->getJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}");

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
        ],
    ]);
});

it('should update a status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $status = $project->statuses()->create(['name' => 'TO_DO']);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}", [
        'name' => 'IN_PROGRESS',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
        ],
    ]);
});

it('should delete a status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $status = $project->statuses()->create(['name' => 'TO_DO']);

    $response = $this->deleteJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}");

    $response->assertNoContent();
});

// status api resource tests (index, store, show, update, destroy) (bad scenarios)

it('should not create a new status with missing name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

it('should not create a new status with invalid name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses", [
        'name' => 123,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

it('should not return a status from another project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);
    $anotherProject = Project::factory()->create(['organization_id' => $organization->id]);
    $anotherProject->users()->attach($user->id);

    $status = $anotherProject->statuses()->create(['name' => 'TO_DO']);

    $response = $this->getJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}");

    $response->assertNotFound();
});

it('should not update a status with invalid name', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $status = $project->statuses()->create(['name' => 'TO_DO']);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}", [
        'name' => 123,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

it('should not update a status from another project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);
    $anotherProject = Project::factory()->create(['organization_id' => $organization->id]);
    $anotherProject->users()->attach($user->id);

    $status = $anotherProject->statuses()->create(['name' => 'TO_DO']);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}", [
        'name' => 'IN_PROGRESS',
    ]);

    $response->assertNotFound();
});

it('should not delete a status from another project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);
    $anotherProject = Project::factory()->create(['organization_id' => $organization->id]);
    $anotherProject->users()->attach($user->id);

    $status = $anotherProject->statuses()->create(['name' => 'TO_DO']);

    $response = $this->deleteJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses/{$status->id}");

    $response->assertNotFound();
});

it('should not create a new status if user is not the organization owner', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create();
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses", [
        'name' => 'TO_DO',
    ]);

    $response->assertStatus(422);
});

it('should not create a new status if user is not a member of the organization', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/statuses", [
        'name' => 'TO_DO',
    ]);

    $response->assertNotFound();
});
