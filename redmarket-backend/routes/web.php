<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('img/productos/main/{filename}', [ImageController::class, 'main'])
    ->where('filename', '[a-zA-Z0-9._-]+');
