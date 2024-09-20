<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
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
    Route::prefix('users/me')->name('profile.')->group(function () {
        Route::apiSingleton('/', ProfileController::class)->destroyable();
        Route::post('avatar', [ProfileController::class, 'setAvatar'])->name('setAvatar');
        Route::delete('avatar', [ProfileController::class, 'removeAvatar'])->name('removeAvatar');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('updatePassword');
    });

    // Rotas para o recurso de usuários
    Route::apiResource('users', UserController::class)
        ->only(['index', 'show']);

    // Rotas para o recurso de organizações
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::post('{organizationId}/avatar', [OrganizationController::class, 'setAvatar'])->name('setAvatar');
        Route::delete('{organizationId}/avatar', [OrganizationController::class, 'removeAvatar'])->name('removeAvatar');
    });
    Route::apiResource('organizations', OrganizationController::class);
});
