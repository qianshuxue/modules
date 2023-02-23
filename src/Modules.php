<?php

namespace Outbook\Modules;

use Outbook\Modules\Contracts\Repository;
use Outbook\Modules\Exceptions\ModuleNotFoundException;
use Illuminate\Foundation\Application;

class Modules
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var CurrModule
     */
    protected $currModule;

    /**
     * Create a new Modules instance.
     *
     * @param Application $app
     * @param Repository  $repository
     */
    public function __construct(Application $app, Repository $repository)
    {
        $this->app = $app;
        $this->repository = $repository;
    }

    /**
     * Register the module service provider file from all modules.
     *
     * @return void
     */
    public function register()
    {
        $reflection = new \ReflectionClass($this->app->request->server);
        $property = $reflection->getProperty('parameters');
        $property->setAccessible(true);
        $server = $property->getValue($this->app->request->server);

        if (isset($server['REQUEST_URI'])) {
            $uriArr = explode("?", $server['REQUEST_URI']);
            $uri = $uriArr[0] ?? '';
            $content = @file_get_contents(storage_path('all_route_list.json'));
            if ($content){
                $allRouteList = json_decode($content,true);
                $uri = ltrim($uri,'/');
                if(isset($allRouteList['routes'][$uri])){
                    $this->currModule = $allRouteList['routes'][$uri];
                }else{
                    foreach($allRouteList['preg'] as $r => $m){
                        if (preg_match($r, $uri)){
                            $this->currModule = $m;
                            break;
                        }
                    }
                }
            }
        }

        $modules = $this->repository->enabled();

        $modules->each(function ($module) {
            if (!empty($this->currModule) && $this->currModule != $module['basename']) {
                return;
            }

            try {
                $this->registerServiceProvider($module);

                $this->autoloadFiles($module);
            } catch (ModuleNotFoundException $e) {
                //
            }
        });
    }

    /**
     * Register the module service provider.
     *
     * @param array $module
     *
     * @return void
     */
    private function registerServiceProvider($module)
    {
        $serviceProvider = module_class($module['slug'], 'Providers\\ModuleServiceProvider');

        if (class_exists($serviceProvider)) {
            $this->app->register($serviceProvider);
        }
    }

    /**
     * Autoload custom module files.
     *
     * @param array $module
     *
     * @return void
     */
    private function autoloadFiles($module)
    {
        if (isset($module['autoload'])) {
            foreach ($module['autoload'] as $file) {
                $path = module_path($module['slug'], $file);

                if (file_exists($path)) {
                    include $path;
                }
            }
        }
    }

    /**
     * Oh sweet sweet magical method.
     *
     * @param string $method
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->repository, $method], $arguments);
    }
}
