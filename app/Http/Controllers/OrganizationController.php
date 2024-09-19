<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationDeleteRequest;
use App\Http\Requests\OrganizationStoreRequest;
use App\Http\Requests\OrganizationUpdateRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Rules\OrganizationOwnerRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return OrganizationResource::collection(Organization::paginate(5))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrganizationStoreRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $organization = $user->ownOrganizations()->create($data);
        $organization->refresh();

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrganizationDeleteRequest $request, int $id)
    {
        Validator::make(
            ['organization_id' => $id],
            ['organization_id' => ['required', 'exists:organizations,id', new OrganizationOwnerRule]]
        )->validate();

        $organization = Organization::find($id);

        $organization->delete();

        return response()
            ->noContent();
    }

    public function setAvatar(Request $request, int $id)
    {
        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

        $avatar = $request->file('avatar');
        $extension = $avatar->getClientOriginalExtension();
        $avatarName = $id . '.' . $extension;

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

        abort_unless($organization, '404', 'Not found');

        $organization->update([
            'avatar' => config('agilis.organizations.avatars.default'),
        ]);

        return (new OrganizationResource($organization))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
