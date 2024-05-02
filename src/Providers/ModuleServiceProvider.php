<?php

namespace Webbycrown\Customization\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        \Webbycrown\Customization\Models\CustomizationDetails::class,
        \Webbycrown\Customization\Models\CustomizationPages::class,
        \Webbycrown\Customization\Models\CustomizationSections::class,
        \Webbycrown\Customization\Models\CustomizationSettings::class,
    ];
}
