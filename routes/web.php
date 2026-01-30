<?php

use Illuminate\Support\Facades\Route;
use Xenon007\SeatSpfarm\Http\Controllers\DashboardController;
use Xenon007\SeatSpfarm\Http\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| SP Farming Routes
|--------------------------------------------------------------------------
|
| All routes for the SP Farming plugin are defined here. They are grouped
| under the configured route prefix and protected by the 'web' and 'auth'
| middleware so that only authenticated users can access them. The route
| names are prefixed with 'seat-spfarm.' for convenience.
|
*/

Route::group([
    'prefix' => config('seat-spfarm.route_prefix', 'spfarm'),
    'as' => 'seat-spfarm.',
    'middleware' => ['web', 'auth'],
], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
});