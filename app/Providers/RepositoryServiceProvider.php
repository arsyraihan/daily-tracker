<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Superadmin\Users\UsersRepositoryInterface;
use App\Repositories\Superadmin\Users\UsersRepository;
use App\Repositories\Superadmin\Absensi\AbsensiRepositoryInterface;
use App\Repositories\Superadmin\Absensi\AbsensiRepository;
use App\Repositories\Superadmin\Aktivitas\AktivitasRepositoryInterface;
use App\Repositories\Superadmin\Aktivitas\AktivitasRepository;
use App\Repositories\Superadmin\Tugas\TugasRepositoryInterface;
use App\Repositories\Superadmin\Tugas\TugasRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UsersRepositoryInterface::class, UsersRepository::class);
        $this->app->bind(AbsensiRepositoryInterface::class, AbsensiRepository::class);
        $this->app->bind(AktivitasRepositoryInterface::class, AktivitasRepository::class);
        $this->app->bind(TugasRepositoryInterface::class, TugasRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
