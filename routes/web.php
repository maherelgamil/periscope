<?php

use Illuminate\Support\Facades\Route;
use MaherElGamil\Periscope\Http\Controllers\DashboardController;
use MaherElGamil\Periscope\Http\Controllers\MetricsController;

Route::get('/', [DashboardController::class, 'overview'])->name('periscope.overview');
Route::get('/jobs', [DashboardController::class, 'jobs'])->name('periscope.jobs');
Route::get('/jobs/{uuid}', [DashboardController::class, 'job'])->name('periscope.jobs.show');
Route::post('/jobs/{uuid}/retry', [DashboardController::class, 'retry'])->name('periscope.jobs.retry');
Route::get('/failed', [DashboardController::class, 'failed'])->name('periscope.failed');
Route::get('/workers', [DashboardController::class, 'workers'])->name('periscope.workers');
Route::get('/exceptions', [DashboardController::class, 'exceptions'])->name('periscope.exceptions');
Route::get('/exceptions/show', [DashboardController::class, 'exception'])->name('periscope.exceptions.show');
Route::get('/alerts', [DashboardController::class, 'alerts'])->name('periscope.alerts');
Route::get('/schedules', [DashboardController::class, 'schedules'])->name('periscope.schedules');
Route::get('/batches', [DashboardController::class, 'batches'])->name('periscope.batches');
Route::get('/performance', [DashboardController::class, 'performance'])->name('periscope.performance');

if (config('periscope.metrics.enabled', true)) {
    Route::withoutMiddleware(config('periscope.middleware', []))
        ->middleware(config('periscope.metrics.middleware', ['web']))
        ->group(function () {
            Route::get('/metrics', [MetricsController::class, 'prometheus'])->name('periscope.metrics');
            Route::get('/metrics.json', [MetricsController::class, 'json'])->name('periscope.metrics.json');
        });
}
Route::get('/queues', [DashboardController::class, 'queues'])->name('periscope.queues');
