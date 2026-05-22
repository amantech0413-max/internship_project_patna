<?php

namespace App\Providers;

use App\Repositories\CollegeRepository;
use App\Repositories\Contracts\CollegeRepositoryInterface;
use App\Repositories\Contracts\InternshipGroupRepositoryInterface;
use App\Repositories\Contracts\BulkStudentRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\InternshipGroupRepository;
use App\Repositories\BulkStudentRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(InternshipGroupRepositoryInterface::class, InternshipGroupRepository::class);
        $this->app->bind(CollegeRepositoryInterface::class, CollegeRepository::class);
        $this->app->bind(BulkStudentRepositoryInterface::class, BulkStudentRepository::class);
    }
}
