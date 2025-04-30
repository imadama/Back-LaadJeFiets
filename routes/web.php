<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/hello', [ApiController::class, 'hello']);
Route::get('/name', [ApiController::class, 'getName']);
require __DIR__.'/auth.php';

