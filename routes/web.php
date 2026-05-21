<?php

use Illuminate\Support\Facades\Route;

Route::get('/admin/{path?}', function () {
    $index = base_path('frontend/.output/public/index.html');

    if (file_exists($index)) {
        return file_get_contents($index);
    }

    return redirect('http://localhost:3000');
})->where('path', '.*');

Route::get('/', function () {
    return response()->json([
        'app' => config('bli.name'),
        'organization' => config('bli.organization'),
        'api' => url('/api/v1'),
        'admin' => url('/admin'),
        'docs' => 'See README.md and docs/postman_collection.json',
    ]);
});
