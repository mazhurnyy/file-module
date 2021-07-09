<?php

namespace Modules\File\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\File\Events\FileDeleted;
use Modules\File\Events\FileRestored;
use Modules\File\Events\FileSaved;
use Modules\File\Services\Path;
use Modules\File\Traits\Prefixes;

/**
 * Class File
 *
 * @property string                                             $token
 * @property string                                             $name
 * @property string                                             $alias
 * @property int                                                $extension_id
 * @property int                                                $order
 * @property int                                                $size
 * @property string                                             $created_at
 * @property int                                                $id
 * @property \Illuminate\Support\Carbon|null                    $deleted_at
 * @property-read \Modules\File\Models\Extension                $extension
 * @property-read \Modules\File\Models\Fileable                 $file
 * @property-read \Illuminate\Database\Eloquent\Collection      $fileVersion
 * @property-read int|null                                      $file_version_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $filetables
 * @property-read \Illuminate\Database\Eloquent\Collection      $gallery
 * @property-read int|null                                      $gallery_count
 * @property-read string                                        $original_file
 * @property-read string                                        $original_path
 * @property-read array                                         $picture
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File newQuery()
 * @method static \Illuminate\Database\Query\Builder|\Modules\File\Models\File onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File order()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereExtensionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereToken($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\File\Models\File withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\File\Models\File withoutTrashed()
 * @mixin \Eloquent
 * @property string|null                                        $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\File whereUpdatedAt($value)
 * @property string|null                                        $link Ссылка
 * @method static \Illuminate\Database\Eloquent\Builder|File whereLink($value)
 * @package Modules\File\Models
 */
class File extends Model
{
    use  SoftDeletes, Prefixes;

    protected $table = 'files';

    public $timestamps = false;

    protected $dispatchesEvents = [
        'saved'    => FileSaved::class,
        'deleted'  => FileDeleted::class,
        'restored' => FileRestored::class,
    ];

    protected $fillable = [
        'token',
        'name',
        'alias',
        'link',
        'extension_id',
        'order',
        'size',
        'created_at',
        'deleted_at',
    ];

    protected $appends = [
        // массив с урлами картинок всех разрешений [xs,sm,md,lg][extension]
        'picture',
        'original_file',
        'original_path',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function filetables(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function file(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne('Modules\File\Models\Fileable', 'file_id', 'id');
    }

    /**
     * версии изображения
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fileVersion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('Modules\File\Models\FileVersion', 'file_id', 'id')->orderBy('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function extension(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('Modules\File\Models\Extension')->orderBy('id');
    }

    /**
     * полный путь к оригиналу файла
     *
     * @return string
     */
    public function getOriginalFileAttribute(): string
    {
        return Path::getUrlFileRoot($this);
    }

    /**
     * относительный путь к файлу
     *
     * @return string
     */
    public function getOriginalPathAttribute(): string
    {
        return config('file.path.storage') . Path::getPath($this);
    }

    /**
     * возвращаем массив с урлами всех разрешений [xs,sm,md,lg][extension]
     *
     * @return array
     */
    public function getPictureAttribute(): array
    {
        if (isset($this->pivot->fileable_type))
        {
            $extensions = Extension::getByType('images');
            foreach ($this->getPrefixes($this->pivot->fileable_type) as $key => $value)
            {

                foreach ($extensions as $extension)
                {
                    foreach ($this->fileVersion as $item)
                    {
                        try
                        {
                            if ($item['prefix'] == $value['prefix'] && $item['extension_id'] == $extension->id)
                            {
                                $images[$key][$extension->name] = Path::getUrlImages(
                                    $this->token, $this->alias, $extension->name, $value['prefix']
                                );
                                $images[$key]['height']         = $item['height'];
                                $images[$key]['width']          = $item['width'];
                                break;
                            }
                        } catch (\Throwable $throwable)
                        {
                            //
                        }
                    }
                }
            }
        }

        return $images ?? [];
    }

    /**
     * сортировки в запрос
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOrder(Builder $query): Builder
    {
        return $query->orderBy('order', 'asc');
    }

    /*
    | -----------------------------------------------------------------------------------------------------------------
    | СТАНДАРТНЫЕ ЗАПРОСЫ К БАЗЕ ДАННЫХ
    |----------------------------------------------------------------------------------------------------------------
    */
    /**
     * Находим файл по токену
     *
     * @param string $token
     *
     * @return File|null
     */
    public static function findByToken(string $token): ?File
    {
        return self::whereToken($token)->first();
    }

    /**
     * Находим удаленные файлы
     *
     * @param string $token
     *
     * @return File
     */
    public static function findTrashedByToken(string $token): File
    {
        return self::withTrashed()->whereToken($token)->first();
    }

    /**
     * Проверка, есть ли запись о файле с токеном
     * true - токен используеться, false - не занят
     *
     * @param string $token
     *
     * @return bool true - токен используеться, false - не занят
     */
    public static function isTokenFile(string $token): bool
    {
        return !is_null(self::withTrashed()->whereToken($token)->first());
    }
}
