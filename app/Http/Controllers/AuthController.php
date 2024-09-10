<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $credentials = $request->validated();
        $credentials['password'] = Hash::make($request->password);

        $user = User::create($credentials);

        $resultToken = $user->createToken('access_token');

        return (new AuthResource($resultToken))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $canAuth = $user && Hash::check($request->password, $user->password);

        abort_unless($canAuth, 422, 'The provided credentials are incorrect');

        $resultToken = $user->createToken('access_token');

        return new AuthResource($resultToken);
    }

    public function logout()
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();

        return response(status: 204);
    }
}
