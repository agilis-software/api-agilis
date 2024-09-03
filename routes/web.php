<?php

use App\Http\Controllers\OpenAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/api/docs', [OpenAPIController::class, 'docs']);

Route::get('/api/docs/{asset}', [OpenAPIController::class, 'asset'])
    ->where('asset', '.*');