<?php

namespace Saifur\DBHelper\app\Facades\DBBackup;

use Illuminate\Support\Facades\Facade;

class SDBHDBBackupFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sdbhdbbackuphelper';
    }
}
