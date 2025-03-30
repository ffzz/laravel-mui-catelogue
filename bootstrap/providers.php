<?php

return [
    App\Providers\AppServiceProvider::class,
    // Only include Telescope provider if the class exists
    class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class) ? App\Providers\TelescopeServiceProvider::class : null,
    App\Providers\AcornServiceProvider::class,
];
