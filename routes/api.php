<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProcurementApiController;
use App\Http\Controllers\MaterialSupplierController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Health / Handshake check endpoint
    Route::get('/procurement/status', [ProcurementApiController::class, 'status'])
        ->name('api.v1.procurement.status');

    // Inbound Procurement API endpoint
    Route::post('/procurement/prices', [ProcurementApiController::class, 'receivePrices'])
        ->name('api.v1.procurement.prices');
});

// Lookup endpoint for R&D Formula auto-fill
Route::get('/material-suppliers/price', [MaterialSupplierController::class, 'getPrice'])
    ->name('api.material-suppliers.price');
