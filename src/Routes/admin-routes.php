<?php

use Illuminate\Support\Facades\Route;
use Webbycrown\Customization\Http\Controllers\Admin\CustomizationController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => config('app.admin_url')], function () {

    /**
     * Admin customization routes
     */
    Route::controller(CustomizationController::class)->group(function () {

        Route::prefix('customization')->group(function () {

            Route::get('/{slug1}/{slug2}', 'sections_index')
                    ->defaults('_config', ['view' => 'wc_customization::admin.sections.index'])
                    ->name('wc_customization.admin.customization.sections.index');

            Route::get('/setting/{slug1}/{slug2}/{id}', 'repeater_sections_setting_index')
                    ->defaults('_config', ['view' => 'wc_customization::admin.sections.setting-repeater'])
                    ->name('wc_customization.admin.customization.sections.setting.repeater');

            Route::get('/setting/{slug1}/{slug2}', 'sections_setting_index')
                    ->defaults('_config', ['view' => 'wc_customization::admin.sections.setting'])
                    ->name('wc_customization.admin.customization.sections.setting');

            Route::get('/{slug1}', 'pages_index')
                    ->defaults('_config', ['view' => 'wc_customization::admin.pages.index'])
                    ->name('wc_customization.admin.customization.pages.index');

            Route::get('/', 'index')
                    ->defaults('_config', ['view' => 'wc_customization::admin.customization.index'])
                    ->name('wc_customization.admin.customization.index');

            Route::post('/store', 'store')
                    ->defaults('_config', ['redirect' => 'wc_customization::admin.customization.index'])
                    ->name('wc_customization.customization.store');

            Route::prefix('page')->group(function () {

                Route::post('/store', 'page_store')
                        ->defaults('_config', ['redirect' => 'wc_customization::admin.customization.index'])
                        ->name('wc_customization.page.store');

                Route::get('/edit/{id}', 'page_edit')->name('wc_customization.page.edit');

                Route::delete('/delete/{id}', 'page_delete')->name('wc_customization.page.delete');

            });

            Route::prefix('section')->group(function () {

                Route::post('/store', 'section_store')
                        ->defaults('_config', ['redirect' => 'wc_customization::admin.customization.index'])
                        ->name('wc_customization.section.store');

                Route::get('/edit/{id}', 'section_edit')->name('wc_customization.section.edit');

                Route::delete('/delete/{id}', 'section_delete')->name('wc_customization.section.delete');

                Route::prefix('setting')->group(function () {

                    Route::post('/store', 'section_setting_store')
                            ->defaults('_config', ['redirect' => 'wc_customization::admin.customization.index'])
                            ->name('wc_customization.section.setting.store');

                    Route::get('/edit/{id}', 'section_setting_edit')->name('wc_customization.section.setting.edit');

                    Route::delete('/delete/{id}', 'section_setting_delete')->name('wc_customization.section.setting.delete');

                    Route::post('/validate', 'section_setting_validate')->name('wc_customization.section.setting.validate');

                });

            });

        });

    });

});

    