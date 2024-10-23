<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectUserController;
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
        Route::apiSingleton('/', ProfileController::class);
        Route::post('delete', [ProfileController::class, 'destroy']);
        Route::post('avatar', [ProfileController::class, 'setAvatar'])->name('setAvatar');
        Route::delete('avatar', [ProfileController::class, 'removeAvatar'])->name('removeAvatar');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('updatePassword');
    });

    // Rotas para o recurso de usuários
    Route::apiResource('users', UserController::class)
        ->only(['index', 'show']);

    // Rotas de organizações
    Route::prefix('organizations')->name('organizations.')->group(function () {
        Route::prefix('{organizationId}')->name('organization.')->group(function() {
            Route::post('avatar', [OrganizationController::class, 'setAvatar'])->name('setAvatar');
            Route::delete('avatar', [OrganizationController::class, 'removeAvatar'])->name('removeAvatar');
            Route::post('delete', [OrganizationController::class, 'destroy'])->name('destroy');
            Route::post('invite', [OrganizationController::class, 'invite'])->name('invite');

            // Rotas de projetos
            Route::prefix('projects')->group(function () {
                Route::prefix('{projectId}')->group(function () {
                    Route::post('assign', [ProjectUserController::class, 'assign'])->name('projects.users.assign');
                    Route::post('unassign', [ProjectUserController::class, 'unassign'])->name('projects.users.unassign');
                    Route::post('leave', [ProjectUserController::class, 'leave'])->name('projects.leave');

                    Route::apiResource('users', ProjectUserController::class)
                        ->only(['index', 'show']);
                });
            });

            Route::apiResource('projects', ProjectController::class);
        });
    });
    Route::apiResource('organizations', OrganizationController::class)->except(['destroy']);
});
