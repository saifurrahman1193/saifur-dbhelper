<?php

namespace Saifur\DBHelper\app\Facades\Helpers;

use Illuminate\Support\Facades\Facade;

class SDBHCommonFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sdbhcommonhelper';
    }
}
