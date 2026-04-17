<?php

use Illuminate\Support\Facades\Route;
use MaherElGamil\Periscope\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'overview'])->name('periscope.overview');
Route::get('/jobs', [DashboardController::class, 'jobs'])->name('periscope.jobs');
Route::get('/jobs/{uuid}', [DashboardController::class, 'job'])->name('periscope.jobs.show');
Route::get('/failed', [DashboardController::class, 'failed'])->name('periscope.failed');
Route::get('/workers', [DashboardController::class, 'workers'])->name('periscope.workers');
Route::get('/queues', [DashboardController::class, 'queues'])->name('periscope.queues');
