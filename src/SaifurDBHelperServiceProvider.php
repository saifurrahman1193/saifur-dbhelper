<?php

namespace Saifur\DBHelper;

use Illuminate\Support\ServiceProvider;
use Saifur\DBHelper\app\Facades\DBBackup\DBBackupHelper;

class SaifurDBHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('dbbackuphelper', function () {  return new DBBackupHelper();   });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'dbhelper');
    }
}
