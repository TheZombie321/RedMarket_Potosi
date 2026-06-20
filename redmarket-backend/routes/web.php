<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('img/productos/main/{filename}', function (string $filename) {
    $filename = basename($filename);
    $path = 'main/' . $filename;
    if (!Storage::disk('root_images')->exists($path)) {
        abort(404);
    }
    $content = Storage::disk('root_images')->get($path);
    return response($content, 200, ['Content-Type' => 'image/webp']);
})->where('filename', '[a-zA-Z0-9._-]+');
