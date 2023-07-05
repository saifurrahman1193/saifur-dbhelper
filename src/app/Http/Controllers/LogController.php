<?php

namespace Saifur\LogViewer\app\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LogController extends Controller
{
    use ApiResponser;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email|max:255|',
                'password' => 'required|string|min:8',
            ],
            [
                'email.required' => 'Invalid User ID or Password',
                'email.email' => 'Invalid User ID or Password',
                'email.exists' => 'Invalid User ID or Password',
                'password.required' => 'Invalid User ID or Password',
                'password.min' => 'Invalid User ID or Password',
            ]
        );
        if ($validator->fails()) {
            return $this->set_response(null, 422, 'failed', $validator->errors()->all());
        }

        $password = $request->password;
        $user = User::where('email', $request->email)->first();
        $existing_password = $user->password;

        if (!Hash::check($password, $existing_password))
        {
            return $this->set_response(null, 401, 'failed', ['Invalid User ID or Password']);
        }


        $user = User::where('email', $request->email)->first();

        if (!($user->status==1))
        {
            return $this->set_response(null, 422, 'failed', ['User is inactive!']);
        }

        if (!($user->log_viewer==1))
        {
            return $this->set_response(null, 422, 'failed', ['User is not authorized for log viewer!']);
        }

        return $this->set_response(null, 200, 'success', ['Logged in!']);
    }


    public function logList(Request $request)
    {
        return view('logviewer::log.loglist');
    }

    public function logFilesListData(Request $request)
    {
        try {
            $memory_limit = (int) ini_get('memory_limit')*1024*1024;

            $dir = storage_path().'/logs/';
            $fileList = array_diff(scandir($dir), array('.', '..'));
            $files = [];

            foreach ($fileList as $item)
            {
                if (!in_array($item, ['.gitignore']))
                {
                    $file_size = fileSize($dir.$item);
                    $files[] = [
                        'file_name' => $item,
                        'file_path' => $dir.$item,
                        'date' => str_replace(["laravel-", ".log"],"",$item),
                        'size' => $file_size,
                        'memory_limit_exceeds' => $file_size>$memory_limit,
                    ];
                }
            }


            $files = collect($files);
            $files = $files->sortByDesc('date')->values()->all();

            $data = [
                "info" => [
                    "memory_limit" => (int) ini_get('memory_limit')*1024*1024
                ],
                "files" => $files
            ];

            return $this->set_response($data,  200,'success', ['Log file list']);
        } catch (\Throwable $th) {
            return $this->set_response(null,  400,'failed', ['Log list']);
        }

    }



    // public function singleLogFileData(Request $request)
    // {

    //     $file_path = $request->file_path ?? '';
    //     $data = file($file_path, FILE_IGNORE_NEW_LINES);
    //     $data_f = [];

    //     $parent_log_index = 0;
    //     foreach ($data as $key => $value)
    //     {
    //         $datetime = substr($value, 1, 19);
    //         $is_date = strtotime($datetime);

    //         if ($is_date)
    //         {
    //             $log_full = $value;
    //             $log = substr($log_full, 22);

    //             $environment = explode('.', $log)[0];
    //             $level = explode('.',explode(':', $log)[0])[1];

    //             $log = trim(str_replace($environment.'.'.$level.':', '', $log));

    //             $data_f[] = [
    //                 'log' => $log,
    //                 'datetime'=> $datetime,
    //                 'environment'=> $environment,
    //                 'level'=> $level,
    //             ];
    //             $parent_log_index++;
    //         }
    //         if(!$is_date)
    //         {
    //             if (substr($value, 1, 10)!='stacktrace')
    //             {
    //                 $data_f[$parent_log_index-1]['details'][] = $value;
    //             }
    //         }
    //     }

    //     $data = [
    //         'info' => [
    //             'datetime' => $data_f[0]['datetime'],
    //             'date' => getFormattedDate($data_f[0]['datetime']),
    //             'day' => getYMDToDMcYDay($data_f[0]['datetime'])
    //         ],
    //         'data' => $data_f,
    //     ];

    //     return $this->set_response($data,  200,'success', ['Log lines list']);
    // }


    public function singleLogFileData(Request $request)
    {

        $file_path = $request->file_path ?? '';
        $data = [];
        try {
            $data = file($file_path, FILE_IGNORE_NEW_LINES);
        } catch (\Throwable $th) {
        }
        $data_f = [];

        $parent_log_index = 0;
        foreach ($data as $key => $value)
        {
            $datetime = substr($value, 1, 19);
            $is_date = strtotime($datetime);

            if ($is_date)
            {
                $log_full = $value;
                $log = substr($log_full, 22);

                $environment = explode('.', $log)[0];
                $level = explode('.',explode(':', $log)[0])[1];

                $log = trim(str_replace($environment.'.'.$level.':', '', $log));

                $data_f[] = [
                    'log' => $log,
                    'datetime'=> $datetime,
                    'environment'=> $environment,
                    'level'=> $level,
                ];
                $parent_log_index++;
            }
            if(!$is_date)
            {
                if (substr($value, 1, 10)!='stacktrace')
                {
                    $data_f[$parent_log_index-1]['details'][] = $value;
                }
            }
        }

        $data = [
            'info' => [
                'datetime' => $data_f[0]['datetime'] ?? '',
                'date' => isset($data_f[0]['datetime']) ? getFormattedDate($data_f[0]['datetime']) : '',
                'day' => isset($data_f[0]['datetime']) ? getYMDToDMcYDay($data_f[0]['datetime']) : ''
            ],
            'data' => $data_f,
        ];

        return $this->set_response($data,  200,'success', ['Log lines list']);
    }

    public function logFileDownload(Request $request)
    {

        $file_path = $request->file_path ?? '';
        $file_name = $request->file_name ?? '';

        if (isset($file_name))
        {
            $file_name = explode('/', $file_path);
            $file_name = end($file_name);
        }

        header('Content-Type: text/plain');

        return response()->download($request->file_path, $file_name, []);
    }

    public function logFileDelete(Request $request)
    {

        $file_path = $request->file_path ?? '';

        unlink($file_path);
        return $this->set_response(null,  200,'success', ['Log file deleted!']);
    }

    public function logFileDeleteMultiple(Request $request)
    {

        $file_paths = $request->file_paths ?? [];

        foreach ($file_paths as $key => $file_path) {
            unlink($file_path);
        }
        return $this->set_response(null,  200,'success', ['Selected log files deleted!']);
    }


}

