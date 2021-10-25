<?php

namespace App\Http\Middleware;

use App\Model\Theme\User;
use App\Model\Theme\Dieticians;
use Closure;

class ApiToken
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
            if (!$token) {
                return response()->json(["message" => "Token Bilginiz Bulunmamaktadır."], 401, [], JSON_UNESCAPED_UNICODE);
            }
            $user = User::where("api_token", $token)->first();
            $dietician = Dieticians::where("api_token", $token)->first();
            if (!$user && !$dietician) {
                return response()->json(["message" => "Token Bilgisine Ait Kullanıcı Bulunmamaktadır."], 401, [], JSON_UNESCAPED_UNICODE);
            }
            return $next($request);
        } else {
            return response()->json(["message" => "Token Bulunmamaktadır."], 401, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
