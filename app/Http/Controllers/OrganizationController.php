<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationStoreRequest;
use App\Http\Requests\OrganizationUpdateRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Rules\OrganizationOwnerRule;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $filter = $request->query('filter', 'all');

        $organizations = match ($filter) {
            'own' => $user->ownOrganizations(),
            default => $user->organizations(),
        };

        return OrganizationResource::collection($organizations->paginate(10))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function store(OrganizationStoreRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $organization = $user->ownOrganizations()->create($data);
        $organization->users()->attach($user->id);
        $organization->refresh();

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(int $id)
    {
        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(OrganizationUpdateRequest $request, int $id)
    {
        Validator::make(
            ['organization_id' => $id],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $data = $request->validated();

        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

        $organization->update($data);

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function destroy(Request $request, int $id)
    {
        Validator::make(
            [
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'organization_id' => $id,
            ],
            [
                'password' => ['required', 'string', 'current_password', 'confirmed'],
                'organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule],
            ]
        )->validate();

        $organization = Organization::find($id);

        $organization->delete();

        return response()
            ->noContent();
    }

    public function setAvatar(Request $request, int $id)
    {
        Validator::make(
            ['organization_id' => $id],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

        $request->validate([
            'avatar' => 'required|file|mimes:png,jpeg,jpg|max:4096',
        ]);

        $avatar = $request->file('avatar');
        $extension = $avatar->getClientOriginalExtension();
        $avatarName = $id.'.'.$extension;

        if ($organization->avatar !== config('agilis.organizations.avatars.default')) {
            Storage::disk('public')->delete($organization->avatar);
        }

        $avatarPath = $avatar->storeAs(config('agilis.organizations.avatars.folder'), $avatarName, 'public');

        $organization->avatar = $avatarPath;
        $organization->save();

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function removeAvatar(int $id)
    {
        Validator::make(
            ['organization_id' => $id],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $organization = Organization::find($id);

        if ($organization->avatar != config('agilis.organizations.avatars.default')) {
            MediaService::deleteMedia($organization->avatar);
        }

        $organization->update([
            'avatar' => config('agilis.organizations.avatars.default'),
        ]);

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
