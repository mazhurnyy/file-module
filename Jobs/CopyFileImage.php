<?php

namespace Modules\File\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\File\Services\SaveFile;

/**
 * Class CopyFileImage
 * Копирование файлов изображений
 *
 * @package Modules\File\Jobs
 */
class CopyFileImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    /**
     * @var array ['model', 'path', 'extension']
     */
    private array $data;

    /**
     * Create a new job instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $saveFile = (new SaveFile($this->data['model'], $this->data['path'], $this->data['extension']));
        if ($saveFile->saveFileImage() == 0)
        {
            throw new \Exception();
        }
    }
}
