<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rotas para o perfil do usuário autenticado
    Route::prefix('me')->group(function () {
        Route::apiSingleton('/', ProfileController::class);

        Route::post('avatar', [ProfileController::class, 'setAvatar']);
        Route::delete('avatar', [ProfileController::class, 'removeAvatar']);
    });
});
