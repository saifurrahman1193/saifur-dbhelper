<?php

namespace Saifur\LogViewer\app\Traits;
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


        // writeToLog('Api global responser ('.$details[0].') =  '.$resData, self::status_code_handler($status_code));

        if ($request && isset($request->segment))
        {
            $request->request->remove('password');
            activityLogGenerate(
                [
                    'user_id' => auth()->user()->id ?? null,
                    'visitor_ip' => $request->ip(),
                    'log_type_id' => $request->log_type_id,
                    'segment' => $request->header('segment') ?? $request->segment,
                    'page' => $request->header('pagename') ?? $request->pagename,
                    'page_url' => $request->header('pageurl') ?? $request->pageurl,
                    'api_path' => url()->full(),  // $request->url()
                    'api_request' => json_encode($request->all()),
                    'api_response' => json_encode($res_data),
                    'user_agent' => $request->header('User-Agent'),
                    'logtime' => getNow(),
                ]
            );
        }


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
