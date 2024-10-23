<?php

use App\Http\Controllers\ProjectUserController;
use App\Http\Requests\ProjectAssignRequest;
use App\Http\Requests\ProjectUnassignRequest;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists all projects successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user);
    Project::factory(3)->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get("/api/organizations/{$organization->id}/projects");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('creates a project successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    Sanctum::actingAs($user);

    $response = $this->postJson("api/organizations/$organization->id/projects", [
        'name' => 'Project Name',
        'description' => 'Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id', 'name', 'description', 'start_date', 'finish_date',
            ],
        ]);
});

it('shows a project successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
            ],
        ]);
});

it('updates a project successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}", [
        'name' => 'Updated Project Name',
        'description' => 'Updated Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $project->id,
                'name' => 'Updated Project Name',
                'description' => 'Updated Project Description',
            ],
        ]);
});

it('deletes a project successfully', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->delete("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(204);
});

it('fails to list all projects when not authenticated', function () {
    $organization = Organization::factory()->create();
    Project::factory()->create(['organization_id' => $organization->id]);

    $response = $this->get("/api/organizations/{$organization->id}/projects");

    $response->assertStatus(401);
});

it('fails to list all projects when user is not in organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get("/api/organizations/{$organization->id}/projects");

    $response->assertStatus(404);
});

it('fails to list all projects when organization does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get('/api/organizations/invalid-id/projects');

    $response->assertStatus(404);
});

it('fails to create a project when not authenticated', function () {
    $organization = Organization::factory()->create();

    $response = $this->postJson("api/organizations/$organization->id/projects", [
        'name' => 'Project Name',
        'description' => 'Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(401);
});

it('fails to create a project when user is not in organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("api/organizations/$organization->id/projects", [
        'name' => 'Project Name',
        'description' => 'Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(422);
});

it('fails to show a project when not authenticated', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $response = $this->get("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(401);
});

it('fails to show a project when user is not in organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(404);
});

it('fails to show a project when organization does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get("/api/organizations/invalid-id/projects/{$project->id}");

    $response->assertStatus(404);
});

it('fails to show a project when project does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->get("/api/organizations/{$organization->id}/projects/invalid-id");

    $response->assertStatus(404);
});

it('fails to update a project when not authenticated', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}", [
        'name' => 'Updated Project Name',
        'description' => 'Updated Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(401);
});

it('fails to update a project when user is not in organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}", [
        'name' => 'Updated Project Name',
        'description' => 'Updated Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(422);
});

it('fails to update a project when organization does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/invalid-id/projects/{$project->id}", [
        'name' => 'Updated Project Name',
        'description' => 'Updated Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(422);
});

it('fails to update a project when project does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $organization->users()->attach($user->id);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/invalid-id", [
        'name' => 'Updated Project Name',
        'description' => 'Updated Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(404);
});

it('fails to update a project when user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs(User::factory()->create());

    $response = $this->putJson("/api/organizations/{$organization->id}/projects/{$project->id}", [
        'name' => 'Updated Project Name',
        'description' => 'Updated Project Description',
        'start_date' => '2022-01-01',
        'finish_date' => '2022-12-31',
    ]);

    $response->assertStatus(422);
});

it('fails to delete a project when not authenticated', function () {
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);

    $response = $this->delete("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(401);
});

it('fails to delete a project when user is not in organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->delete("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(422);
});

it('fails to delete a project when organization does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->delete("/api/organizations/invalid-id/projects/{$project->id}");

    $response->assertStatus(422);
});

it('fails to delete a project when project does not exist', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs($user);

    $response = $this->delete("/api/organizations/{$organization->id}/projects/invalid-id");

    $response->assertStatus(404);
});

it('fails to delete a project when user is not the owner of the organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $project = Project::factory()->create(['organization_id' => $organization->id]);
    Sanctum::actingAs(User::factory()->create());

    $response = $this->delete("/api/organizations/{$organization->id}/projects/{$project->id}");

    $response->assertStatus(422);
});

// projectUser
it('displays a listing of users in a project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $organization = $user->organizations()->create(['name' => 'Test Org', 'owner_id' => $user->id]);
    $project = $organization->projects()->create(['name' => 'Test Project']);
    $project->users()->attach($user->id);

    $response = $this->getJson("/api/organizations/{$organization->id}/projects/{$project->id}/users");

    $response->assertStatus(200)
        ->assertJson(UserResource::collection($project->users)->response()->getData(true));
});

it('displays a specified user in a project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $organization = $user->organizations()->create(['name' => 'Test Org', 'owner_id' => $user->id]);
    $project = $organization->projects()->create(['name' => 'Test Project']);
    $project->users()->attach($user->id);

    $response = $this->getJson("/api/organizations/{$organization->id}/projects/{$project->id}/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson((new UserResource($user))->response()->getData(true));
});

it('associates new users to a project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $organization = $user->organizations()->create(['name' => 'Test Org', 'owner_id' => $user->id]);
    $project = $organization->projects()->create(['name' => 'Test Project']);
    $newUser = User::factory()->create();

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/assign", [
        'users' => [$newUser->id],
    ]);

    $response->assertStatus(200);
    $this->assertTrue($project->users()->where('id', $newUser->id)->exists());
});

it('does not associate already associated users to a project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $organization = $user->organizations()->create(['name' => 'Test Org', 'owner_id' => $user->id]);
    $project = $organization->projects()->create(['name' => 'Test Project']);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/assign", [
        'users' => [$user->id],
    ]);

    $response->assertStatus(400);
    $this->assertCount(1, $project->users);
});

it('removes users from a project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $member = User::factory()->create();

    $organization = $user->organizations()->create(['name' => 'Test Org', 'owner_id' => $user->id]);
    $organization->users()->attach($member->id);
    $project = $organization->projects()->create(['name' => 'Test Project']);
    $project->users()->attach($user->id);
    $project->users()->attach($member->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/unassign", [
        'users' => [$member->id],
    ]);

    $response->assertStatus(204);
    $this->assertFalse($project->users()->where('id', $member->id)->exists());
});

it('does not remove users not associated to a project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $organization = $user->organizations()->create(['name' => 'Test Org', 'owner_id' => $user->id]);
    $project = $organization->projects()->create(['name' => 'Test Project']);
    $project->users()->attach($user->id);

    $response = $this->postJson("/api/organizations/{$organization->id}/projects/{$project->id}/unassign", [
        'users' => [User::factory()->create()->id],
    ]);

    $response->assertStatus(400);
    $this->assertCount(1, $project->users);
});
