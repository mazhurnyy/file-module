<?php

namespace Modules\File\Services;

use Carbon\Carbon;
use GdImage;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Image;
use Modules\File\Models\Extension;
use Modules\File\Models\File;
use Modules\File\Models\Fileable;
use Modules\File\Models\FileVersion;
use Modules\File\Repositories\TypeFile;
use Modules\File\Traits\Prefixes;

/**
 * Class SaveFile
 * методы работы с файлами, запись файлов, запись картинок и тд.
 *
 * @package Modules\File\Services
 */
class SaveFile
{
    use Prefixes;

    /**
     * @var Model  модель уникальной сущности, к которой добавляем файл
     */
    protected Model $model;
    /**
     * @var Extension|null типа расширения файла (расширение, имя, mime)
     */
    protected ?Extension $extension;

    /**
     * @var string уникальный токен для имени файла
     */
    protected string $token;
    /**
     * @var string путь к оригиналу файла
     */
    protected string $path;
    /**
     * @var string расширение файла
     */
    protected string $extension_name;
    /**
     * @var int сортировка
     */
    protected int $order;
    /**
     * @var Image|null
     */
    protected ?Image $binary = null;
    /**
     * @var int|null ID  созданного файла
     */
    protected ?int $file_id = null;

    /**
     * @var Image изображение
     */
    public Image $img;

    /**
     * SaveFile constructor.
     *
     * @param Model  $model          модель уникальной сущности, к которой добавляем файл
     * @param string $path           путь к файлу оригинала, для копирования
     * @param string $extension_name расширение файла
     * @param int    $order          сортировка ( от 1)
     */
    public function __construct(Model $model, string $path, string $extension_name, int $order = 1)
    {
        $this->model          = $model;
        $this->path           = $path;
        $this->extension_name = $extension_name;
        $this->order          = $order;
    }

    /**
     * сохранение всех файлов, кроме изображений
     *
     * @return int размер загруженного файла
     * @throws \Exception
     */
    public function copyFile(): int
    {
        $this->extension = Extension::findByName($this->extension_name);
        $this->token     = $this->generateToken();

        $params = [
            'contentType'        => $this->extension->mime,
            'contentDisposition' => 'attachment',
        ];

        $path = Path::getPathDisk($this->token, $this->model) . '.' . $this->extension_name;
        $size = 0;
        $flag = true;
        $try  = 1;
        while ($flag && $try <= 3):
            try {
                $size = StorageCloud::copyFile($path, $this->path, $params);
                if ($size > 0) {
                    $flag = false;
                }
            } catch (\Throwable $throwable) {
                \Log::error($throwable->getMessage());
                \Log::error($throwable->getTraceAsString());
            }
            $try++;
        endwhile;

        $this->addFileInfo($size);

        return $size;
    }

    /**
     * сохранение файлов изображений, формируем файлы нужных размеров
     * пытаемся выполнить три попытки
     *
     * @return int размер загруженного оригинала файла
     * @throws \Exception
     */
    public function saveFileImage(): int
    {
        $this->token = $this->generateToken();
        $size        = 0;
        $flag        = true;
        $try         = 1;
        $img            = \Image::canvas(1, 1);
        while ($flag && $try <= 3):
            try {
                $img = \Image::make($this->path);
                $flag      = false;//Image migrated successfully
            } catch (\Exception $e) {
                //not throwing  error when exception occurs
            }
            $try++;
        endwhile;

        if ($img) {
            $size = $this->resizePhoto($img);
            unset($img);
        }

        return $size;
    }

    /**
     * сохранияем изображение с формата Base64
     *
     * @param $image_64
     *
     * @throws \Exception
     */
    public function saveBase64($image_64)
    {
        $this->binary = $image_64;
        $this->token = $this->generateToken();

        $this->resizePhoto($this->binary);
    }


    /**
     * генерируем уникальный токен для имени файла
     *
     * @return string
     */
    private function generateToken(): string
    {
        $token = strtoupper(md5(uniqid(rand(), true)));
        if (substr($token, 0, 2) === 'AD' || substr($token, 2, 2) === 'AD' ||
            substr($token, 4, 2) === 'AD') {
            $this->generateToken();
        }

        if (File::isTokenFile($token)) {
            $this->generateToken();
        }

        return $token;
    }

    /**
     * создаем файлы заданных размеров для текущего типа сущности,
     * сохраняем оригинал файла
     *
     * @param GdImage|Image $img
     *
     * @return int - размер созданного файла
     * @throws \Exception
     */
    public function resizePhoto(GdImage|Image $img): int
    {
        $file_extension = ['webp' => 'image/webp'];
        $path           = Path::getPathDisk($this->token, $this->model);
        $size           = $this->saveImage($img, $file_extension, $path, 100);
        $file_extension += [
            'jpg' => 'image/jpeg',
        ];
        $prefix = [];
        foreach ($this->getPrefixesModel($this->model) as $key => $parameters) {
            if ($parameters['quality'] > 0 && !in_array($parameters['prefix'], $prefix)) {
                $prefix[] = $parameters['prefix'];
                $path_prefix = $path . '-' . $parameters['prefix'];
                $img = $this->changeImage($parameters);
                $this->saveImage($img, $file_extension, $path_prefix, $parameters['quality'], $parameters['prefix']);
                unset($img);
            }
        }

        return $size;
    }

