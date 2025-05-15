<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ErrorMessageController;
use App\Http\Controllers\HealthController;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AdminController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sockets', [SocketController::class, 'index']);
    Route::post('/socket/new', [SocketController::class, 'store']);

    Route::post('/{account_id}/socket/start/{socket_id}', [SessionController::class, 'start']);
    Route::post('/{account_id}/socket/stop/{socket_id}', [SessionController::class, 'stop']);
    
    Route::delete('/socket/delete/{id}', [SocketController::class, 'destroy']);
    Route::post('/account/{id}/changepass', [AuthController::class, 'changePassword']);
    Route::post('/account/profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::get('/profile/{username}/image', [ProfileController::class, 'getUserImage']);
    Route::get('/getsessioninfo/{socket_id}', [SocketController::class, 'getSessionInfo']);
    
    Route::get('/{user_id}/notifications', [ErrorMessageController::class, 'userNotifications']);
    Route::delete('/{user_id}/notifications/clear', [ErrorMessageController::class, 'clearNotifications']);
});

Route::get('/health', [HealthController::class, 'check']);
Route::post('/allsockets', [SocketController::class, 'getAllSockets']);
Route::get('/health/backend', [HealthController::class, 'checkBackend']);
Route::get('/health/mysql', [HealthController::class, 'checkMysql']);
Route::get('/health/amafamily', [HealthController::class, 'checkAmafamily']);
Route::get('/health/broncofanclub', [HealthController::class, 'checkBroncofanclub']);
Route::get('/socketbelongsto/{socket_id}', [SocketController::class, 'belongsTo']);
Route::post('/isuseradmin/{account_id}', [AdminController::class, 'getRoleFromUser']);
Route::get('/socketinfo/{socket_id}', [SocketController::class, 'getSocketInfo']);
