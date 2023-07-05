<?php

namespace Saifur\DBHelper\app\Facades\DBBackup;

use Illuminate\Support\Facades\DB;


class DBBackupHelper
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
            $str.= isset($newline) ? $newline.$view_create_statement : $view_create_statement;
        }
        return $str;
    }

    public function all_procedure_function_create_statement($params=[])
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



}
