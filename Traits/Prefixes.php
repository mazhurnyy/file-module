<?php

namespace Modules\File\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait Prefixes
 *
 * @package Modules\File\Traits
 */
trait Prefixes
{
    /**
     * Возвращаем настройки изображений модели по пути в модулю
     * для модуля один размер для картинок
     *
     * @param string $model_path путь к модели в модуле
     *
     * @return array
     */
    public static function getPrefixes(string $model_path): array
    {
        // имя модуля
        $module = Str::lower(Str::between($model_path, 'Modules\\', '\Models'));
        // имя модели
        $model = Str::lower(Str::after($model_path, '\Models\\'));
        return config($module . '.prefixes_model.' . $model) ?? [];
    }

    /**
     * @param Model $model
     *
     * @return array
     */
    protected function getPrefixesModel(Model $model): array
    {
        return $this->getPrefixes($model->getMorphClass());
    }

    /**
     * получаем префикс к файлу по его алиасу
     *
     * @param string|null $model_path
     * @param string|null $alias_prefix алиас префикса
     *
     * @return string
     */
    public static function getPrefix(?string $model_path, ?string $alias_prefix): string
    {
        $config = self::getPrefixes($model_path);

        return $config[$alias_prefix]['prefix'] ?? '';
    }
}