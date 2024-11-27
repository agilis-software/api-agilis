<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationInviteRequest;
use App\Http\Resources\OrganizationMemberResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrganizationUserController extends Controller
{
    public function index(int $id)
    {
        $user = Auth::user();
        $organization = $user->organizations()->find($id);

        abort_unless($organization, 404, 'Organization not found');

        return OrganizationMemberResource::collection(
            $organization->users->map(function ($user) use ($organization) {
                return new OrganizationMemberResource($user, $organization);
            }))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(int $id, int $userId)
    {
        $user = Auth::user();
        $organization = $user->organizations()->find($id);

        abort_unless($organization, 404, 'Organization not found');

        $userToFind = $organization->users->find($userId);

        abort_unless($userToFind, 404, 'User not found');

        return (new OrganizationMemberResource($userToFind, $organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function invite(OrganizationInviteRequest $request, int $id)
    {
        $this->validateOrganizationOwner($id);

        $organization = Organization::findOrFail($id);
        $data = $request->validated();

        $emailExists = $organization->users()->where('email', $data['email'])->exists();

        abort_if($emailExists, 422, 'User already in organization');

        $user = User::where('email', $data['email'])->firstOrFail();

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
