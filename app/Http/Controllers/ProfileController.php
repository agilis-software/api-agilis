<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\ProfileResource;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return (new ProfileResource($user))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();

        $user->update($request->validated());

        return (new ProfileResource($user))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password', 'confirmed'],
        ]);
        $user = Auth::user();
        $user->delete();

        return response()
            ->noContent();
    }

    public function setAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|file|mimes:png,jpeg,jpg|max:4096',
        ]);

        $user = Auth::user();

        if ($user->avatar !== config('agilis.users.avatars.default')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $mediaPath = MediaService::saveMedia(
            $request->file('avatar'),
            config('agilis.users.avatars.folder'),
            $user->id
        );

        $user->update([
            'avatar' => $mediaPath,
        ]);

        return (new ProfileResource($user))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar !== config('agilis.users.avatars.default')) {
            MediaService::deleteMedia($user->avatar);
        }

        $user->update([
            'avatar' => config('agilis.users.avatars.default'),
        ]);

        return (new ProfileResource($user))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $user = Auth::user();

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()
            ->noContent();
    }
}
