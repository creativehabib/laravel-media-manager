<?php
use Illuminate\Support\Facades\Route;
use Habib\MediaManager\Http\Controllers\MediaManagerController;

Route::group([
    'prefix' => config('mediamanager.route_prefix'),
    'middleware' => config('mediamanager.middleware'),
], function () {
    Route::get('/', [MediaManagerController::class, 'index'])
        ->name('mediamanager.index');

    Route::post('/upload', [MediaManagerController::class, 'upload'])
        ->name('mediamanager.upload');

    Route::delete('/{media}', [MediaManagerController::class, 'destroy'])
        ->name('mediamanager.destroy');
});
