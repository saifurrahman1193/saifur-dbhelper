<?php

namespace Saifur\DBHelper\app\Facades\DBBackup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Saifur\DBHelper\app\Facades\Helpers\SDBHCommonFacade;


class SDBHDBBackupHelper
{

    public function all_table_create_statement($params=[])
    {
        $database = $params['database'] ?? '';
        $newline = $params['newline'] ?? '';

        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ? and table_type="BASE TABLE"', [$database])
                  ;

        $str = '';
        foreach ($tables as $key => $table)
        {
            $table_name = $database.'.'.$table->table_name;
            $table_create_statement = ((array) DB::select('SHOW CREATE TABLE '.$table_name)[0])['Create Table'].';';
            $str.= isset($newline) ? $newline.$table_create_statement : $table_create_statement;
        }
        return $str;
    }

    public function all_view_create_statement($params=[])
    {
        $database = $params['database'] ?? '';
        $newline = $params['newline'] ?? '';

        $views = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ? and table_type="VIEW"', [$database]);

        $str = '';
        foreach ($views as $key => $item)
        {
            $view_name = $database.'.'.$item->table_name;
            $view_create_statement = ((array) DB::select('SHOW CREATE TABLE '.$view_name)[0])['Create View'].';';
            $view_create_statement = SDBHCommonFacade::replaceBetweenStrings($view_create_statement, 'ALGORITHM=UNDEFINED DEFINER', 'SQL SECURITY DEFINER', 'OR REPLACE');

            $str.= isset($newline) ? $newline.$view_create_statement : $view_create_statement;
        }
        return $str;
    }

