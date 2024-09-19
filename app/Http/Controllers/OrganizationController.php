<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationDeleteRequest;
use App\Http\Requests\OrganizationStoreRequest;
use App\Http\Requests\OrganizationUpdateRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();

        $organization = Organization::create($data);

        return new OrganizationResource($organization);
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
        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

        $organization->delete();

        return response()
            ->noContent();
    }

    public function setAvatar(Request $request, int $id)
    {
        $user = Auth::user();

        $organization = Organization::find($id);

        abort_unless($organization, 404, 'Not found');

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
        $user = Auth::user();
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
