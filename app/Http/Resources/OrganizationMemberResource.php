<?php

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrganizationMemberResource extends JsonResource
{
    public function __construct($resource, $organization)
    {
        parent::__construct($resource);
        $this->organization = $organization;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birth_date,
            'avatar_url' => Storage::disk('public')
                ->url($this->avatar),
            'is_owner' => $this->organization->owner_id == $this->id
        ];
    }
}
