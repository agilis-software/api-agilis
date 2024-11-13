<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusStoreRequest;
use App\Http\Requests\StatusUpdateRequest;
use App\Http\Resources\StatusResource;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(int $organizationId, int $projectId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->findOrFail($organizationId);
        $project = $organization->projects()->findOrFail($projectId);

        return StatusResource::collection($project->statuses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StatusStoreRequest $request, int $organizationId, int $projectId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->findOrFail($organizationId);

        Validator::make(
            ['organization_id' => $organizationId],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $project = $organization->projects()->findOrFail($projectId);

        $status = $project->statuses()->create($request->validated());

        return new StatusResource($status);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $organizationId, int $projectId, int $statusId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->findOrFail($organizationId);
        $project = $organization->projects()->findOrFail($projectId);
        $status = $project->statuses()->findOrFail($statusId);

        return new StatusResource($status);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StatusUpdateRequest $request, int $organizationId, int $projectId, int $statusId)
    {
        $user = Auth::user();

        Validator::make(
            ['organization_id' => $organizationId],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $organization = $user->organizations()->findOrFail($organizationId);
        $project = $organization->projects()->findOrFail($projectId);
        $status = $project->statuses()->findOrFail($statusId);

        $status->update($request->validated());

        return new StatusResource($status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $organizationId, int $projectId, int $statusId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->findOrFail($organizationId);

        Validator::make(
            ['organization_id' => $organizationId],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $project = $organization->projects()->findOrFail($projectId);
        $status = $project->statuses()->findOrFail($statusId);

        $status->delete();

        return response()->noContent();
    }
}
