<?php

use App\Http\Resources\StatusResource;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Task CRUD tests (index, store, show, update, destroy)
it('should list tasks', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);
    $doingStatus = $project->statuses()->create(['name' => 'DOING']);

    $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $project->tasks()->create([
        'title' => 'Task 2',
        'description' => 'Description 2',
        'due_date' => now()->addDays(2),
        'status_id' => $doingStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/$organization->id/projects/$project->id/tasks");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

it('should store a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    Sanctum::actingAs($user);

    $dueDate = now()->addDays();
    $response = $this->postJson("/api/organizations/$organization->id/projects/$project->id/tasks", [
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => $dueDate->toISOString(),
        'status_id' => $todoStatus->id
    ]);

    $response->assertCreated();
    $response->assertJsonFragment([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => $dueDate->toISOString(),
        'status' => new StatusResource($todoStatus),
    ]);
});

it('should update a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $newDueDate = now()->addDays(2);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id", [
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => $newDueDate->toISOString(),
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => $newDueDate->toISOString(),
        'status' => new StatusResource($todoStatus),
    ]);
});

it('should delete a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id");

    $response->assertNoContent();
});

// task status test

it('should update a task status', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);
    $doingStatus = $project->statuses()->create(['name' => 'DOING']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/status", [
        'status_id' => $doingStatus->id,
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'status' => new StatusResource($doingStatus),
    ]);
});

it('should not list tasks when project not exists', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->getJson("/api/organizations/$organization->id/projects/1/tasks");

    $response->assertNotFound();
});

it('should not list tasks when organization not exists', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/organizations/1/projects/1/tasks');

    $response->assertNotFound();
});

it('should not store a task when project not exists', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/$organization->id/projects/1/tasks", [
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => 1,
    ]);

    $response->assertStatus(422);
});

it('should not store a task when organization not exists', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/organizations/1/projects/1/tasks', [
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
    ]);

    $response->assertStatus(422);
});

it('should not update a task when project not exists', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/1/tasks/1", [
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => now()->addDays(2),
        'status_id' => 1,
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseMissing('tasks', ['title' => 'Task 1 Updated']);
});

it('should not update a task when organization not exists', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/organizations/1/projects/1/tasks/1', [
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => now()->addDays(2),
    ]);

    $response->assertNotFound();
});

it('should not delete a task when project not exists', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/$organization->id/projects/1/tasks/1");

    $response->assertNotFound();
});

it('should not delete a task when organization not exists', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->deleteJson('/api/organizations/1/projects/1/tasks/1');

    $response->assertNotFound();
});

it('should not update a task status when project not exists', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/1/tasks/1/status", [
        'status_id' => 1,
    ]);

    $response->assertStatus(422);
});

it('should not update a task status when organization not exists', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/organizations/1/projects/1/tasks/1/status', [
        'status_id' => 1,
    ]);

    $response->assertStatus(422);
});

it('should not store a task when invalid form data is provided', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/organizations/$organization->id/projects/$project->id/tasks", [
        'title' => '',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('title');
});

it('should not update a task when invalid form data is provided', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id", [
        'title' => '',
        'description' => 'Description 1 Updated',
        'due_date' => now()->addDays(2),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('title');
});

it('should not update a task status when invalid form data is provided', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/status", [
        'status_id' => 100,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('status_id');
});

it('should not store a task when user is not authenticated', function () {
    $organization = Organization::factory()->create();

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $response = $this->postJson("/api/organizations/$organization->id/projects/$project->id/tasks", [
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
    ]);

    $response->assertUnauthorized();
});

it('should not update a task when user is not authenticated', function () {
    $organization = Organization::factory()->create();

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id", [
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => now()->addDays(2),
    ]);

    $response->assertUnauthorized();
});

it('should not delete a task when user is not authenticated', function () {
    $organization = Organization::factory()->create();

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $response = $this->deleteJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id");

    $response->assertUnauthorized();
});

it('should not update a task status when user is not authenticated', function () {
    $organization = Organization::factory()->create();

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/status", [
        'status_id' => $todoStatus->id,
    ]);

    $response->assertUnauthorized();
});

it('should not store a task when user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    Sanctum::actingAs($user);

    $dueDate = now()->addDays();
    $response = $this->postJson("/api/organizations/$organization->id/projects/$project->id/tasks", [
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => $dueDate->toISOString(),
    ]);

    $response->assertStatus(422);
});

it('should not update a task when user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id", [
        'title' => 'Task 1 Updated',
        'description' => 'Description 1 Updated',
        'due_date' => now()->addDays(2),
    ]);

    $response->assertStatus(422);
});

it('should not delete a task when user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $organization->users()->attach($user->id);

    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id");

    $response->assertStatus(422);
});

it('should assign a project user to a task', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = $organization->projects()->create([
        'name' => 'Project 1',
        'description' => 'Description 1',
    ]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $project->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/assignee", [
        'assignee_id' => $user->id,
    ]);

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'title',
            'description',
            'due_date',
            'status',
            'assignee',
        ],
    ]);
});

it('should not assign a project user to a task when user is not authenticated', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    $project = $organization->projects()->create([
        'name' => 'Project 1',
        'description' => 'Description 1',
    ]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $project->users()->attach($user->id);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/assignee", [
        'assignee_id' => $user->id,
    ]);

    $response->assertUnauthorized();
});

it('should not assign a project user to a task when project not exists', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $organization->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/1/tasks/1/assignee", [
        'assignee_id' => $user->id,
    ]);

    $response->assertNotFound();
});

it('should not assign a project user to a task when organization not exists', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/organizations/1/projects/1/tasks/1/assignee', [
        'assignee_id' => $user->id,
    ]);

    $response->assertNotFound();
});

it('should not assign a project user to a task when user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();

    $organization->users()->attach($user->id);

    $project = $organization->projects()->create([
        'name' => 'Project 1',
        'description' => 'Description 1',
    ]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    $project->users()->attach($user->id);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/assignee", [
        'assignee_id' => $user->id,
    ]);

    $response->assertStatus(422);
});

it('should not assign a project user to a task when user is not a project user', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $project = $organization->projects()->create([
        'name' => 'Project 1',
        'description' => 'Description 1',
    ]);

    $todoStatus = $project->statuses()->create(['name' => 'TO_DO']);

    $task = $project->tasks()->create([
        'title' => 'Task 1',
        'description' => 'Description 1',
        'due_date' => now()->addDays(),
        'status_id' => $todoStatus->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/$organization->id/projects/$project->id/tasks/$task->id/assignee", [
        'assignee_id' => $user->id,
    ]);

    $response->assertStatus(404);
});
