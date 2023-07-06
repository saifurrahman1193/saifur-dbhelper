<?php

namespace Saifur\DBHelper\app\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Saifur\DBHelper\app\Traits\ApiResponser;
use Saifur\DBHelper\app\Facades\Helpers\SDBHCommonFacade;
use Saifur\DBHelper\app\Facades\DBBackup\SDBHDBBackupFacade;

class DBBackupController extends Controller
{
    use ApiResponser;

    public function serverDBStructureBackup()
    {
        try {

            $database = config('app.db');
            $user = config('app.dbuser');
            $pass = config('app.dbpass');
            $host = config('app.dbhost');



            $tableViewsCounts = DB::select('SELECT count(TABLE_NAME) AS TOTALNUMBEROFTABLES FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?', [$database]);
            $tableViewsCounts = $tableViewsCounts[0]->TOTALNUMBEROFTABLES;

            $viewsCounts = DB::select('SELECT count(TABLE_NAME) AS TOTALNUMBEROFVIEWS FROM INFORMATION_SCHEMA.TABLES WHERE  TABLE_TYPE LIKE "VIEW" AND TABLE_SCHEMA = ?', [$database]);
            $viewsCounts = $viewsCounts[0]->TOTALNUMBEROFVIEWS;

            $tablesCount = $tableViewsCounts-$viewsCounts;


            $proceduresCounts = DB::select('SELECT count(TYPE) AS proceduresCounts FROM mysql.proc WHERE  TYPE="PROCEDURE" AND db = ?', [$database]);
            $proceduresCounts = $proceduresCounts[0]->proceduresCounts;

            $functionsCounts = DB::select('SELECT count(TYPE) AS functionsCounts FROM mysql.proc WHERE  TYPE="FUNCTION" AND db = ?', [$database]);
            $functionsCounts = $functionsCounts[0]->functionsCounts;

            $projectURL = url('/');
            $deviceIP = \Request::ip();

            $all_table_create_statement =  SDBHDBBackupFacade::all_table_create_statement(['database'=>$database, 'newline' => '<newline><newline>']);
            $all_view_create_statement =  SDBHDBBackupFacade::all_view_create_statement(['database'=>$database, 'newline' => '<newline><newline>']);
            $all_procedure_create_statement =  SDBHDBBackupFacade::all_procedure_create_statement(['database'=>$database, 'newline' => '<newline><newline>', 'ROUTINE_TYPE' => 'PROCEDURE']);
            $all_function_create_statement =  SDBHDBBackupFacade::all_function_create_statement(['database'=>$database, 'newline' => '<newline><newline>', 'ROUTINE_TYPE' => 'FUNCTION']);


            $data = '-- DB: '.$database.' Database Backup Generated time = '.SDBHCommonFacade::YmdTodmYPm(SDBHCommonFacade::getNow()).
                            '<newline>-- Project URL = '.$projectURL.
                            '<newline>-- Device IP = '.$deviceIP.

                            '<newline><newline>-- =============Objects Counting Start================= '.
                            '<newline>-- =============Objects Counting Start================= '.
                            '<newline>-- Total Tables + Views = '.$tableViewsCounts.
                            '<newline>-- Total Tables = '.$tablesCount.
                            '<newline>-- Total Views = '.$viewsCounts.
                            '<newline>-- Total Procedures = '.$proceduresCounts.
                            '<newline>-- Total Functions = '.$functionsCounts.
                            '<newline>-- =============Objects Counting End================= '.
                            '<newline>-- =============Objects Counting End================= '.

                            '<newline><newline>-- =============Table Structure Start================= '.
                            '<newline>-- =============Table Structure Start================= '.
                            '<newline><newline>SET FOREIGN_KEY_CHECKS=0; '.
                            '<newline>SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";'.
                            '<newline>START TRANSACTION;'.
                            '<newline>SET time_zone = "+06:00";'.
                            '<newline>drop database if exists '.$database.';'.
                            '<newline>CREATE DATABASE IF NOT EXISTS '.$database.' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'.
                            '<newline>use '.$database.';'.

                            '<newline><newline><newline>-- Total Tables = '.$tablesCount.
                            $all_table_create_statement.  // all tables create statement will store here

                            '<newline><newline><newline>SET FOREIGN_KEY_CHECKS=1;'.
                            '<newline>COMMIT;'.
                            '<newline><newline>-- =============Table Structure end================= '.
                            '<newline>-- =============Table Structure end================= '.

                            '<newline><newline>-- =============View Structure Start================= '.
                            '<newline>-- =============View Structure Start================= '.

                            '<newline><newline><newline>-- Total Views = '.$viewsCounts.
                            $all_view_create_statement.  // all views create statement will store here
                            '<newline><newline>-- =============View Structure end================= '.
                            '<newline>-- =============View Structure end================= '.

                            '<newline><newline>-- =============Procedure Structure Start================= '.
                            '<newline>-- =============Procedure Structure Start================= '.
                            '<newline><newline><newline>-- Total Procedures = '.$proceduresCounts.
                            $all_procedure_create_statement.  // all procedures create statement will store here
                            '<newline><newline>-- =============Procedure Structure end================= '.
                            '<newline>-- =============Procedure Structure end================= '.

                            '<newline><newline>-- =============Function Structure Start================= '.
                            '<newline>-- =============Function Structure Start================= '.
                            '<newline><newline><newline>-- Total Functions = '.$functionsCounts.
                            $all_function_create_statement.  // all functions create statement will store here
                            '<newline><newline>-- =============Function Structure end================= '.
                            '<newline>-- =============Function Structure end================= '

                            ;


            return $this->set_response([
                'instructions' => [
                    'search <newline> and then replace with batch enter',
                    'search (backslash n) and then replace with batch enter ',
                    'search (") and then replace with only double quote ',
                    'search (backslash r) and then replace with empty string ',
                ],
                'data' => $data
            ],  200,'success', ['DB Backup']);
        } catch (\Throwable $th) {
        }
    }

