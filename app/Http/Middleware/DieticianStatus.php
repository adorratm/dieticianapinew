<?php

namespace App\Http\Middleware;

use App\Model\Theme\Dieticians;
use Closure;

class DieticianStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $admin = Dieticians::where("api_token", $token)->where("status", "dietician")->first();
            if (!$admin) {
                return response()->json("Bu İşlemler İçin Yetkili Değilsiniz.", 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return $next($request);
            }
        }
    }
}
