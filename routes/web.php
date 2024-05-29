<?php

use Illuminate\Support\Facades\Route;
use Sazzad\LaravelDynamicReportGenerator\Http\Controllers\ReportController;

Route::prefix('report-generator')->group(function () {
    Route::get('/', [ReportController::class, 'index']);
    Route::post('/save-report', [ReportController::class, 'save']);
    Route::get('/reports', [ReportController::class, 'listReports']);
    Route::get('/execute-report/{id}', [ReportController::class, 'execute']);
    Route::get('/columns/{table}', [ReportController::class, 'getColumns']);
});
