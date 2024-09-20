<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return (new UserCollection(User::paginate(15)))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(int $id)
    {
        $user = User::find($id);

        abort_unless($user, 404, 'Not found');

        return (new UserResource($user))
            ->response()
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
