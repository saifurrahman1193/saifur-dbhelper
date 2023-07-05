<?php

namespace Saifur\LogViewer\app\Http\Middleware;

use Closure;
use App\Models\User;
use App\Traits\Queries;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Hash;

class SaifurLogViewerMiddleware
{
    use ApiResponser;
    use Queries;

    public function handle($request, Closure $next)
    {
        if (\config('app.saifur_logviewer_middleware')==1)
        {
            if (isset($request->email) && isset($request->password))
            {
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

                if (!($user->status==1))
                {
                    return $this->set_response(null, 422, 'failed', ['User is not authorized for log viewer!']);
                }


                return $next($request);
            }
            else
            {
                return $this->set_response(null,401,'error',['Unauthenticated.']);
            }
        }
        else
        {
            return $next($request);
        }
    }

}