    public function serverDBDataBackup(Request $request)  // db data backup only
    {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/uploads/db/backups/';
        File::ensureDirectoryExists($dir);
        $file_name = 'db_backup_'.Str::slug(SDBHCommonFacade::getNow(), '_').'.sql';
        $file_full_url = $dir.$file_name;

        $data_arr = SDBHDBBackupFacade::db_all_table_data_backup_process(['request' => $request, 'file_name' => $file_name, 'dir' => $dir]);

        $zip_file_full_url = substr($file_full_url, 0, -3).'zip'; // zip full url
        $zip_file_name = substr($file_name, 0, -3).'zip'; // zip file name
        $zip = new ZipArchive();
        $zip->open($zip_file_full_url, ZipArchive::CREATE); // open the zip archive
        $zip->addFile($data_arr['file_full_url'], $data_arr['file_name']); // add data sql file to zip archive
        $zip->close(); // close the file

        File::delete($data_arr['file_full_url']);  // after zip delete the sql file

        return Response::download($zip_file_full_url, $zip_file_name, [ // download the zip file
            'Content-Type' => 'application/zip',
        ]);
    }

    public function serverDBFullBackup(Request $request)  // db data+structure backup fully
    {
        // Init process
        $dir = $_SERVER['DOCUMENT_ROOT'].'/uploads/db/backups/';
        File::ensureDirectoryExists($dir);
        $file_name_structure = 'db_backup_structure_'.Str::slug(SDBHCommonFacade::getNow(), '_').'.sql';
        $file_name_data = 'db_backup_data_'.Str::slug(SDBHCommonFacade::getNow(), '_').'.sql';

        // Main Process
        $structure_arr = SDBHDBBackupFacade::db_all_table_structure_backup_process(['request' => $request, 'file_name' => $file_name_structure, 'dir' => $dir]);
        $data_arr = SDBHDBBackupFacade::db_all_table_data_backup_process(['request' => $request, 'file_name' => $file_name_data, 'dir' => $dir]);

        // Zip Process
        $zip_file_name = 'db_backup_'.Str::slug(SDBHCommonFacade::getNow(), '_').'.zip'; // zip file name
        $zip_file_full_url = $dir.$zip_file_name; // zip full url
        $zip = new ZipArchive();
        $zip->open($zip_file_full_url, ZipArchive::CREATE); // open the zip archive
        $zip->addFile($structure_arr['file_full_url'], $structure_arr['file_name']); // add structure sql to zip archive
        $zip->addFile($data_arr['file_full_url'], $data_arr['file_name']); // add data sql file to zip archive
        $zip->close(); // close the file

        // Delete Process
        File::delete($structure_arr['file_full_url']);  // after zip delete the structure sql file
        File::delete($data_arr['file_full_url']);  // after zip delete the data sql file

        // Download Process
        return Response::download($zip_file_full_url, $zip_file_name, [ // download the zip file
            'Content-Type' => 'application/zip',
        ]);
    }

