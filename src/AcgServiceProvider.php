<?php

namespace Marcha\Acg;

use Illuminate\Support\ServiceProvider;

class AcgServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //$this->registerContainerCreator();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {        
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CreateContainer::class,
                Commands\CreateContainers::class,
                Commands\CreateTransformer::class,
            ]);
        } 
    }

    /**
     * Register the make:migration generator.
     */
    private function registerContainerCreator()
    {
        $this->app->singleton('command.marcha.create-container', function ($app) {
            return $app['Marcha\Acg\Commands\CreateContainer'];
        });
       
        $this->commands('command.marcha.create-container');
    }

}
