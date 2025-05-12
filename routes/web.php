<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SessionController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/hello', [ApiController::class, 'hello']);
Route::get('/name', [ApiController::class, 'getName']);
require __DIR__.'/auth.php';
Route::get('/test', [TestController::class, 'test']);
Route::get('/start', [SessionController::class, 'start']);
Route::get('/stop', [SessionController::class, 'stop']);