    public function serverDBStatus(Request $request)
    {
        $databaseName = DB::getDatabaseName();
        $table_names = DB::table('information_schema.tables')->where('table_schema', $databaseName)->where('TABLE_TYPE', 'BASE TABLE')->pluck('table_name')->toArray();

        $databaseInformation = [];

        $tables = [];
        foreach ($table_names as $table) {
            $tableinfo = [];

            $tableinfo['name'] = $table;
            $tableinfo['engine'] = DB::select("SHOW TABLE STATUS LIKE '$table'")[0]->Engine;
            $tableinfo['rows'] = DB::table($table)->count();
            $dataSize = DB::select("SELECT data_length FROM information_schema.TABLES WHERE table_schema = '$databaseName' AND table_name = '$table'")[0]->data_length;
            $tableinfo['dataSize'] = $dataSize;
            $tableinfo['dataSize_m'] = SDBHCommonFacade::convertDataSize($dataSize);
            $indexSize = DB::select("SELECT index_length FROM information_schema.TABLES WHERE table_schema = '$databaseName' AND table_name = '$table'")[0]->index_length;
            $tableinfo['indexSize'] = $indexSize;
            $tableinfo['indexSize_m'] = SDBHCommonFacade::convertDataSize($indexSize);
            $totalSize = DB::select("SELECT (data_length + index_length) AS total_size FROM information_schema.TABLES WHERE table_schema = '$databaseName' AND table_name = '$table'")[0]->total_size;
            $tableinfo['totalSize'] = $totalSize;
            $tableinfo['totalSize_m'] = SDBHCommonFacade::convertDataSize($totalSize);

            $tables[] = $tableinfo;
        }

        $view_names = DB::table('information_schema.tables')->where('table_schema', $databaseName)->where('TABLE_TYPE', 'VIEW')->pluck('table_name')->toArray();

        $procedure_names = DB::table('mysql.proc')->where('db', $databaseName)->where('TYPE', 'PROCEDURE')->pluck('name')->toArray();

        $function_names = DB::table('mysql.proc')->where('db', $databaseName)->where('TYPE', 'FUNCTION')->pluck('name')->toArray();

        $databaseInformation = [
            'table' => [
                'tables' => $tables,
                'summary' => [
                    'total_size' => array_sum(array_column($tables, 'totalSize')),
                    'total_size_m' => SDBHCommonFacade::convertDataSize(array_sum(array_column($tables, 'totalSize'))),
                ]
            ],
            'view' => [
                'views' => $view_names,
            ],
            'procedure' => [
                'procedures' => $procedure_names,
            ],
            'function' => [
                'functions' => $function_names,
            ]
        ];
        if($request->filled('view') && $request->view=='html') return view('dbhelper::db.db_status', compact('databaseInformation'));

        return $this->set_response($databaseInformation,  200,'success', ['Data']);
    }




}
