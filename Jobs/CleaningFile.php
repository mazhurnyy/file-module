<?php

namespace Modules\File\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\File\Models\Extension;
use Modules\File\Models\File;
use Modules\File\Repositories\TypeFile;
use Modules\File\Services\Path;
use Modules\File\Services\StorageCloud;

/**
 *  очистка файлов на  облаке и таблице файлов
 */
class CleaningFile implements ShouldQueue
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
        $files = $this->findTrashed($this->data['delete_data'], $this->data['limit']);
        foreach ($files as $file)
        {
            $this->deleteFile($files, $file);
            $file->forceDelete();
        }
    }

    /**
     * Удаление файлов
     * удаляем файлы в хранилище, сортируем по новому, без учета удаленного, удаляем запись в таблице
     *
     * @param Collection $files
     * @param File       $file
     */
    private function deleteFile(Collection $files, File $file)
    {
        // удаляем файлы в хранилище
        $file_model = $file;
        if (isset($file_model->token))
        {
            StorageCloud::deleteDirectory(
                Path::getPathByToken($file_model->token) . $file_model->token
                . '/'
            );
            // сортируем оставшиеся файлы
            $model = $this->findModelFile($file);
            if ($model)
            {
                $i         = 0;
                $extension = Extension::find($file->extension_id);
                if (isset($model->files))
                {
                    $files_model = $files->sortBy('order');
                    foreach ($files_model as $item)
                    {
                        $i++;
                        $item->order = $i;
                        $item->save;
                    }
                }
                $type         = TypeFile::getTypeByName($model, $extension->name);
                $model->$type = $i;
                $model->save();
            }
        }
    }

    /**
     * поиск модели по объекту файла
     *
     * @param File $file
     *
     * @return Model|null
     */
    protected function findModelFile(File $file): ?Model
    {
        $item = $file->file->fileable_type ?? null;
        if ($item)
        {
            $item = '\\' . $item;
            return $item::withTrashed()->whereId($file->file->fileable_id)->first();
        } else
        {
            return null;
        }
    }

    /**
     * Находим удаленные записи
     *
     * @param Carbon $delete_data
     * @param int    $limit
     *
     * @return Collection
     */
    private function findTrashed(Carbon $delete_data, int $limit): Collection
    {
        return File::onlyTrashed()
            ->where('deleted_at', '<', $delete_data)
            ->limit($limit)
            ->orderBy('deleted_at')
            ->get();
    }
}