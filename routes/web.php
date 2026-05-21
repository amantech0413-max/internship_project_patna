<?php

use Illuminate\Support\Facades\Route;

/*
| Vue 3 admin SPA (Laravel + Vite) — source: resources/js/admin
| URLs: /admin/login, /admin/dashboard, /admin/register, etc.
*/

Route::get('/register/{slug?}', function (?string $slug = null) {
    if ($slug) {
        return redirect('/admin/register/'.$slug);
    }

    return redirect('/admin/register');
})->where('slug', '[a-z0-9\-]+');

Route::get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*');

Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'api' => url('/api/v1'),
        'admin_panel' => url('/admin'),
        'admin_login' => url('/admin/login'),
        'student_register' => url('/admin/register'),
    ]);
});
