<?php

use Illuminate\Support\Facades\Route;
use Saifur\LogViewer\app\Http\Controllers\LogController;


Route::group(['prefix' => 'saifur/log-viewer'],function (){
    Route::post('login', [LogController::class, 'login'])->middleware("throttle:50,5");
    Route::get('log-list', [LogController::class, 'logList']);


    Route::group(['middleware' => 'SaifurLogViewerMiddleware'],function (){

        Route::post('log-files-list-data', [LogController::class, 'logFilesListData']);
        Route::post('single-log-file-data', [LogController::class, 'singleLogFileData']);
        Route::post('log-file-download', [LogController::class, 'logFileDownload']);
        Route::post('log-file-delete', [LogController::class, 'logFileDelete']);
        Route::post('log-file-delete-multiple', [LogController::class, 'logFileDeleteMultiple']);
    });

});
