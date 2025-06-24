<?php

use Illuminate\Support\Facades\Route;
use webdophp\ProSystemsIntegration\Http\Controllers\v1\ProSystemsController;


Route::middleware(['api', 'pro-systems.key'])->prefix('api/v1/pro-systems')->group(function () {
    Route::get('/ping', [ProSystemsController::class, 'ping']);
    Route::get('/data', [ProSystemsController::class, 'data']);
    Route::get('/confirm', [ProSystemsController::class, 'confirm']);
});

