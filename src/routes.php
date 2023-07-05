<?php

use Illuminate\Support\Facades\Route;
use Saifur\DBHelper\app\Http\Controllers\DBBackupController;

Route::group(['prefix' => 'saifur/db-helper/db-backup', 'middleware'=>'auth:api'], function (){

    Route::post('server-db-structure-backup', [DBBackupController::class, 'serverDBStructureBackup']);
    Route::post('server-db-data-backup', [DBBackupController::class, 'serverDBDataBackup']);
    Route::post('server-db-status', [DBBackupController::class, 'serverDBStatus']);

});
