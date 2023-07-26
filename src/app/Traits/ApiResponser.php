<?php

namespace Saifur\DBHelper\app\Traits;
use Illuminate\Support\Facades\Response;

trait ApiResponser{

    public static function  set_response($data, $status_code, $status, $details, $request=null)
    {
        $res_data = [
            'status'        =>  $status,
            'code'          =>  $status_code,
            'data'          =>  $data,
            'message'       =>  $details
        ];

        $response = Response::json($res_data, 200, [] )->header('Content-Type', 'application/json');


        return $response;
    }


    public static function status_code_handler($status_code)
    {
        if ($status_code==200) return 'info';
        else if ($status_code==401) return 'warning';
        else if ($status_code==500) return 'debug';
        else return 'error';
    }

}
