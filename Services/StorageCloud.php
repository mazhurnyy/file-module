<?php

namespace Modules\File\Services;

use Illuminate\Support\Str;
use Storage;

/**
 * Работа с облачным хранилищем данных
 * настройки подключения в конфиге диск - cloud выбранный как дефолтный
 */
class StorageCloud
{
    /**
     * Копируем открытый файл в хранилище, получаем размер
     *
     * @param string                          $url    путь к файлу
     * @param \Intervention\Image\Image|mixed $file   файл для записи
     * @param array                           $params параметры для файла
     *
     * @return int
     */
    public static function saveFile(string $url, \Intervention\Image\Image $file, array $params): int
    {
        Storage::cloud()->put($url, $file, $params);

        return Storage::cloud()->size($url);
    }

    /**
     * Копируем файл на облако с удаленного сервера
     *
     * @param string $path   путь к новому файлу
     * @param string $url    путь к исходному файлу
     * @param array  $params параметры сохранения
     *
     * @return int
     */
    public static function copyFile(string $path, string $url, $params = []): int
    {
        Storage::cloud()->put($path, self::loadFileGet($url), $params);

        return Storage::cloud()->size($path);
    }

    /**
     * удаляем директорию с файлами
     *
     * @param string $directory путь к директории
     */
    public static function deleteDirectory(string $directory)
    {
        Storage::cloud()->deleteDirectory($directory);
    }

    /**
     * удаляем директорию с файлами
     *
     * @param string $directory путь к директории или полный путь а файлу
     * @param string $filename  имя файла, который надо удалить
     */
    public static function deleteFile(string $directory, $filename = '')
    {
        Storage::cloud()->delete($directory . $filename);
    }

    /**
     * переименование файла на удаленном сервере
     *
     * @param string $filename_old старое имя файла, включая полный путь к файлу, который надо переименовать
     * @param string $filename_new временно только алиас (новое имя файла, только имя файла, без пути)
     */
    public static function renameFile(string $filename_old, string $filename_new)
    {
        // метод не работает с Selectel - проблемы с апи используем метод renameFileByAlias
        // вместо этого копируем файл и затем удаляем

        Storage::cloud()->move($filename_old, $filename_new);
    }

    /**
     * переименование файла на удаленном сервере
     * копируем файл с новым алиасом, затем удаляем старый файл
     *
     * @param string $filename_old старое имя файла, включая полный путь к файлу, который надо переименовать
     * @param string $alias_new    только новый алиас
     */
    public static function renameFileByAlias(string $filename_old, string $alias_new)
    {
        $search   = Str::is('*-*', $filename_old) ? '-' : '.';
        $name_new = Str::beforeLast($filename_old, '/') . '/' . $alias_new . $search . Str::after($filename_old, $search);
        StorageCloud::copyFile(
            $name_new, config('file.root') . '/' . config('app.name') . '/' .
                     $filename_old
        );
        StorageCloud::deleteFile($filename_old);
    }

    /**
     * загрузка файла c удаленного сервера Get запросом
     *
     * @param string $request_url полный путь к файлу на удаленном сервере
     *
     * @return string
     */
    private static function loadFileGet(string $request_url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        $data_curl = curl_exec($ch);
        curl_close($ch);

        return $data_curl;
    }
}