<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\ProjectResource;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(string $organizationId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->find($organizationId);

        abort_unless($organization, 404, 'Not found');

        $projects = $organization->projects;

        return ProjectResource::collection($projects)
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function store(ProjectStoreRequest $request, string $organizationId)
    {
        Validator::make(
            ['organization_id' => $organizationId],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $user = Auth::user();
        $organization = $user->ownOrganizations()->find($organizationId);
        $data = $request->validated();

        $project = $organization->projects()
            ->create($data);

        $project->users()->attach($user);

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(string $organizationId, string $projectId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->find($organizationId);

        abort_unless($organization, 404, 'Not found');

        $project = $organization->projects()->find($projectId);

        abort_unless($project, 404, 'Not found');

        return (new ProjectResource($project))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(ProjectUpdateRequest $request, string $organizationId, string $projectId)
    {
        $project = $this->getProject($organizationId, $projectId);

        $data = $request->validated();

        $project->update($data);

        return (new ProjectResource($project))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function destroy(string $organizationId, string $projectId)
    {
        $project = $this->getProject($organizationId, $projectId);
        $project->delete();

        return response()->noContent();
    }

    private function getProject(string $organizationId, string $projectId)
    {
        Validator::make(
            ['organization_id' => $organizationId],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $user = Auth::user();
        $organization = $user->ownOrganizations()->find($organizationId);

        abort_unless($organization, 404, 'Not found');

        $project = $organization->projects()->find($projectId);

        abort_unless($project, 404, 'Not found');

        return $project;
    }
}
