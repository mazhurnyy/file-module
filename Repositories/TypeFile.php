<?php

namespace Modules\File\Repositories;

use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * Class Gender
 * гендерные данные персон
 *
 * @package Modules\Book\Repositories
 */
class TypeFile
{
    const AUDIO        = 'audio';
    const BOOK         = 'books';
    const DOCUMENT     = 'documents';
    const IMAGE        = 'images';
    const VIDEO        = 'video';
    const PRESENTATION = 'presentation';
    const TXT          = 'txt';

    /**
     * Возвращаем список форматов
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getTypeList(): \Illuminate\Support\Collection
    {
        return collect(
            [
                self::AUDIO        => config('file.type.audio') ?? ['mp3'],
                self::BOOK         => config('file.type.books') ?? ['fb2.zip', 'fb2', 'mp3', 'pdf'],
                self::DOCUMENT     => config('file.type.documents') ?? ['doc', 'docx', 'rtf', 'pdf'],
                self::IMAGE        => config('file.type.images') ?? ['jpg', 'webp', 'jpeg', 'gif', 'png', 'bmp'],
                self::VIDEO        => config('file.type.video') ?? ['avi', 'mp4', 'mkv', 'wmv', 'mpeg'],
                self::PRESENTATION => config('file.type.presentations') ?? ['ppt', 'pptx'],
                self::TXT          => config('file.type.txt') ?? ['txt'],
            ]
        );
    }

    /**
     * Находим массив расширений по типу контента
     *
     * @param string $type
     *
     * @return array
     */
    public static function getExtensions(string $type): array
    {
        return self::getTypeList()->get($type);
    }

    /**
     * Находим тип файла по расширению и разрешенным типам модели
     *
     * @param Model|null $model
     * @param string     $name
     *
     * @return string|null
     */
    public static function getTypeByName(?Model $model, string $name): ?string
    {
        foreach ($model->getTypeFile() as $item) {
            if (in_array($name, self::getExtensions($item))) {
                $type = $item;
                break;
            }
        }

        return $type ?? TypeFile::IMAGE;
    }

    /**
     * @return array
     */
    public static function getConstantsAll(): array
    {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     *
     */
    public static function getAliasList()
    {
        // TODO: Implement getAliasList() method.
    }

    /**
     * @param int|string $format
     *
     * @return string|null
     */
    public static function getAlias($format): ?string
    {
        return null;
    }
}
