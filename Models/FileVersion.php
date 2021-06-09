<?php

namespace Modules\File\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FileVersion
 * Версии файлов в разных форматах, для картинок файл с разными размерами
 *
 * @property int                             $id           ID
 * @property int                             $file_id
 * @property int                             $size
 * @property string                          $prefix
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int                             $extension_id ID расширения файла
 * @property int                             $height       Высота изобразения
 * @property int                             $width        Ширина изобразения
 * @property-read \Modules\File\Models\File  $file
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion newQuery()
 * @method static \Illuminate\Database\Query\Builder|\Modules\File\Models\FileVersion onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereExtensionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\FileVersion whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\File\Models\FileVersion withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\File\Models\FileVersion withoutTrashed()
 * @mixin \Eloquent
 * @package Modules\File\Models
 */
class FileVersion extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'file_versions';

    public $timestamps = false;

    protected $fillable = ['file_id', 'prefix', 'size', 'height', 'width', 'extension_id', 'deleted_at'];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * находим все версии файлов по $file_id и $extension_id
     *
     * @param int $file_id
     * @param int $extension_id
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getByFileIdExtensionId(int $file_id, int $extension_id)
    {
        return self::whereFileId($file_id)
            ->whereExtensionId($extension_id)
            ->get();
    }
}