<?php

namespace App\Providers;

use Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 记录sql操作日历
        DB::listen(function ($sql) {

            if (env('APP_ENV') == 'product' || $sql->time > 1000) {
                foreach ($sql->bindings as $i => $binding) {
                    if ($binding instanceof \DateTime) {
                        $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                    } else {
                        if (is_string($binding)) {
                            $sql->bindings[$i] = "'$binding'";
                        }
                    }
                }

                // Insert bindings into query
                $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

                $query = vsprintf($query, $sql->bindings);
                @file_put_contents(storage_path('logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_query.log'),
                    date('Y-m-d H:i:s') . ': ' . $query . ' 【耗时：' . $sql->time . 'ms】' . PHP_EOL,
                    FILE_APPEND);
            }
        });
    }


}