    public function all_procedure_create_statement($params=[])
    {
        $database = $params['database'] ?? '';
        $newline = $params['newline'] ?? '';
        $ROUTINE_TYPE = $params['ROUTINE_TYPE'] ?? '';

        $prefix_proc_func =  '<newline>DELIMITER $$<newline>';
        $postfix_proc_func = '$$'.
                             '<newline>DELIMITER ;<newline>';

        $list = DB::select('
                            SELECT
                                r.ROUTINE_NAME routine_name,
                                CONCAT("CREATE ", r.ROUTINE_TYPE, " ", r.ROUTINE_NAME, group_concat(" (",p.PARAMETER_MODE, " ", p.PARAMETER_NAME, " ", p.DATA_TYPE," ) "), r.ROUTINE_DEFINITION) AS create_statement
                            FROM
                                information_schema.ROUTINES r
                                LEFT JOIN information_schema.PARAMETERS p ON r.SPECIFIC_NAME = p.SPECIFIC_NAME
                            WHERE
                            r.ROUTINE_SCHEMA = ?  AND r.ROUTINE_TYPE = ?
                            group by r.ROUTINE_NAME', [$database, $ROUTINE_TYPE]);


        $str = '';
        foreach ($list as $key => $item)
        {
            $routine_name = $item->routine_name;
            $create_statement = $item->create_statement;
            $drop_statement = 'DROP '.$ROUTINE_TYPE.' IF EXISTS  '.$routine_name.';<newline> $$<newline>';
            $create_statement= isset($newline) ? $newline.$create_statement : $routine_definition;

            $str .= $prefix_proc_func.$drop_statement.$create_statement.$postfix_proc_func;
        }
        return $str;
    }


    public function all_function_create_statement($params=[])
    {
        $database = $params['database'] ?? '';
        $newline = $params['newline'] ?? '';
        $ROUTINE_TYPE = $params['ROUTINE_TYPE'] ?? '';

        $prefix_proc_func =  '<newline>DELIMITER $$<newline>';
        $postfix_proc_func = '$$'.
                             '<newline>DELIMITER ;<newline>';

        $list = DB::select('
                            SELECT
                                r.ROUTINE_NAME routine_name,
                                CONCAT("CREATE ", r.ROUTINE_TYPE, " ", r.ROUTINE_NAME, group_concat(distinct " (", p.PARAMETER_NAME, " ", p.DATA_TYPE," ) "), " RETURNS ", r.DATA_TYPE, " ", r.ROUTINE_DEFINITION) AS create_statement
                            FROM
                                information_schema.ROUTINES r
                                LEFT JOIN information_schema.PARAMETERS p ON r.SPECIFIC_NAME = p.SPECIFIC_NAME
                            WHERE
                            r.ROUTINE_SCHEMA = ?  AND r.ROUTINE_TYPE = ?
                            group by r.ROUTINE_NAME', [$database, $ROUTINE_TYPE]);


        $str = '';
        foreach ($list as $key => $item)
        {
            $routine_name = $item->routine_name;
            $create_statement = $item->create_statement;
            $drop_statement = 'DROP '.$ROUTINE_TYPE.' IF EXISTS  '.$routine_name.';<newline> $$<newline>';
            $create_statement= isset($newline) ? $newline.$create_statement : $routine_definition;

            $str .= $prefix_proc_func.$drop_statement.$create_statement.$postfix_proc_func;
        }
        return $str;
    }


    // public function generateInsertStatements()
    // {
    //     $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

    //     foreach ($tables as $table) {
    //         $records = DB::table($table)->get();

    //         if ($records->isEmpty()) {
    //             continue;
    //         }

    //         $insertStatements = [];

    //         foreach ($records as $record) {
    //             $insertStatements[] = DB::table($table)->insertGetId((array) $record, 'id');
    //         }

    //         $tableInsertStatements = implode(";\n", $insertStatements) . ';';
    //         dd($tableInsertStatements);

    //         // echo "Table: $table\n";
    //         // echo "$tableInsertStatements\n\n";
    //     }
    // }

    public function db_all_table_structure_backup_process($params=[]) // db structure (table + view + procedure + function) stores in a file
    {
        $request = $params['request'] ?? [];
        $dir = $params['dir'] ?? '';
        $file_name = $params['file_name'] ?? '';

        $file_full_url = $dir.$file_name;
        $database = DB::getDatabaseName();



        $projectURL = url('/');
        $deviceIP = \Request::ip();

        $file = fopen($file_full_url, 'w');

        // General Info Start
        $general_info = '-- DB: '.$database.' Database Backup Generated time = '.SDBHCommonFacade::YmdTodmYPm(SDBHCommonFacade::getNow()).
                            PHP_EOL.'-- Project URL = '.$projectURL.
                            PHP_EOL.'-- Device IP = '.$deviceIP;
        fwrite($file, $general_info);
        // General Info End


        // Objects Counting Start Start
        $tableViewsCounts = DB::select('SELECT count(TABLE_NAME) AS TOTALNUMBEROFTABLES FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?', [$database]);
        $tableViewsCounts = $tableViewsCounts[0]->TOTALNUMBEROFTABLES;

        $viewsCounts = DB::select('SELECT count(TABLE_NAME) AS TOTALNUMBEROFVIEWS FROM INFORMATION_SCHEMA.TABLES WHERE  TABLE_TYPE LIKE "VIEW" AND TABLE_SCHEMA = ?', [$database]);
        $viewsCounts = $viewsCounts[0]->TOTALNUMBEROFVIEWS;

        $tablesCount = $tableViewsCounts-$viewsCounts;


        $proceduresCounts = DB::select('SELECT count(TYPE) AS proceduresCounts FROM mysql.proc WHERE  TYPE="PROCEDURE" AND db = ?', [$database]);
        $proceduresCounts = $proceduresCounts[0]->proceduresCounts;

        $functionsCounts = DB::select('SELECT count(TYPE) AS functionsCounts FROM mysql.proc WHERE  TYPE="FUNCTION" AND db = ?', [$database]);
        $functionsCounts = $functionsCounts[0]->functionsCounts;

        $object_counting =
                            PHP_EOL.PHP_EOL.'-- =============Objects Counting Start================= '.
                            PHP_EOL.'-- =============Objects Counting Start================= '.
                            PHP_EOL.'-- Total Tables + Views = '.$tableViewsCounts.
                            PHP_EOL.'-- Total Tables = '.$tablesCount.
                            PHP_EOL.'-- Total Views = '.$viewsCounts.
                            PHP_EOL.'-- Total Procedures = '.$proceduresCounts.
                            PHP_EOL.'-- Total Functions = '.$functionsCounts.
                            PHP_EOL.'-- =============Objects Counting End================= '.
                            PHP_EOL.'-- =============Objects Counting End================= '.PHP_EOL.PHP_EOL;

        fwrite($file, $object_counting);
        // Objects Counting Start End


        // Table Process start
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ? and table_type="BASE TABLE"', [$database]);
        $all_table_create_statement = '';
        foreach ($tables as $key => $table)
        {
            $table_name = $database.'.'.$table->table_name;
            $table_create_statement = ((array) DB::select('SHOW CREATE TABLE '.$table_name)[0])['Create Table'].';';

            $all_table_create_statement .=  '-- ==================Table: '.$table_name.'================== '.PHP_EOL.$table_create_statement.PHP_EOL.PHP_EOL;
        }

        $table_structure_full = PHP_EOL.PHP_EOL.'-- =============Table Structure Start================= '.
                            PHP_EOL.'-- =============Table Structure Start================= '.
                            PHP_EOL.PHP_EOL.'SET FOREIGN_KEY_CHECKS=0; '.
                            PHP_EOL.'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";'.
                            PHP_EOL.'START TRANSACTION;'.
                            PHP_EOL.'SET time_zone = "+06:00";'.
                            PHP_EOL.'drop database if exists '.$database.';'.
                            PHP_EOL.'CREATE DATABASE IF NOT EXISTS '.$database.' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'.
                            PHP_EOL.'use '.$database.';'.

                            PHP_EOL.PHP_EOL.PHP_EOL.'-- Total Tables = '.$tablesCount.
                            PHP_EOL.PHP_EOL.

                            $all_table_create_statement.  // all tables create statement will store here

                            PHP_EOL.PHP_EOL.PHP_EOL.'SET FOREIGN_KEY_CHECKS=1;'.
                            PHP_EOL.'COMMIT;'.
                            PHP_EOL.PHP_EOL.'-- =============Table Structure end================= '.
                            PHP_EOL.'-- =============Table Structure end================= ';

        fwrite($file, $table_structure_full);

        // Table Process end


        // View Structure Start
        $views = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ? and table_type="VIEW"', [$database]);
        $all_view_create_statement = '';
        foreach ($views as $key => $item)
        {
            $view_name = $database.'.'.$item->table_name;
            $view_create_statement = ((array) DB::select('SHOW CREATE TABLE '.$view_name)[0])['Create View'].';';
            $view_create_statement = SDBHCommonFacade::replaceBetweenStrings($view_create_statement, 'ALGORITHM=UNDEFINED DEFINER', 'SQL SECURITY DEFINER', 'OR REPLACE');

            $all_view_create_statement .=  '-- ==================View: '.$view_name.'================== '.PHP_EOL.$view_create_statement.PHP_EOL.PHP_EOL;
        }


        $view_structure_full = PHP_EOL.PHP_EOL.'-- =============View Structure Start================= '.
        PHP_EOL.'-- =============View Structure Start================= '.

        PHP_EOL.PHP_EOL.PHP_EOL.'-- Total Views = '.$viewsCounts.
        PHP_EOL.PHP_EOL.$all_view_create_statement.  // all views create statement will store here
        PHP_EOL.PHP_EOL.'-- =============View Structure end================= '.
        PHP_EOL.'-- =============View Structure end================= ';

        fwrite($file, $view_structure_full);
        // View Structure end



        // Procedure Start
        $ROUTINE_TYPE = 'PROCEDURE';

        $prefix_proc_func =  PHP_EOL.'DELIMITER $$'.PHP_EOL;
        $postfix_proc_func = '$$'.
                                PHP_EOL.'DELIMITER ;'.PHP_EOL;

        $list = DB::select('
                            SELECT
                                r.ROUTINE_NAME routine_name,
                                CONCAT("CREATE ", r.ROUTINE_TYPE, " ", r.ROUTINE_NAME, group_concat(" (",p.PARAMETER_MODE, " ", p.PARAMETER_NAME, " ", p.DATA_TYPE," ) "), r.ROUTINE_DEFINITION) AS create_statement
                            FROM
                                information_schema.ROUTINES r
                                LEFT JOIN information_schema.PARAMETERS p ON r.SPECIFIC_NAME = p.SPECIFIC_NAME
                            WHERE
                            r.ROUTINE_SCHEMA = ?  AND r.ROUTINE_TYPE = ?
                            group by r.ROUTINE_NAME', [$database, $ROUTINE_TYPE]);


        $all_procedure_create_statement = '';
        foreach ($list as $key => $item)
        {
            $routine_name = $item->routine_name;
            $create_statement = $item->create_statement;
            $drop_statement = 'DROP '.$ROUTINE_TYPE.' IF EXISTS  '.$routine_name.';'.PHP_EOL.'$$'.PHP_EOL;

            $all_procedure_create_statement .= '-- ==================Procedure: '.$routine_name.'================== '.PHP_EOL.$prefix_proc_func.$drop_statement.$create_statement.$postfix_proc_func.PHP_EOL.PHP_EOL;
        }

        $procedure_structure_full = PHP_EOL.PHP_EOL.'-- =============Procedure Structure Start================= '.
                            PHP_EOL.'-- =============Procedure Structure Start================= '.
                            PHP_EOL.PHP_EOL.PHP_EOL.'-- Total Procedures = '.$proceduresCounts.
                            PHP_EOL.PHP_EOL.$all_procedure_create_statement.  // all procedures create statement will store here
                            PHP_EOL.PHP_EOL.'-- =============Procedure Structure end================= '.
                            PHP_EOL.'-- =============Procedure Structure end================= ';

        fwrite($file, $procedure_structure_full);
        // Procedure End


        // Function Start
        $ROUTINE_TYPE = 'FUNCTION';

        $prefix_func =  PHP_EOL.'DELIMITER $$'.PHP_EOL;
        $postfix_func = '$$'.
                                PHP_EOL.'DELIMITER ;'.PHP_EOL;

        $list = DB::select('
                            SELECT
                                r.ROUTINE_NAME routine_name,
                                CONCAT("CREATE ", r.ROUTINE_TYPE, " ", r.ROUTINE_NAME, group_concat(distinct " (", p.PARAMETER_NAME, " ", p.DATA_TYPE," ) "), " RETURNS ", r.DATA_TYPE, " ", r.ROUTINE_DEFINITION) AS create_statement
                            FROM
                                information_schema.ROUTINES r
                                LEFT JOIN information_schema.PARAMETERS p ON r.SPECIFIC_NAME = p.SPECIFIC_NAME
                            WHERE
                            r.ROUTINE_SCHEMA = ?  AND r.ROUTINE_TYPE = ?
                            group by r.ROUTINE_NAME', [$database, $ROUTINE_TYPE]);


        $all_function_create_statement = '';
        foreach ($list as $key => $item)
        {
            $routine_name = $item->routine_name;
            $create_statement = $item->create_statement;
            $drop_statement = 'DROP '.$ROUTINE_TYPE.' IF EXISTS  '.$routine_name.';'.PHP_EOL.'$$'.PHP_EOL;

            $all_function_create_statement .= '-- ==================Function: '.$routine_name.'================== '.PHP_EOL.$prefix_func.$drop_statement.$create_statement.$postfix_func.PHP_EOL.PHP_EOL;
        }

        $function_structure_full = PHP_EOL.PHP_EOL.'-- =============Function Structure Start================= '.
                            PHP_EOL.'-- =============Function Structure Start================= '.
                            PHP_EOL.PHP_EOL.PHP_EOL.'-- Total Functions = '.$functionsCounts.
                            PHP_EOL.PHP_EOL.$all_function_create_statement.  // all functions create statement will store here
                            PHP_EOL.PHP_EOL.'-- =============Function Structure end================= '.
                            PHP_EOL.'-- =============Function Structure end================= ';

        fwrite($file, $function_structure_full);
        // Function End


        fclose($file); // close the file

        return [
            'dir' => $dir,
            'file_name' => $file_name,
            'file_full_url' => $file_full_url
        ];
    }


    public function db_all_table_data_backup_process($params=[])   // db data of tables stores in a file
    {
        $request = $params['request'] ?? [];
        $dir = $params['dir'] ?? '';
        $file_name = $params['file_name'] ?? '';

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

        return [
            'dir' => $dir,
            'file_name' => $file_name,
            'file_full_url' => $file_full_url
        ];
    }


}