    /**
     * подгоняем изображение под заданный размер
     * если высота proportion->height меньше proportion->width - форматируем сначала по высоте, потом по ширине
     * если ширина proportion->width меньше proportion->height - форматируем сначала по ширине, потом по высоте
     * $this->proportions - размеры изображения
     *
     * @param array|null $parameters - параметры изображения (ширина, высота, качество)
     */
    private function changeImage(array $parameters = null): Image
    {
        $img = is_null($this->binary) ? \Image::make($this->path) : $this->binary;
        //    $img =  \Image::make($this->path);

        if ($parameters['width'] < $parameters['height']) {
            $img->widen($parameters['width'], function ($constraint) {
            $constraint->upsize();
            });
            if ($img->height() > $parameters['height']) {
                $img->fit($parameters['height'], $parameters['width'], function ($constraint) {
                $constraint->upsize();
                });
            }
        } else {
            $img->heighten($parameters['height'], function ($constraint) {
            $constraint->upsize();
            });
            if ($img->width() > $parameters['width']) {
                $img->fit($parameters['width'], $parameters['height'], function ($constraint) {
                $constraint->upsize();
                });
            }
        }

        return $img;
    }

    /**
     * @param   $img
     * @param array  $file_extension
     * @param string $path
     * @param int    $quality
     * @param null   $prefix
     *
     * @return int
     * @throws \Exception
     */
    private function saveImage($img, array $file_extension, string $path, int $quality, $prefix = null): int
    {
        $size = 0;
        foreach ($file_extension as $name => $mime) {
            $img->response($name, $quality); // по умолчанию качество 90
            $url    = $path . '.' . $name;
            $params = [
                'contentType'        => $mime,
                'contentDisposition' => 'inline',
            ];
            $size   = StorageCloud::saveFile($url, $img, $params);

            $this->extension = Extension::findByName($name);
            // если это оригинал файла
            if ($prefix === null && $size > 74) {
                $this->file_id = $this->addFileInfo($size);
            } else {
                if (!empty($this->file_id)) {
                    $this->addFileVersion($img, $size, $prefix, $this->file_id);
                }
            }
        }

        return $size;
    }

    /**
     * добавляем запись о файле в базу
     *
     * @param int $size размер файла
     *
     * @return null|int ID созданного файла
     * @throws \Exception
     */
    private function addFileInfo(int $size): ?int
    {
        if ($size > 0) {
            $type = TypeFile::getTypeByName($this->model, $this->extension->name);
            $order = ($this->order == 1) ? $this->model->$type + 1 : $this->order;
            $this->model->$type = $this->model->$type + 1;
            $this->model->save();

            $file_model = File::create(
                [
                    'token'        => $this->token,
                    'size'         => $size,
                    'extension_id' => $this->extension->id,
                    'order'        => $order,
                    'alias'        => $this->model->alias,
                    'created_at'   => Carbon::now(),
                ]
            );
            Fileable::create(
                [
                    'file_id'       => $file_model->id,
                    'fileable_type' => $this->model->getMorphClass(),
                    'fileable_id'   => $this->model->id,
                ]
            );
            if (in_array(class_basename($this->model->getMorphClass()), config('file.one_file'))) {
                $this->deleteOldFile($type);
            }
        }

        return $file_model->id ?? null;
    }

    /**
     * добавляем информацию о версии файла с префиксом для изображений
     *
     * @param   $img
     * @param int    $size    размер файла
     * @param string $prefix
     * @param int    $file_id id оригинального файла
     */
    private function addFileVersion($img, int $size, string $prefix, int $file_id)
    {
        FileVersion::create(
            [
                'file_id'      => $file_id,
                'prefix'       => $prefix,
                'size'         => $size,
                'extension_id' => $this->extension->id,
                'height'       => $img->height(),
                'width'        => $img->width(),
            ]
        );
    }

    /**
     * удаляем старые версии файлов у сущностей, где возможен только один файл с картинкой (version, person, user)
     *
     * @param string $type тип файлов. Доступные типы - images, books, presentation
     *
     * @throws \Exception
     */
    private function deleteOldFile(string $type)
    {
        $files = File::whereIn('extension_id', Extension::getIdsByType($type))
            ->whereHas(
                'file',
                function ($q) {
                return $q->whereFileableId($this->model->id)->whereFileableType(
                    $this->model->getMorphClass()
                );
                }
            )
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($files as $key => $file) {
            if ($key == 0) {
                $file->order = 1;
                $file->save();
            } else {
                foreach (FileVersion::whereFileId($file->id)->get() as $item) {
                    File::destroy($item->id);
                }
                File::destroy($file->id);
            }
        }

        $this->model->$type = 1;
        $this->model->save();
    }
}
