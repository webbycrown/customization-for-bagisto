<?php

use Illuminate\Support\Facades\Route;
use Webbycrown\Customization\Http\Controllers\Shop\CustomizationController;

Route::prefix('api/v1')->group(function () {
    
    Route::controller(CustomizationController::class)->group(function () {
        
        Route::get('/customization_details', 'get_customization_details');
        
        Route::post('/customization_details', 'get_customization_details');

    });

});