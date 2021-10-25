<?php

namespace App\Http\Controllers\Api\Panel\Login;

use App\Http\Controllers\Controller;
use App\Model\Panel\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class indexController extends Controller
{


    public function index()
    {

    }

    public function login(Request $request)
    {
        $user = User::where("email", $request->email)->where("status", "admin")->first();
        if (!$user) {
            return response()->json(["success"=>false,"title"=>"Başarısız!","msg"=>"Girmiş Olduğunuz Mail Hesabına Ait Yetkili Bilgisi Bulunamadı."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            if (Hash::check($request->password, $user->password)) {
                $update = User::where("_id", $user->_id)->update(["api_token" => Str::random(60)]);
                if ($update) {
                    $user = User::where("email", $request->email)->first();
                    return response()->json(["success"=>true,"title"=>"Başarılı!","msg"=>"Merhaba \"{$user->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.","user"=>$user], 200, [], JSON_UNESCAPED_UNICODE);
                }
            } else {
                return response()->json(["success"=>false,"title"=>"Başarısız!","msg"=>"Girmiş Olduğunuz Şifre Yanlış Kontrol Edip, Lütfen Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
}
