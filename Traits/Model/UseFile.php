<?php

namespace Modules\File\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\File\Models\Extension;
use Modules\File\Repositories\TypeFile;
use Modules\File\Services\Path;

/**
 * Trait UseFile
 * Подключаем к моделям, которые работают с файлами
 * Добавляем к модели переменные:
 *      trashed_img
 *      first_img_xs
 *      first_img_md
 *      actual_img
 *
 * @package Modules\File\Traits\Model
 */
trait UseFile
{

    protected array $type_files = [TypeFile::IMAGE];
    /**
     * @return array
     */
    public function getTypeFile(): array
    {
        return $this->type_files ?? [];
    }

    /**
     * все файлы, привязанные к модели
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesAll(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')->withTrashed()->orderBy('order');
    }

    /**
     * только актуальные файлы привязанные к модели, без удаленных
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesActual(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')->orderBy('order');
    }

    /**
     * только удаленные файлы привязанные к модели
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesTrashed(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')->onlyTrashed();
    }

    /**
     * только изображения
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesImageActual(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::IMAGE))
            ->orderBy('order');
    }

    /**
     * только удаленные файлы изображений
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesImageTrashed(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::IMAGE))
            ->onlyTrashed()
            ->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesAudio(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::AUDIO))
            ->orderBy('order');
    }
    /**
     * только версии книги  и аудиофайлы
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesBook(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::BOOK))
            ->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesDocument(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::DOCUMENT))
            ->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesVideo(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::VIDEO))
            ->orderBy('order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function filesPresentation(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany('Modules\File\Models\File', 'fileable')
            ->whereIn('extension_id', Extension::getIdsByType(TypeFile::PRESENTATION))
            ->orderBy('order');
    }

    /**
     * Строим массив путей к актуальным изображениям сущности
     *
     * @return array
     */
    public function getFirstImgAttribute(): array
    {
        return $this->filesImageActual->sortBy('order')->first()->picture ?? $this->getFirstImgDefault();
    }

    /**
     * Строим json массив путей к актуальным изображениям сущности - actual_img
     * для админки
     *
     * @return string json
     */
    public function getActualImgAttribute(): string
    {
        $files = [];
        foreach ($this->filesImageActual as $item)
        {
            if (isset($item->picture['lg']))
            {
                $files[] = config('file.file.path.storage') . ($item->picture['lg']['webp'] ?? $item->picture['lg']['jpg']);
            } elseif (isset($item->picture['sm']))
            {
                $files[] = config('file.file.path.storage') . ($item->picture['sm']['webp'] ?? $item->picture['sm']['jpg']);
            }
        }

        return json_encode($files);
    }

    /**
     * Строим json массив путей к удаленным изображениям сущности - trashed_img
     * для админки
     *
     * @return string json
     */
    public function getTrashedImgAttribute(): ?string
    {
        foreach ($this->filesImageTrashed as $item)
        {
            if (isset($item->picture['lg']))
            {
                $files[] = config('file.file.path.storage') . ($item->picture['lg']['webp'] ?? $item->picture['lg']['jpg']);
            }
        }

        return json_encode($files ?? []);
    }

    /**
     * Возвращаем путь к изображению xs  - first_img_xs
     * используеться в админке
     *
     * @return string
     * @throws \Exception
     */
    public function getFirstImgXsAttribute(): string
    {
        return $this->firstImgPath('xs');
    }

    /**
     * Возвращаем путь к изображению md  - first_img_md
     * используеться в админке
     *
     * @return string|null
     * @throws \Exception
     */
    public function getFirstImgMdAttribute(): ?string
    {
        return $this->firstImgPath('md');
    }

    /**
     * возвращаем путь к первомому активному изображению сущности, размером $prefix (lg, sm, md, xs)
     *
     * @param string|null $prefix
     *
     * @return string
     * @throws \Exception
     */
    private function firstImgPath($prefix = null): ?string
    {
        $path = Path::pathFileByPrefix(
                $this->filesImageActual->sortBy('order')->first(), $prefix
            ) ?? null;
        $url  = null;
        if ($path)
        {
            $url = config('file.path.storage') . $path;
        } else
        {
            $path = 'images/plugs/' . $this->getModelNameLower($this) . '/' . $prefix . '.webp';
            if (file_exists($path))
            {
                $url = mix($path);
            }
        }

        return $url;
    }

    /**
     * строим пути к заглушкам первого изображения сущности
     * в моделях переопределяем метод
     *
     * @return array
     */
    protected function getFirstImgDefault(): array
    {
        return [
            "xs" => [
                "jpg"    => '',
                "height" => 0,
                "width"  => 0,
                "webp"   => '',
            ],
            "sm" => [
                "jpg"    => '',
                "height" => 0,
                "width"  => 0,
                "webp"   => '',
            ],
            "md" => [
                "jpg"    => '',
                "height" => 0,
                "width"  => 0,
                "webp"   => '',
            ],
            "lg" => [
                "jpg"    => '',
                "height" => 0,
                "width"  => 0,
                "webp"   => '',
            ],
        ];
    }

    /**
     * Возвращаем имя модели в нижнем регистре по коллекции модели
     *
     * @param Model $model
     *
     * @return string
     */
    private function getModelNameLower(Model $model): string
    {
        return Str::snake($this->getModelName($model));
    }

    /**
     * Возвращаем имя модели по коллекции модели
     *
     * @param Model $model
     *
     * @return string
     */
    private function getModelName(Model $model): string
    {
        return class_basename($model->getMorphClass());
    }
}
