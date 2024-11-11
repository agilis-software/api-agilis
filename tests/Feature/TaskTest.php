<?php

use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Task CRUD tests (index, store, show, update, destroy)
it('should list tasks', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = Status::create(['name' => 'TO_DO']);
    $doingStatus = Status::create(['name' => 'DOING']);

    $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(1),
        'status_id' => $todoStatus->id
    ]);

    $project->tasks()->create([
        'title' => 'Task 2',
        'description' => 'Description 2',
        'due_date' => now()->addDays(2),
        'status_id' => $doingStatus->id
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/{$organization->id}/projects/{$project->id}/tasks");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

it('should store a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = Status::create(['name' => 'TO_DO']);

    Sanctum::actingAs($user);

    $dueDate = now()->addDays(1);
    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/tasks", [
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => $dueDate->toISOString()
    ]);

    $response->assertCreated();
    $response->assertJsonFragment([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => $dueDate->toISOString(),
        'status' => $todoStatus->name
    ]);
});

it('should update a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = Status::create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(1),
        'status_id' => $todoStatus->id
    ]);

    Sanctum::actingAs($user);

    $newDueDate = now()->addDays(2);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}/tasks/{$task->id}", [
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => $newDueDate->toISOString()
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => $newDueDate->toISOString(),
        'status' => $todoStatus->name
    ]);
});

it('should delete a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = Status::create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(1),
        'status_id' => $todoStatus->id
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/{$organization->id}/projects/{$project->id}/tasks/{$task->id}");

    $response->assertNoContent();
});
