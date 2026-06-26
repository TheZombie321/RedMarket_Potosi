<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function main(string $filename)
    {
        $filename = basename($filename);
        $path = 'main/' . $filename;
        if (!Storage::disk('root_images')->exists($path)) {
            abort(404);
        }
        $content = Storage::disk('root_images')->get($path);
        $mime = Storage::disk('root_images')->mimeType($path) ?: 'image/webp';
        return response($content, 200, ['Content-Type' => $mime]);
    }
}
