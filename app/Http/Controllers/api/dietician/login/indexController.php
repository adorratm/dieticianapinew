<?php

namespace App\Http\Controllers\Api\Dietician\Login;

use App\Http\Controllers\Controller;
use App\Model\Dietician\Dieticians;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class indexController extends Controller
{
    public function login(Request $request)
    {
        $dietician = Dieticians::where("email", $request->email)->where("isActive", 1)->first();
        if (empty($dietician)) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girmiş Olduğunuz Mail Hesabına Ait Kullanıcı Bilgisi Bulunamadı."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {

            if (Hash::check($request->password, $dietician->password)) {
                $update = Dieticians::where("_id", $dietician->_id)->update(["api_token" => Str::random(60)]);
                if ($update) {
                    $dietician = Dieticians::where("email", $request->email)->first();
                    $dietician["profile_photo"] = DB::table("dieticians_file")->where("dieticians_id", $dietician->_id)->where("type", "profile_photo")->first();
                    if (!empty($dietician["profile_photo"])) :
                        $dietician["profile_photo"] = $dietician["profile_photo"]["img_url"];
                    endif;
                    $dietician["company_logo"] = DB::table("dieticians_file")->where("dieticians_id", $dietician->_id)->where("type", "company_logo")->first();
                    if (!empty($dietician["company_logo"])) :
                        $dietician["company_logo"] = $dietician["company_logo"]["img_url"];
                    endif;
                    $dietician["clinic_photos"] = DB::table("dieticians_file")->where("dieticians_id", $dietician->_id)->where("type", "clinic_photos")->get();
                    if (!empty($dietician["profile_photo"])) :
                        $dietician["clinic_photos"] = $dietician["clinic_photos"];
                    endif;

                    return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Merhaba \"{$dietician->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.", "user" => $dietician], 200, [], JSON_UNESCAPED_UNICODE);
                }
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girmiş Olduğunuz Şifre Yanlış Kontrol Edip, Lütfen Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
}
