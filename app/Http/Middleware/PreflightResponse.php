<?php

namespace App\Http\Middleware;
use Closure;
class PreflightResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        if($request->getMethod() === 'OPTIONS'){
            die("666");
            //$origin = $request->header('ORIGIN', '*');
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Allow-Methods: *');
            header('Access-Control-Allow-Headers: Origin, Access-Control-Request-Headers, SERVER_NAME, Access-Control-Allow-Headers, cache-control, token, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie, X-XSRF-TOKEN, auth-token');
        }
        return $next($request);
    }
}
