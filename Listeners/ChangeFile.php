<?php

namespace Modules\File\Listeners;

use Modules\File\Events\SomeEvent;
use Modules\File\Repositories\TypeFile;

/**
 * Class ChangeFile
 * действия при добавлении/удалении файлов сущностей
 *
 * @package Modules\File\Listeners
 */
class ChangeFile extends SomeEvent
{

    public function handle(SomeEvent $event)
    {
        $this->updateCountFile($event);
    }

    /**
     * Обновляем количество файлов в таблицах сущностей
     *
     * @param SomeEvent $event
     */
    private function updateCountFile(SomeEvent $event)
    {
        // todo нужны универсальные типы под разные проекты
        if (isset($event->changed->file))
        {
            $count = 0;
            $model = ('\\' . $event->changed->file->fileable_type)::find(
                $event->changed->file->fileable_id
            );
            if($model) {
                $type = TypeFile::getTypeByName($model, $event->changed->extension->name);
                if ($type === TypeFile::IMAGE) {
                    $count = $model->filesImageActual->count();
                } elseif ($type === TypeFile::BOOK) {
                    $count = $model->filesBook->count();
                } elseif ($type === TypeFile::AUDIO) {
                    $count = $model->filesAudio->count();
                } elseif ($type === TypeFile::DOCUMENT) {
                    $count = $model->filesDocument->count();
                } elseif ($type === TypeFile::VIDEO) {
                    $count = $model->filesVideo->count();
                } elseif ($type === TypeFile::PRESENTATION) {
                    $count = $model->filesPresentation->count();
                }
            }
            if (isset($type))
            {
                $model->update([$type => $count]);
            }
        }
    }
}