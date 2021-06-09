<?php

namespace Modules\File\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FileSaved
 *
 * @package Modules\File\Events
 */
class FileSaved extends SomeEvent
{
    public function __construct(Model $changed)
    {
        parent::__construct($changed);
    }
}