<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => new StatusResource($this->status),
            'due_date' => $this->due_date,
            'project_id' => $this->project->id,
            'assignee' => (new UserResource($this->load('assignee')->assignee)),
        ];
    }
}
