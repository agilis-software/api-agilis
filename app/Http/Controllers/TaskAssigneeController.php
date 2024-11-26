<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskAssignRequest;
use App\Http\Resources\TaskResource;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskAssigneeController extends Controller
{
    public function update(TaskAssignRequest $request, int $organizationId, int $projectId, int $taskId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->findOrFail($organizationId);

        Validator::make(['organization_id' => $organizationId], [
            'organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule],
        ])->validate();

        $project = $organization->projects()->findOrFail($projectId);
        $task = $project->tasks()->findOrFail($taskId);

        $assignee = $project->users()->findOrFail($request->assignee_id);

        $task->assignee()
            ->associate($assignee);

        $task->save();

        return (new TaskResource($task))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
