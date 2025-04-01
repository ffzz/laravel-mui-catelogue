<?php

use App\Http\Controllers\Api\V1\ContentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Version 1
Route::prefix('v1')->group(function () {
    // Content routes
    Route::prefix('content')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('api.content.index');
        Route::get('/{id}', [ContentController::class, 'show'])->where('id', '[0-9]+')->name('api.content.show');
        Route::post('/refresh-cache', [ContentController::class, 'refreshCache'])->name('api.content.refresh-cache');
    });
});
