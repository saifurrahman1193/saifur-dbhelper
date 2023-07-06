<?php

namespace Saifur\DBHelper;

use Illuminate\Support\ServiceProvider;
use Saifur\DBHelper\app\Facades\Helpers\SDBHCommonHelper;
use Saifur\DBHelper\app\Facades\DBBackup\SDBHDBBackupHelper;

class SaifurDBHelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Facades Registration
        $this->app->bind('sdbhcommonhelper', function () {  return new SDBHCommonHelper();   });
        $this->app->bind('sdbhdbbackuphelper', function () {  return new SDBHDBBackupHelper();   });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'dbhelper');
        require_once __DIR__.'/app/Libraries/Helpers.php';
    }
}
