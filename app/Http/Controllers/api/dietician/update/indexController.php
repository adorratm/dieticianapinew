<?php

namespace App\Http\Controllers\api\dietician\update;

use App\Http\Controllers\Controller;
use App\Model\Theme\Dieticians;
use Illuminate\Http\Request;

class indexController extends Controller
{
    public function update(Request $request)
    {

        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
        }
        $dietician = Dieticians::where("api_token", $token)->first();
        if ($dietician) {
            $data = $request->except("_token");
            if (!empty($data["status"])) {
                unset($data["status"]);
            }
            $update = Dieticians::where("api_token", $token)->update($data);
            if ($update) {
                $dietician = Dieticians::where("api_token", $token)->first();
                return response()->json(["msg" => "Güncelleme İşlemi Başarılı", "title" => "Başarılı", "success" => true, "data" => $dietician], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Güncelleme İşlemi Başarısız", 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
