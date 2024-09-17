<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return new UserResource($user);
    }

    public function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();

        $user->update($request->validated());

        return new UserResource($user);
    }

    public function setAvatar(Request $request)
    {
        $user = Auth::user();
        $avatar = $request->file('avatar');
        $extension = $avatar->getClientOriginalExtension();
        $avatarName = $user->id.'.'.$extension;

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $avatarPath = $avatar->storeAs('avatars', $avatarName, 'public');

        $user->avatar = $avatarPath;
        $user->save();

        return new UserResource($user);
    }

    public function removeAvatar()
    {
        $user = Auth::user();
        $user->update([
            'avatar' => 'avatars/default.png',
        ]);

        return new UserResource($user);
    }
}
