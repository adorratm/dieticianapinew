<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
    public function terminate(Request $request){
        $start_time=LARAVEL_START;
        $end_time=microtime(true);
        $log = '['.date("Y-m-d H:i:s") . ']';
        $log .= '['.($end_time-$start_time)*100 . ' ms]';
        $log .= '['.$request->ip() . ']';
        $log .= '['.$request->method() . ']';
        $log .= '['.$request->fullUrl() . ']';
        Log::info($log);
    }
}
