<?php

namespace Outbook\Modules\Providers;

use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $generators = [
            'command.make.module'            => \Outbook\Modules\Console\Generators\MakeModuleCommand::class,
            'command.make.module.controller' => \Outbook\Modules\Console\Generators\MakeControllerCommand::class,
            'command.make.module.middleware' => \Outbook\Modules\Console\Generators\MakeMiddlewareCommand::class,
            'command.make.module.migration'  => \Outbook\Modules\Console\Generators\MakeMigrationCommand::class,
            'command.make.module.model'      => \Outbook\Modules\Console\Generators\MakeModelCommand::class,
            'command.make.module.policy'     => \Outbook\Modules\Console\Generators\MakePolicyCommand::class,
            'command.make.module.provider'   => \Outbook\Modules\Console\Generators\MakeProviderCommand::class,
            'command.make.module.request'    => \Outbook\Modules\Console\Generators\MakeRequestCommand::class,
            'command.make.module.seeder'     => \Outbook\Modules\Console\Generators\MakeSeederCommand::class,
            'command.make.module.test'       => \Outbook\Modules\Console\Generators\MakeTestCommand::class,
        ];

        foreach ($generators as $slug => $class) {
            $this->app->singleton($slug, function ($app) use ($slug, $class) {
                return $app[$class];
            });

            $this->commands($slug);
        }
    }
}
