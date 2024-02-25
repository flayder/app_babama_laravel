<?php

declare(strict_types=1);

namespace App\Modules\Transaction\Providers;

use App\Modules\Transaction\Transaction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Register any application services. */
    public function register(): void
    {
//        require_once app_path('/Helper/helpers.php');

        $this->app->bind('modules.transaction', fn () => new Transaction());
    }

    /** Bootstrap any application services. */
    public function boot(): void
    {
        $this->setMigrations();
    }

    private function setMigrations()
    {
        $mainPath = database_path('migrations');
        $directories = glob(__DIR__ . '/../Database/Migrations' . '/' , GLOB_ONLYDIR);
        $paths = array_merge([$mainPath], $directories);

        $this->loadMigrationsFrom($paths);
    }
}
