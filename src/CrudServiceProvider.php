<?php

namespace Vtec\Crud;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spatie\QueryBuilder\QueryBuilder;
use Vtec\Crud\Commands\CrudGeneratorCommand;
use Vtec\Crud\Commands\CrudInstallCommand;
use Vtec\Crud\Commands\CrudMakeCommand;
use Vtec\Crud\Commands\UserCreateCommand;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/admin.php' => config_path('admin.php'),
                __DIR__.'/../config/cors.php' => config_path('cors.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/crud'),
            ], 'lang');

            $this->publishes([
                __DIR__.'/../files/docker' => base_path('docker'),
                __DIR__.'/../files/docker-compose.yml' => base_path('docker-compose.yml'),
                __DIR__.'/../files/Dockerfile' => base_path('Dockerfile'),
            ], 'docker');

            $this->publishes([
                __DIR__.'/../files/.php_cs.dist' => base_path('.php_cs.dist'),
            ], 'phpcs');

            $this->commands([
                CrudInstallCommand::class,
                CrudMakeCommand::class,
                CrudGeneratorCommand::class,
                UserCreateCommand::class,
            ]);
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'crud');

        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, auth()->user()->password);
        }, __('crud::validation.mismatch_password'));

        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/', (string) $value);
        }, __('crud::validation.strong_password'));

        QueryBuilder::macro('exportOrPaginate', function () {
            if (request()->get('perPage')) {
                return $this
                    ->paginate(request()->get('perPage'))
                    ->appends(request()->query());
            }

            return $this->get();
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/admin.php', 'admin');
    }
}
