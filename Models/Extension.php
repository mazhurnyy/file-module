<?php

namespace Modules\File\Models;

use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\File\Repositories\TypeFile;

/**
 * Class Extension
 *
 * @property string          $name
 * @property string          $note
 * @property string          $mime
 * @property File            files
 * @property int             $id
 * @property-read int|null   $files_count
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Extension newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Extension newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Extension query()
 * @method static \Illuminate\Database\Eloquent\Builder|Extension whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Extension whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Extension whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Extension whereNote($value)
 * @package Modules\File\Models
 */
class Extension extends Model
{
    /**
     * @var string
     */
    protected $table = 'extensions';

    /**
     * @var array
     */
    protected $fillable = ['name', 'note', 'mime'];
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('Modules\File\Models\File');
    }

    /*
    | -----------------------------------------------------------------------------------------------------------------
    | СТАНДАРТНЫЕ ЗАПРОСЫ К БАЗЕ ДАННЫХ
    |----------------------------------------------------------------------------------------------------------------
    */
    /**
     * получаем массив ID расширений файлов указанного типа
     * список типов файлов и соответствующих расширений в
     * Доступные типы
     * TypeFile::IMAGE
     * TypeFile::BOOK
     *  если тип не указан, возвращаем все ид файлов
     *
     * @param string $type
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getIdsByType(string $type): \Illuminate\Support\Collection
    {
        return Cache::tags(['extensions'])->rememberForever(
            'extensions_' . $type, function () use ($type)
            {
            return self::whereIn('name', TypeFile::getExtensions($type))->get()->pluck('id');
            }
        );
    }

    /**
     * возвращаем расширеня файлов по типу
     *
     * @param string $type
     *
     * @return Collection
     */
    public static function getByType(string $type): Collection
    {
        return Cache::tags(['extensions'])->rememberForever(
            'extensions_type_' . $type, function () use ($type)
            {
            return self::whereIn('name', TypeFile::getExtensions($type))->get();
            }
        );
    }

    /**
     * Находим объект Extension по $name (расширение файла)
     *
     * @param string $name
     *
     * @return Extension|null
     */
    public static function findByName(string $name): ?Extension
    {
        return self::whereName($name)->first();
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public static function getNameById(int $id): string
    {
        return Cache::tags(['extensions'])->rememberForever(
            'extensions_' . $id, function () use ($id)
            {
            return self::find($id)->name ?? '';
            }
        );
    }

    /**
     * @param string $extensions
     *
     * @return string
     */
    public static function getMimeByName(string $extensions): string
    {
        return Cache::tags(['extensions'])->rememberForever(
            'extensions_' . $extensions, function () use ($extensions)
            {
            return self::whereName($extensions)->first()->mime ?? '';
            }
        );
    }
}