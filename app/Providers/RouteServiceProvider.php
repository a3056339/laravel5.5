<?php

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapWebRoutes($router);

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {

        $router->group(['namespace' => 'App\Http\Controllers'], function ($app) {
            require base_path('app/Http/routes.php');
        });

        //API路由
        $router->group(['namespace' => 'App\Http\Api'], function ($app) {
            require base_path('app/Http/Api/routes.php');
        });

        //总管理后台路由
        $router->group(['namespace' => 'App\Http\Admin','prefix'=>'admin'], function ($app) {
            require base_path('app/Http/Admin/routes.php');
        });

    }
}
