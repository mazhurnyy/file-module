<?php

namespace Modules\File\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\File\Models\File;
use Modules\File\Services\StorageCloud;

/**
 * Class CleaningFile
 * Очиска просроченных, удаленных файлов
 *
 * @package Modules\File\Console
 */
class CleaningFile extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cleaning:file';

    /**
     * @var object контейнер хранения файлов
     */
    protected $container;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleaning Delete File';

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
        (new \Modules\File\Jobs\CleaningFile([
            'delete_data' => Carbon::now()->subMinutes(10),
            'limit' => 50
        ]))->handle();

    }

}