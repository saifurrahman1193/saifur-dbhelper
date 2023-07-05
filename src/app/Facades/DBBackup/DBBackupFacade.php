<?php

namespace Saifur\DBHelper\app\Facades\DBBackup;

use Illuminate\Support\Facades\Facade;

class DBBackupFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dbbackuphelper';
    }
}
