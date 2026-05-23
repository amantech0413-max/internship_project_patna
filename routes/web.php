<?php

use App\Support\RegistrationPaths;
use Illuminate\Support\Facades\Route;

/*
| Vue 3 admin SPA — resources/js/admin
| Registration (all work, same form):
|   /{college-slug}
|   /register/{college-slug}
|   /admin/register/{college-slug}
| Admin panel: /admin/login, /admin/dashboard, ...
*/

Route::get('/privacy-policy', function () {
    return view('admin');
});

Route::get('/register/{slug?}', function () {
    return view('admin');
})->where('slug', '[a-z0-9\-]+');

Route::get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*');

Route::get('/{slug}', function (string $slug) {
    if (RegistrationPaths::isReservedSegment($slug)) {
        abort(404);
    }

    return view('admin');
})->where('slug', '[a-z0-9\-]+');

Route::get('/', function () {
    return view('admin');
});
