<?php

use Illuminate\Support\Facades\Route;
use Saifur\DBHelper\app\Http\Controllers\DBBackupController;

Route::group(['prefix' => 'saifur/db-helper/db-backup'], function (){

    Route::post('server-db-structure-backup', [DBBackupController::class, 'serverDBStructureBackup']);
    Route::post('server-db-data-backup', [DBBackupController::class, 'serverDBDataBackup']);
    Route::post('server-db-full-backup', [DBBackupController::class, 'serverDBFullBackup']);
    Route::post('server-db-status', [DBBackupController::class, 'serverDBStatus']);

});
