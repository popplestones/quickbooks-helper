<?php

use Illuminate\Support\Facades\Route;
use Popplestones\Quickbooks\Http\Controllers\QuickbooksController;

Route::controller(QuickbooksController::class)
    ->as('quickbooks.')
    ->prefix('quickbooks')
    ->group(function () {
        Route::get('connect', 'connect')->name('connect');
        Route::delete('disconnect', 'disconnect')->name('disconnect');
});
