<?php

namespace Modules\File\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\File\Models\File;
use Modules\File\Services\StorageCloud;

/**
 * Class CheckDirStorage
 * Проверка директорий на удаленном хранилище
 * перебираем все директории на удаленном хранилище и сравниваем токен директории с таблицей в БД
 * если записи в базе нет - битые файлы - удаляем всю директорию
 *
 * @package Modules\File\Console
 */
class CheckDirStorage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:check_directory';

    /**
     * @var object контейнер хранения файлов
     */
    protected $container;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking directories on Storage File';

    /**
     * @var string
     */
    private string $file_path = 'check_directory.txt';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        for ($i = 0; $i < 1; $i++)
        {
            $startDir    = $this->startDir();
            $directories = Storage::cloud()->allDirectories($startDir);

            $this->checkToken($directories);
            $this->saveDir($startDir);
        }
    }

    /**
     * проверяем полученние токены с пути к директории
     *
     * @param array $directories
     */
    private function checkToken(array $directories)
    {
        foreach ($directories as $directory)
        {
            if (substr_count($directory, '/') === 3)
            {
                if (File::isTokenFile(Str::afterLast($directory, '/')))
                {
                    StorageCloud::deleteDirectory($directory);
                }
            }
        }
    }

    /**
     * Находим стартовую директорию
     *
     * @return string
     */
    private function startDir(): string
    {
        $value = '255/255';
        try
        {
            $value = Storage::disk('storage')->get($this->file_path);

        } catch (FileNotFoundException $e)
        {
            $this->saveDir($value);
        }

        [$d1, $d2] = explode('/', $value);
        $d2 = (int)($d2) + 1;
        $d1 = $d2 === 256 ? (int)($d1) + 1 : (int)($d1);

        return self::convertDexHex($d1) . '/' . self::convertDexHex($d2);
    }

    /**
     * сохранием путь к  отработанной директории во временной таблице
     */
    /**
     * @param string $startDir
     */
    private function saveDir(string $startDir)
    {
        [$d1, $d2] = explode('/', $startDir);
        Storage::disk('storage')->put($this->file_path, hexdec($d1) . '/' . hexdec($d2));
    }

    /**
     * переводим число с десятичного значения в шестнадцетиричный
     * если число больше 255 - возвращаем 0
     * бополняем 0 до двух знаков
     *
     * @param int $dex
     *
     * @return string
     */
    private static function convertDexHex(int $dex): string
    {
        $dex = dechex($dex < 256 ? $dex : 0);
        return (Str::length($dex) === 1) ? '0' . $dex : $dex;
    }
}