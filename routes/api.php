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
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\TarrifController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/tarrif/{location_id}', [TarrifController::class, 'getTarrif']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/sockets', [SocketController::class, 'index']);
    Route::post('/socket/new', [SocketController::class, 'store']);

    Route::post('/{account_id}/socket/start/{socket_id}', [SessionController::class, 'start']);
    Route::post('/{account_id}/socket/stop/{socket_id}', [SessionController::class, 'stop']);
    
    Route::delete('/socket/delete/{id}', [SocketController::class, 'destroy']);
    Route::post('/account/{id}/changepass', [AuthController::class, 'changePassword']);
    Route::post('/account/profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::get('/profile/{username}/image', [ProfileController::class, 'getUserImage']);
    Route::get('/credits/balance', [CreditController::class, 'getBalance']);
    Route::post('/credits/balance/add', [CreditController::class, 'addBalance']);
    Route::get('/getsessioninfo/{socket_id}', [SocketController::class, 'getSessionInfo']);
    
    Route::get('/{user_id}/notifications', [ErrorMessageController::class, 'userNotifications']);
    Route::delete('/{user_id}/notifications/clear', [ErrorMessageController::class, 'clearNotifications']);

    // User management routes
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

Route::get('/users', [UserController::class, 'getAllUsers']);

Route::get('/health', [HealthController::class, 'check']);
Route::post('/allsockets', [SocketController::class, 'getAllSockets']);
Route::get('/health/backend', [HealthController::class, 'checkBackend']);
Route::get('/health/mysql', [HealthController::class, 'checkMysql']);
Route::get('/health/amafamily', [HealthController::class, 'checkAmafamily']);
Route::get('/health/broncofanclub', [HealthController::class, 'checkBroncofanclub']);
Route::get('/socketbelongsto/{socket_id}', [SocketController::class, 'belongsTo']);
Route::post('/socketbelongsto/bulk', [SocketController::class, 'bulkBelongsTo']);
Route::post('/isuseradmin/{account_id}', [AdminController::class, 'getRoleFromUser']);
Route::get('/socketinfo/{socket_id}', [SocketController::class, 'getSocketInfo']);

// Admin statistieken route
Route::get('/admin/stats', [App\Http\Controllers\AdminController::class, 'getStats']);

// Socket beschikbaarheid en toewijzing routes
Route::get('/sockets/available', [SocketController::class, 'getAvailableSockets']);
Route::post('/locations/{locationId}/sockets/{socketId}', [LocationController::class, 'assignSocket']);

Route::get('/locations', [LocationController::class, 'index']);
Route::post('/locations', [LocationController::class, 'store']);
Route::get('/locations/user/{user_id}', [LocationController::class, 'userLocations']);
Route::get('/locations/{locations_id}', [LocationController::class, 'show']);
Route::get('/locations/{locations_id}/sockets', [LocationController::class, 'showSockets']);
Route::delete('/locations/{locations_id}', [LocationController::class, 'destroy']);
Route::delete('/locations/{locations_id}/sockets/{socket_id}', [LocationController::class, 'destroySocket']);
