<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/profiles/me', [ProfileController::class, 'me']);
    Route::apiResource('profiles', ProfileController::class)->parameters([
        'profile' => 'user_id',
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
