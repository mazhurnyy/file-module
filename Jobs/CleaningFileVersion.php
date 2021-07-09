<?php

namespace Modules\File\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\File\Models\FileVersion;
use Modules\File\Services\Path;
use Modules\File\Services\StorageCloud;

/**
 * Class CleaningFileVersion
 * очистка файлов на  облаке и таблице файлов
 *
 * @package Modules\File\Jobs
 */
class CleaningFileVersion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    private $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     *  очищаем просроченные файлы
     */
    public function handle()
    {
        $files = $this->findTrashed($this->data['delete_data'], $this->data['limit'], '\Modules\File\Models\FileVersion');

        foreach ($files as $file)
        {
            $this->deleteFile($file);
            $file->forceDelete();
        }
    }

    /**
     * удаляем версию файла в хранилище
     *
     * @param FileVersion $file
     */
    private function deleteFile(FileVersion $file)
    {
        $file_model = $file->file;
        if (isset($file_model->token))
        {
            StorageCloud::deleteFile(
                Path::getPathByToken($file_model->token) . $file_model->token
                . '/', $file_model->alias . '-' . $file->prefix . '.' .
                $file_model->extension->name
            );
        }
    }
}