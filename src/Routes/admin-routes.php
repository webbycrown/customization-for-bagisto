<?php

use Illuminate\Support\Facades\Route;
use Webbycrown\Customization\Http\Controllers\Admin\CustomizationController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => config('app.admin_url')], function () {

    /**
     * Admin customization routes
     */

    Route::get('customization/{slug1}/{slug2}', [CustomizationController::class, 'sections_index'])->defaults('_config', [
        'view' => 'wc_customization::admin.sections.index',
    ])->name('wc_customization.admin.customization.sections.index');

    Route::get('customization/setting/{slug1}/{slug2}', [CustomizationController::class, 'sections_setting_index'])->defaults('_config', [
        'view' => 'wc_customization::admin.sections.setting',
    ])->name('wc_customization.admin.customization.sections.setting');

    Route::get('customization/{slug1}', [CustomizationController::class, 'pages_index'])->defaults('_config', [
        'view' => 'wc_customization::admin.pages.index-form',
    ])->name('wc_customization.admin.customization.pages.index');

    Route::get('customization', [CustomizationController::class, 'index'])->defaults('_config', [
        'view' => 'wc_customization::admin.customization.index-form',
    ])->name('wc_customization.admin.customization.index');

    Route::post('customization/store', [CustomizationController::class, 'store'])->defaults('_config', [
        'redirect' => 'wc_customization::admin.customization.index',
    ])->name('wc_customization.customization.store');

    Route::post('customization/page/store', [CustomizationController::class, 'page_store'])->defaults('_config', [
        'redirect' => 'wc_customization::admin.customization.index',
    ])->name('wc_customization.page.store');

    Route::post('customization/section/store', [CustomizationController::class, 'section_store'])->defaults('_config', [
        'redirect' => 'wc_customization::admin.customization.index',
    ])->name('wc_customization.section.store');

    Route::get('customization/page/edit/{id}', [CustomizationController::class, 'page_edit'])->name('wc_customization.page.edit');
    Route::get('customization/section/edit/{id}', [CustomizationController::class, 'section_edit'])->name('wc_customization.section.edit');

});

    