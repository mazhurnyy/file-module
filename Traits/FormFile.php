<?php

namespace Modules\File\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\File\Models\File;
use Modules\File\Services\SaveFile;

/**
 * Trait FormFile
 * используем в админке при обновлении фотографий
 * Обработка данных о файлах с формы
 * проверяем существующие файлы, добавляем, обновляем, удаляем и восстанавливаем информацию о файлах
 *
 * @package Modules\File\Traits
 */
trait FormFile
{
    /**
     * проверяем наличие файлов у сущности
     * имена файлов в строке
     * $actual_img  - существующие токены изображений
     * $input_img   - токены изображений пришедшие с формы
     * $delete_img  - удаленные токены изображений, мягко удаляем
     * $new_img     - новые токены изображений, добавляем в хранилище
     * $present_img - существующие токены изображений, проверяем сортировку и добавляем в хранилище
     *
     * @param Model $model
     * @param array $value
     *
     * @throws \Exception
     */
    protected function updateFormImages(Model $model, array $value)
    {
        $actual_img = $this->getActualFileToken($model);

        $input_img = $this->getInputFileToken($value);

        $delete_img = array_diff_key($actual_img, $input_img);
        $this->deleteFileModel($delete_img);

        foreach ($input_img as $token => $order)
        {
            if (isset($actual_img[$token]))
            {
                $this->updateFileOrder($token, $order);

            } else
            {// новый файл - добавляем
                foreach ($value as $item)
                {
                    if (Str::is('*' . $token . '*', $item))
                    {
                        $path = Str::is('http*', $item) ? $item : public_path($item);
                        //               $size = $this->saveImgFile($model, $path, $order);
                        $saveFile = new SaveFile($model, $path, 'jpg');
                        $saveFile->saveFileImage();
                    }
                }
            }
        }
    }

    /**
     * Восстанавливаем удаленные фото
     *
     * @param Model $model
     * @param array $value
     */
    protected function restoreFormImages(Model $model, array $value)
    {
        $delete_img = $this->getDeleteFileToken($model);
        $input_img  = $this->getInputFileToken($value);

        foreach (array_diff_key($delete_img, $input_img) as $token => $value)
        {
            $this->restoreFileToken($token, count($this->getActualFileToken($model)) + 1);
        }
    }

    /**
     * возвращаем массив информации о файлах пришедших с формы
     *  [token] => 'order'
     *
     * @param array $value
     *
     * @return array
     */
    private function getInputFileToken(array $value): array
    {
        foreach ($value as $key => $path)
        {
            if (Str::is('http*', $path))
            {
                //  существующие файлы
                $str   = Str::after($path, config('filesystems.file.storage'));
                $array = explode('/', $str);
                $token = $array[4] ?? null;
            } else
            {
                //  новые файлы, которые необходимо записать, имя файла с расширением
                $token = Str::before(Str::after($path, config('sleeping_owl.imagesUploadDirectory') . '/'), '.');
            }
            // формируем массив токен -> порядок (начинается с 1)
            $input_img[$token] = $key + 1;
        }

        return $input_img ?? [];
    }

    /**
     * возвращаем массив информации об актуальных фалах текущей модели
     *  [token] => 'order'
     *
     * @param Model $model
     *
     * @return array
     */
    private function getActualFileToken(Model $model): array
    {
        foreach ($model->filesImageActual as $item)
        {
            $img[$item->token] = $item->order;
        }
        return $img ?? [];
    }

    /**
     * возвращаем массив информации о мягко удаленных фалах текущей модели
     *  [token] => 'order'
     *
     * @param Model $model
     *
     * @return array
     */
    private function getDeleteFileToken(Model $model): array
    {
        foreach ($model->filesImageTrashed as $item)
        {
            $img[$item->token] = $item->order;
        }
        return $img ?? [];
    }

    /**
     * удаляем файлы изображений, удаленные в форме админки
     *
     * @param array $files - ключи - токены файлов, которые надо удалить
     *
     * @throws \Exception
     */
    private function deleteFileModel(array $files)
    {
        foreach ($files as $token => $order)
        {
            if (File::isTokenFile($token))
            {
                File::findByToken($token)->delete();
            }
        }
    }

    /**
     * востанавливаем файл по token и ставим в конец списка
     *
     * @param string $token
     * @param int    $order
     */
    protected function restoreFileToken(string $token, int $order)
    {
        $file = File::findTrashedByToken($token);
        $file->update(['order' => $order,]);
        $file->restore();
    }

    /**
     * обновляем сортировку файлов по token
     *
     * @param string $token
     * @param int    $order
     */
    protected function updateFileOrder(string $token, int $order)
    {
        $file = File::whereToken($token)->first();
        if (!is_null($file) && $file->order != $order)
        {
            $file->order = $order;
            $file->save();
        }
    }
}