# Модуль работы с хранилищем данных

работает с хранилищами, поддерживающими S3 Amazon стандарт

## Установка

Перед началом установки пакета, в проекте должны быть установлены пакеты

composer require nwidart/laravel-modules
и
composer require joshbrw/laravel-module-installer

"nwidart/laravel-modules": "^8.2",

"joshbrw/laravel-module-installer": "^2.0"

затем устанивить сам модуль 

composer require mazhurnyy/file-module


## Добавить зависимости модуля

запустить

php artisan module:update File

### Добавить в .env

Настройки подключения к зранилищу S3

AWS_KEY=homestead

AWS_SECRET=secretkey

AWS_REGION=us-east-1

AWS_BUCKET=project

AWS_URL=http://homestead:9600

### Пути к месту хранения файлов,

FILE_ROOT_URL= путь к корню хранилища, для файлов записи проекта, бекапов и тд

FILE_STORAGE_URL= полный путь к хранилищу при чтении файлов проекта


### В моделях, работающих с файлами добавить 

use Modules\File\Traits\Model\UseFile;

добавить возможные типы файлов

protected array $type_files = [TypeFile::IMAGE];