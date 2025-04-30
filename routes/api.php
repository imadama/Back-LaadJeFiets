<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocketController;
use App\Http\Controllers\ProfileController;
use Intervention\Image\Facades\Image;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sockets', [SocketController::class, 'index']);
    Route::post('/socket/new', [SocketController::class, 'store']);
    Route::delete('/socket/delete/{id}', [SocketController::class, 'destroy']);
    Route::post('/account/{id}/changepass', [AuthController::class, 'changePassword']);
    Route::post('/account/profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::get('/profile/{username}/image', [ProfileController::class, 'getUserImage']);
});