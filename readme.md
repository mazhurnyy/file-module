# Модуль работы с хранилищем данных

работает с хранилищами, поддерживающими S3 Amazon стандарт

## Добавить зависимости модуля

запустить
php artisan module:update File

### Добавить в .env

AWS_KEY_S3=homestead
AWS_SECRET_S3=secretkey
AWS_REGION_S3=us-east-1
AWS_BUCKET_S3=project
AWS_URL_S3=http://homestead:9600

FILE_ROOT_URL=
FILE_STORAGE_URL=

### Добавить в  config/filesystems.php 

    /*
    |--------------------------------------------------------------------------
    | Пути к месту хранения файлов, добавить строки в env
    |--------------------------------------------------------------------------
    |
    | FILE_ROOT_URL - путь к корню хранилища, для файлов записи проекта, бекапов и тд
    | FILE_STORAGE_URL - полный путь к хранилищу при чтении файлов проекта
    |
    */
    'file'  => [
        'root'    => env('FILE_ROOT_URL' ,'localhost'),
        'storage' => env('FILE_STORAGE_URL' ,'localhost'),
    ],



### В моделях, работающих с файлами добавить 

use Modules\File\Traits\Model\UseFile;
