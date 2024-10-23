<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectAssignRequest;
use App\Http\Requests\ProjectUnassignRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProjectUserController extends Controller
{
    private function findProject(string $organizationId, string $projectId)
    {
        $organization = Auth::user()->organizations()->findOrFail($organizationId);
        return $organization->projects()->findOrFail($projectId);
    }

    public function index(string $organizationId, string $projectId)
    {
        $project = $this->findProject($organizationId, $projectId);
        return UserResource::collection($project->users)->response()->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(string $organizationId, string $projectId, string $userId)
    {
        $project = $this->findProject($organizationId, $projectId);
        $user = $project->users()->findOrFail($userId);
        return (new UserResource($user))->response()->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function assign(ProjectAssignRequest $request, string $organizationId, string $projectId)
    {
        $project = $this->findProject($organizationId, $projectId);
        $users = $request->validated();
        $existingIds = $project->users()->pluck('id')->toArray();
        $newIds = array_diff($users['users'], $existingIds);

        if (!empty($newIds)) {
            $project->users()->attach($newIds);
            return UserResource::collection(User::find($newIds))->response()->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        }

        abort(400, 'User already assigned to project');
    }

    public function unassign(ProjectUnassignRequest $request, string $organizationId, string $projectId)
    {
        $project = $this->findProject($organizationId, $projectId);
        $users = $request->validated();
        $existingIds = $project->users()->pluck('id')->toArray();
        $removeIds = array_intersect($users['users'], $existingIds);

        if (!empty($removeIds)) {
            $project->users()->detach($removeIds);
            return response()->noContent();
        }

        abort(400, 'User not assigned to project');
    }
}
