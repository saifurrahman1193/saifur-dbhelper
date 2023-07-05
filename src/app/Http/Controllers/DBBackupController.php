<?php

namespace Saifur\DBHelper\app\Http\Controllers;

use ZipArchive;
use Illuminate\Support\Str;
use Saifur\DBHelper\app\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Saifur\DBHelper\app\Facades\DBBackup\DBBackupFacade;

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

            $all_table_create_statement =  DBBackupFacade::all_table_create_statement(['database'=>$database, 'newline' => '<newline><newline>']);
            $all_view_create_statement =  DBBackupFacade::all_view_create_statement(['database'=>$database, 'newline' => '<newline><newline>']);
            $all_procedure_create_statement =  DBBackupFacade::all_procedure_function_create_statement(['database'=>$database, 'newline' => '<newline><newline>', 'ROUTINE_TYPE' => 'PROCEDURE']);
            $all_function_create_statement =  DBBackupFacade::all_procedure_function_create_statement(['database'=>$database, 'newline' => '<newline><newline>', 'ROUTINE_TYPE' => 'FUNCTION']);


            $data = '-- DB: '.$database.' Database Backup Generated time = '.YmdTodmYPm(getNow()).
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
        $file_name = 'db_backup_'.Str::slug(getNow(), '_').'.sql';
        $file_full_url = $dir.$file_name;
        $file = fopen($file_full_url, 'w');

        $databaseName = DB::getDatabaseName();
        $table_names = DB::table('information_schema.tables')
                        ->where('table_schema', $databaseName)
                        ->where('TABLE_TYPE', 'BASE TABLE')
                        // ->where('table_name', 'users')  // later remove
                        ->when($request->filled('except_tables') && count($request->except_tables), function ($q){
                            $q->whereNotIn('table_name', request('except_tables'));
                        })
                        ->pluck('table_name')
                        ->toArray();



        // Loop through the tables and export data into files
        foreach ($table_names as $table_name)
        {
            $data = DB::table($table_name) // getting a table dataset using select
                    ->when($request->filled('table_rules') && count($request->table_rules), function ($q) use($request, $table_name)  // executing all rules
                    {
                        foreach ($request->table_rules as $key => $rule)
                        {
                            if (isset($rule['table_name']) && $rule['table_name']==$table_name && isset($rule['row_limit']))   // for table row limit
                            {
                                $q->limit($rule['row_limit']);
                            }

                            if (isset($rule['table_name']) && $rule['table_name']==$table_name && isset($rule['order_by']) && isset($rule['order_type']))   // for table order by
                            {
                                $q->orderBy($rule['order_by'], $rule['order_type']);
                            }
                        }
                    })
                    ->get();

            if ($data->count() > 0)
            {
                fwrite($file, PHP_EOL.PHP_EOL.'-- ==================Table: '.$table_name.'================== '.PHP_EOL.PHP_EOL);

                foreach ($data as $row)
                {
                    $insert = "INSERT INTO `$table_name` (";
                    $values = "VALUES (";

                    foreach ($row as $column => $value)
                    {
                        $insert .= "`$column`, ";
                        $values .= "'" . addslashes($value) . "', ";
                    }

                    $insert = rtrim($insert, ', ') . ")";
                    $values = rtrim($values, ', ') . ");\n";

                    fwrite($file, $insert . " " . $values);
                }
            }
        }
        fclose($file); // close the file

        $zip_file_full_url = substr($file_full_url, 0, -3).'zip'; // zip full url
        $zip_file_name = substr($file_name, 0, -3).'zip'; // zip file name
        $zip = new ZipArchive();
        $zip->open($zip_file_full_url, ZipArchive::CREATE); // open the zip archive
        $zip->addFile($file_full_url, $file_name); // add file to zip archive
        $zip->close(); // close the file

        File::delete($file_full_url);  // after zip delete the sql file

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
            $tableinfo['dataSize_m'] = convertDataSize($dataSize);
            $indexSize = DB::select("SELECT index_length FROM information_schema.TABLES WHERE table_schema = '$databaseName' AND table_name = '$table'")[0]->index_length;
            $tableinfo['indexSize'] = $indexSize;
            $tableinfo['indexSize_m'] = convertDataSize($indexSize);
            $totalSize = DB::select("SELECT (data_length + index_length) AS total_size FROM information_schema.TABLES WHERE table_schema = '$databaseName' AND table_name = '$table'")[0]->total_size;
            $tableinfo['totalSize'] = $totalSize;
            $tableinfo['totalSize_m'] = convertDataSize($totalSize);

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
                    'total_size_m' => convertDataSize(array_sum(array_column($tables, 'totalSize'))),
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
