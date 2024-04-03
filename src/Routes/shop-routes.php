<?php

use Illuminate\Support\Facades\Route;
use Webbycrown\Customization\Http\Controllers\Shop\CustomizationController;

Route::get('/api/v1/customization_details', [CustomizationController::class, 'get_customization_details']);
Route::post('/api/v1/customization_details', [CustomizationController::class, 'get_customization_details']);