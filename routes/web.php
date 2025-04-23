<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/hello', [ApiController::class, 'hello']);

require __DIR__.'/auth.php';

