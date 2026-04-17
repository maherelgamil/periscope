<?php

use Illuminate\Support\Facades\Route;
use MaherElGamil\Periscope\Http\Controllers\DashboardController;
use MaherElGamil\Periscope\Http\Controllers\MetricsController;

Route::get('/', [DashboardController::class, 'overview'])->name('periscope.overview');
Route::get('/jobs', [DashboardController::class, 'jobs'])->name('periscope.jobs');
Route::get('/jobs/{uuid}', [DashboardController::class, 'job'])->name('periscope.jobs.show');
Route::get('/failed', [DashboardController::class, 'failed'])->name('periscope.failed');
Route::get('/workers', [DashboardController::class, 'workers'])->name('periscope.workers');
Route::get('/exceptions', [DashboardController::class, 'exceptions'])->name('periscope.exceptions');

if (config('periscope.metrics.enabled', true)) {
    Route::withoutMiddleware(config('periscope.middleware', []))
        ->middleware(config('periscope.metrics.middleware', ['web']))
        ->group(function () {
            Route::get('/metrics', [MetricsController::class, 'prometheus'])->name('periscope.metrics');
            Route::get('/metrics.json', [MetricsController::class, 'json'])->name('periscope.metrics.json');
        });
}
Route::get('/queues', [DashboardController::class, 'queues'])->name('periscope.queues');
