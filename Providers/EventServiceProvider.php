<?php

namespace Modules\File\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @package Modules\File\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'Modules\File\Events\FileSaved'    => [
            'Modules\File\Listeners\ChangeFile',
        ],
        'Modules\File\Events\FileDeleted' => [
            'Modules\File\Listeners\ChangeFile',
        ],
        'Modules\File\Events\FileRestored' => [
            'Modules\File\Listeners\ChangeFile',
        ],
    ];
}