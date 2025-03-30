<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('ContentPage');
})->name('home');

require __DIR__ . '/api.php';
