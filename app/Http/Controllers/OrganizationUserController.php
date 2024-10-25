<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationInviteRequest;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrganizationUserController extends Controller
{
    public function index(int $id)
    {
        $organization = Organization::findOrFail($id);

        return UserResource::collection($organization->users)
            > response()
                ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function invite(OrganizationInviteRequest $request, int $id)
    {
        $this->validateOrganizationOwner($id);

        $organization = Organization::findOrFail($id);
        $data = $request->validated();

        $emailExists = $organization->users()->where('email', $data['email'])->exists();

        abort_if($emailExists, 422, 'User already in organization');

        $user = Auth::user()->where('email', $data['email'])->firstOrFail();

        $organization->users()->attach($user->id);

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function kick(int $organizationId, int $userId)
    {
        $this->validateOrganizationOwner($organizationId);

        $organization = Organization::findOrFail($organizationId);
        $user = $organization->users()->findOrFail($userId);

        $organization->users()->detach($userId);

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function leave(int $organizationId)
    {
        $organization = Organization::findOrFail($organizationId);

        abort_if($organization->owner_id === Auth::id(), 422, 'Owner cannot leave organization');

        $organization->users()->findOrFail(Auth::id());

        $organization->users()->detach(Auth::id());

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    private function validateOrganizationOwner(int $id)
    {
        Validator::make(
            ['organization_id' => $id],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();
    }
}
