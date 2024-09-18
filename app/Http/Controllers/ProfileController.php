<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileDeleteRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\ProfileResource;
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

    public function destroy(ProfileDeleteRequest $request)
    {
        $user = Auth::user();
        $user->delete();

        return response()
            ->noContent();
    }

    public function setAvatar(Request $request)
    {
        $user = Auth::user();
        $avatar = $request->file('avatar');
        $extension = $avatar->getClientOriginalExtension();
        $avatarName = $user->id.'.'.$extension;

        if ($user->avatar !== config('agilis.users.avatars.default')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $avatarPath = $avatar->storeAs('avatars', $avatarName, 'public');

        $user->avatar = $avatarPath;
        $user->save();

        return (new ProfileResource($user))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function removeAvatar()
    {
        $user = Auth::user();
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