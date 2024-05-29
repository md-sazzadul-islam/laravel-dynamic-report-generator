<?php
// src/ReportGeneratorServiceProvider.php
namespace Sazzad\LaravelDynamicReportGenerator;

use Illuminate\Support\ServiceProvider;

class ReportGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-dynamic-report-generator');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-dynamic-report-generator'),
        ], 'views');
        $this->publishes([
            __DIR__ . '/../database/migrations/2024_05_29_000000_create_generated_reports_table.php' => database_path('migrations/2024_05_29_000000_create_generated_reports_table.php'),
        ], 'migrations');
    }

    public function register()
    {
        // Register package services if needed
    }
}
