<?php
// src/ReportGeneratorServiceProvider.php
namespace DevForest;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use MdSazzadulIslam\LaravelDynamicReportGenerator\Http\Middleware\ShareErrorsFromSessionMiddleware;

class ReportGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-dynamic-report-generator');
        // $this->publishes([
        //     __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-dynamic-report-generator'),
        // ], 'views');
        $this->publishes([
            __DIR__ . '/../database/migrations/2024_05_29_000000_create_generated_reports_table.php' => database_path('migrations/2024_05_29_000000_create_generated_reports_table.php'),
        ], 'migrations');
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/devforest/laravel-dynamic-report-generator'),
        ], 'public');

        Route::aliasMiddleware('share.errors', ShareErrorsFromSessionMiddleware::class);

        $this->registerRoutes();
    }
    protected function registerRoutes()
    {
        Route::group([
            'middleware' => ['web', 'share.errors'],
            'namespace' => 'DevForest\ReportGenerator\Http\Controllers',
            'prefix' => 'report-generator'
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    public function register()
    {
        // Register package services if needed
    }
}
