<?php

use App\Http\Controllers\Api\CashRegisterApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // Cash Register API
    Route::prefix('cash-register')->name('api.cash-register.')->group(function () {
        Route::get('/last-closing-balance', [CashRegisterApiController::class, 'getLastClosingBalance'])
            ->name('last-closing-balance');
    });

});
