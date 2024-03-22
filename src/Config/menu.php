<?php

return [

    /**
     * Custom Menu.
     */
    [
        'key'        => 'custom_menu',
        'name'       => 'Custom Menu',
        'route'      => 'admin.settings.locales.index',
        'sort'       => 10,
        'icon'       => 'icon-settings',
        'icon-class' => 'settings-icon',
    ], [
        'key'        => 'custom_menu.sub_menu_1',
        'name'       => 'Sub Menu 1',
        'route'      => 'admin.settings.locales.index',
        'sort'       => 1,
        'icon'       => '',
    ], [
        'key'        => 'custom_menu.sub_menu_2',
        'name'       => 'Sub Menu 2',
        'route'      => 'admin.settings.currencies.index',
        'sort'       => 2,
        'icon'       => '',
    ], [
        'key'        => 'custom_menu.sub_menu_3',
        'name'       => 'Sub Menu 3',
        'route'      => 'admin.settings.currencies.index',
        'sort'       => 3,
        'icon'       => '',
    ],

];
