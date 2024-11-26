<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStatusRequest;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Status;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(int $organizationId, int $projectId)
    {
        $user = Auth::user();

        $organization = $user->organizations()->find($organizationId);
        abort_unless($organization, 404, 'Not found');

        $project = $organization->projects()->find($projectId);
        abort_unless($project, 404, 'Not found');

        $tasks = $project->tasks;

        return TaskResource::collection($tasks)
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function store(TaskStoreRequest $request, int $organizationId, int $projectId)
    {
        $user = Auth::user();

        $organization = $user->organizations()->find($organizationId);
        abort_unless($organization, 404, 'Not found');

        Validator::make(['organization_id' => $organizationId], [
            'organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule],
        ])->validate();

        $project = $organization->projects()->find($projectId);
        abort_unless($project, 404, 'Not found');

        $status = $project->statuses()->findOrFail($request->status_id);

        $task = $project->tasks()->create(array_merge($request->validated(), [
            'status_id' => $status->id,
        ]));

        return (new TaskResource($task))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(int $organizationId, int $projectId, int $taskId)
    {
        $task = $this->getTask($organizationId, $projectId, $taskId);

        return (new TaskResource($task))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(TaskUpdateRequest $request, int $organizationId, int $projectId, int $taskId)
    {
        $task = $this->getTask($organizationId, $projectId, $taskId);

        Validator::make(['organization_id' => $organizationId], [
            'organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule],
        ])->validate();

        $task->update($request->validated());

        return (new TaskResource($task))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function destroy(int $organizationId, int $projectId, int $taskId)
    {
        $task = $this->getTask($organizationId, $projectId, $taskId);

        Validator::make(['organization_id' => $organizationId], [
            'organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule],
        ])->validate();

        $task->delete();

        return response()->noContent();
    }

    public function updateStatus(TaskStatusRequest $request, int $organizationId, int $projectId, int $taskId)
    {
        $task = $this->getTask($organizationId, $projectId, $taskId);

        $project = $task->project;

        $status = $project->statuses()->findOrFail($request->status_id);

        abort_unless($status, 404, 'Not found');

        $task->update(['status_id' => $status->id]);

        return (new TaskResource($task))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return mixed
     */
    private function getTask(int $organizationId, int $projectId, int $taskId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->find($organizationId);

        abort_unless($organization, 404, 'Not found');

        $project = $organization->projects()->find($projectId);

        abort_unless($project, 404, 'Not found');

        $task = $project->tasks()->find($taskId);

        abort_unless($task, 404, 'Not found');

        return $task;
    }
}
