<?php

namespace Modules\File\Providers;

use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'File';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'file';
    /**
     * @var array
     */
    protected $sections = [
        //
    ];
    protected $policies = [];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     *
     * @return void
     */
    public function boot()
    {
        $this->projectRegisterTranslations();
        $this->projectRegisterConfig();
        $this->projectRegisterViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        if(class_exists(SleepingOwl\Admin\Admin::class)) {
            (new SleepingOwl\Admin\Admin())->registerSections($this->sections);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->commands(
            [
                \Modules\File\Console\CheckDirStorage::class,
                \Modules\File\Console\CleaningFile::class
            ]
        );
    }

    /**
     * Регистрируем конфиги модулей
     * для подмены стандартных значений, копируеться исходный файл и подменяються значения
     */
    protected function projectRegisterConfig()
    {
        $this->publishes(
            [
                module_path($this->moduleName, 'config.php') => config_path($this->moduleNameLower . '.php'),
            ], 'config'
        );
        $projectConfig = resource_path(
            'projects/' . config('app.name') . '/modules/' . $this->moduleNameLower . '/config.php'
        );
        if (file_exists($projectConfig))
        {
            $this->mergeConfigFrom($projectConfig, $this->moduleNameLower);
        }
        $this->mergeConfigFrom(module_path($this->moduleName, 'config.php'), $this->moduleNameLower);
    }

    /**
     * Регистрируем, отличные от стандартных, шаблоны модуля
     */
    protected function projectRegisterViews()
    {
        //
    }

    /**
     * Регистрируем файлы локализации модуля
     * для подмены стандартных значений, копируеться исходный файл и подменяються значения
     */
    protected function projectRegisterTranslations()
    {
        $langPath = resource_path('projects/' . config('app.name') . '/modules/' . $this->moduleNameLower . '/lang');

        if (is_dir($langPath))
        {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else
        {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (!app()->environment('production'))
        {
            //           app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
