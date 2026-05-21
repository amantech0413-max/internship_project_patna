<?php

namespace App\Providers;

use App\Models\Student;
use App\Observers\StudentObserver;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Student::observe(StudentObserver::class);

        // API-only auth: never redirect to web route('login') (undefined in this project)
        Authenticate::redirectUsing(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }

            return config('app.url');
        });
    }
}
