<?php

use Illuminate\Support\Facades\Route;
use DevForest\Http\Controllers\ReportController;

$middleware = config('report-generator.requireAuthentication') ? ['web', 'auth'] : ['web'];


Route::group(['prefix' => 'report-generator', 'middleware' => $middleware], function () {
    Route::get('/', [ReportController::class, 'index']);
    Route::post('/save-report', [ReportController::class, 'save']);
    Route::get('/reports', [ReportController::class, 'listReports']);
    Route::get('/execute-report/{id}', [ReportController::class, 'execute']);
    Route::get('/columns/{table}', [ReportController::class, 'getColumns']);

    Route::get('/reports/{id}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{id}', [ReportController::class, 'update'])->name('reports.update');
});
