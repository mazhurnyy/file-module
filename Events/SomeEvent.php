<?php

namespace Modules\File\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class SomeEvent
 */
class SomeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected ?Model $changed;

    /**
     * SomeEvent constructor.
     *
     * @param Model|null $changed
     */
    public function __construct(?Model $changed = null)
    {
        $this->changed = $changed;
    }
}
