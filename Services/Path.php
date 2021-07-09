<?php

namespace Modules\File\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\File\Models\Extension;
use Modules\File\Models\File;
use Modules\File\Traits\Prefixes;

/**
 * Class Path
 * формируем пути к файлам, генерируем элементы для пути, собираем путь к файлу
 *
 * @package Modules\File\Services
 */
class Path
{
    use Prefixes;

    /**
     * Находим путь к файлу по модели файла и префиксу
     *
     * @param File|null   $file
     * @param string|null $prefix
     *
     * @return string|null
     */
    public static function pathFileByPrefix(?File $file, $prefix = null): ?string
    {
        return $file ? self::getTokenPath(
                $file->token
            ) . $file->token . '/' . $file->alias . self::getPrefixFile($file, $prefix) . '.' . Extension::getNameById(
                $file->extension_id
            ) : null;
    }

    /**
     * формируем часть пути к файлу, получаемыю по токену
     *
     * @param string $token
     *
     * @return string
     */
    public static function getPathByToken(string $token): string
    {
        return '/' . substr($token, 0, 2) . '/' . substr($token, 2, 2) . '/' . substr($token, 4, 2) . '/';
    }

    /**
     * возвращаем путь к файлу на диске
     *
     * @param string $token
     * @param Model  $model
     *
     * @return string
     */
    public static function getPathDisk(string $token, Model $model): string
    {
        return self::getPathByToken($token) . $token . '/' . $model->alias;
    }

    /**
     * путь к файлу на удаленном сервере
     *
     * @param File $file
     *
     * @return string
     */
    public static function getUrlFileRoot(File $file): string
    {
        return config('file.path.storage') . self::getPath($file);
    }

    /**
     * возвращаем путь (каталоги) к файлу по токену
     *
     * @param string $token
     *
     * @return string
     */
    private static function getTokenPath(string $token): string
    {
        return '/' . substr($token, 0, 2) . '/' . substr($token, 2, 2) . '/' . substr($token, 4, 2) . '/';
    }

    /**
     * Находим путь к файлу
     *
     * @param File $file
     * @param null $alias_prefix
     *
     * @return string
     */
    public static function getPath(File $file, $alias_prefix = null): string
    {
        return Path::getPathByToken(
                $file->token
            ) . $file->token . '/' . $file->alias . self::getPrefixFile($file, $alias_prefix) . '.' . Extension::getNameById
            (
                $file->extension_id
            );
    }

    /**
     * полный путь к файлу картинок
     *
     * @param string      $token
     * @param string|null $alias
     * @param string      $extension имя расширения файла
     * @param null        $alias_prefix
     *
     * @return string
     */
    public static function getUrlImages(string $token, ?string $alias, string $extension, $alias_prefix = null): string
    {
        return config('file.path.storage') . Path::getPathByToken(
                $token
            ) . $token . '/' . $alias . '-' . $alias_prefix . '.' . $extension;
    }

    /**
     * получаем префикс к файлу по его алиасу
     *
     * @param File $file
     * @param null $alias_prefix
     *
     * @return string|null
     */

    private static function getPrefixFile(File $file, $alias_prefix = null): ?string
    {
        $prefix = self::getPrefix($file->pivot->fileable_type, $alias_prefix);

        return $prefix ? '-' . $prefix : null;

    }
}