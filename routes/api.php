<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {
    // Rota para logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rotas para o perfil do usuário autenticado
    Route::prefix('users/me')->group(function () {
        Route::apiSingleton('/', ProfileController::class)->destroyable();
        Route::post('avatar', [ProfileController::class, 'setAvatar'])->name('profile.setAvatar');
        Route::delete('avatar', [ProfileController::class, 'removeAvatar'])->name('profile.removeAvatar');

        Route::put('password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    });

    // Rotas para o recurso de usuários
    Route::apiResource('users', UserController::class)
        ->only(['index', 'show']);
});
