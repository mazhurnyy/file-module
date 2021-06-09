<?php

namespace Modules\File\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\File\Events\FileableDeleting;
use Modules\File\Events\FileableSaved;

/**
 * Class Fileable
 *
 * @property int                                $file_id     ID файла
 * @property int                                $fileable_id ID сущности
 * @property string                             $fileable_type
 * @property-read \Modules\File\Models\File     $file
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\Fileable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\Fileable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\Fileable query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\Fileable whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\Fileable whereFileableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\File\Models\Fileable whereFileableType($value)
 * @property-read \Modules\Setting\Models\Table $tables
 * @package Modules\File\Models
 * @mixin \Eloquent
 */
class Fileable extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'fileables';

    protected $primaryKey = 'file_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $dispatchesEvents = [
        'saved'    => FileableSaved::class,
        'deleting' => FileableDeleting::class,
    ];

    protected $fillable = ['file_id', 'fileable_id', 'fileable_type'];

    /**
     * @return mixed
     */
    public function file()
    {
        return $this->belongsTo(File::class)->withTrashed();
    }

    /*
    | -----------------------------------------------------------------------------------------------------------------
    | СТАНДАРТНЫЕ ЗАПРОСЫ К БАЗЕ ДАННЫХ
    |----------------------------------------------------------------------------------------------------------------
    */
    /**
     * Нахолдим IDS файлов сущности по $fileable_id и $model_path
     *
     * @param int    $fileable_id
     * @param string $model_path
     *
     * @return array
     */
    public static function getFileablesFileIds(int $fileable_id, string $model_path): array
    {
        return self::select('file_id')->whereFileableId($fileable_id)->whereFileableType($model_path)
                ->get()->pluck('file_id')->toArray() ?? [];
    }
}
