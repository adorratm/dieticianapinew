<?php

namespace App\Http\Controllers\Api\Theme\Dietician;

use App\Http\Controllers\Controller;
use App\MoDel\Theme\Dieticians;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Integer;

class indexController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $dietician = Dieticians::where("api_token", $token)->first();
            if ($dietician) {
                return response()->json($dietician, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
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
                    return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Merhaba \"{$dietician->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.", "user" => $dietician], 200, [], JSON_UNESCAPED_UNICODE);
                }
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girmiş Olduğunuz Şifre Yanlış Kontrol Edip, Lütfen Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
    public function register(Request $request)
    {
        $dietician = Dieticians::where("email", $request->email)->first();
        if ($dietician) {
            return response()->json("Bu E-posta Adresi Bir Kullanıcı Tarafından Kullanılmaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3|max:70',
                'email' => 'email',
                "phone" =>   'required|min:11|max:19',
                'password' => 'required|confirmed|min:6',
                'identity_file' => 'mimes:jpeg,jpg,png,gif|required',
                'certificate_file' => 'mimes:jpeg,jpg,png,gif,pdf|required',
            ]);
            if ($validator->fails()) {
                return response()->json(["status" => false, "msg" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                $data = $request->except("_token");
                $data["status"] = "dietician";

                $data["password"] = Hash::make($data["password"]);
                unset($data["password_confirmation"]);
                $data["api_token"] = Str::random(60);
                $data["isActive"] = 0;
                $data["isApply"] = 1;
                $slug = Str::slug($data["name"], "-");
                $control = \App\Model\Panel\Dieticians::where("slug", $slug)->count();
                if ($control) {
                    $data["slug"] = $slug . "-" . $control;
                } else {
                    $data["slug"] = $slug;
                }
                $dietician = Dieticians::insertGetID($data);
                unset($data);
                if (!empty($request->file())) {
                    $status = 1;
                    foreach ($request->file() as $key => $file) :

                        $strFileName = Str::slug($request->title);
                        $extension = $file->extension();
                        $fileNameWithExtension = $strFileName . "-" . rand(0, 99999999999) . "-" . time() . "." . $extension;
                        $path = $file->storeAs("uploads/dietician-apply/{$strFileName}/", $fileNameWithExtension, "public");
                        $count = DB::table("dieticians_file")->where("dietician_id", $dietician)->count();
                        $data["dieticians_id"] = (string)$dietician;
                        $data["img_url"] = $path;
                        $data["isActive"] = 1;
                        $data["rank"] = $count + 1;
                        $data["isCover"] = 0;
                        $add = DB::table("dieticians_file")->insert($data);
                        if (!$path || !$add) {
                            $status = 0;
                        }
                    endforeach;
                    if ($status == 0) {
                        return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Resimler Eklenirken Bir Hata Oluştu"], 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Resimler Başarıyla Eklendi"], 200, [], JSON_UNESCAPED_UNICODE);
                    }
                }
                return response()->json(["status" => true, "msg" => "Kayıt Başarılı"], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
    public function profile(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $dietician = Dieticians::where("api_token", $token)->first();

            if ($dietician) {
                $data = $dietician;
                $dietician["cities"] =  DB::table("cities")->select("name", "towns")->where("name", $dietician->city)->first();
                $dietician["towns"] =  DB::table("towns")->select("name", "districts")->whereIn("_id", $dietician["cities"]["towns"])->get();
                $towns = DB::table("towns")->select("name", "districts")->where("name", $data->town)->first();
                $dietician["districts"] =  DB::table("districts")->select("name", "neigborhoods")->whereIn("_id", $towns["districts"])->get();
                $districts = DB::table("districts")->where("name", $data->district)->first();

                $dietician["neighborhoods"] =  DB::table("neighborhoods")->select("name")->whereIn("_id", $districts["neighborhoods"])->get();
                return response()->json(["data" => $dietician], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
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
    public function pass_update(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
        }
        $dietician = Dieticians::where("api_token", $token)->first();
        if ($dietician) {
            $data = $request->except("_token");
            if (Hash::check($data["current_password"], $dietician->password)) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|confirmed|min:6'
                ]);
                if ($validator->fails()) {
                    return response()->json($validator->messages(), 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    unset($data["password_confirmation"]);
                    unset($data["current_password"]);
                    $data["password"] = Hash::make($data["password"]);
                    $update = Dieticians::where("api_token", $token)->update($data);
                    if ($update) {
                        return response()->json("Güncelleme İşlemi Başarılı", 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json("Güncelleme İşlemi Başarısız", 200, [], JSON_UNESCAPED_UNICODE);
                    }
                }
            } else {
                return response()->json("Mevcut Şifreniz Hatalı", 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public function logout(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
        }
        $dietician = Dieticians::where("api_token", $token)->first();
        if ($dietician) {
            $data["api_token"] = Str::random(60);
            if (!empty($data["status"])) {
                unset($data["status"]);
            }
            $update = Dieticians::where("api_token", $token)->update($data);
            if ($update) {
                return response()->json("Token Güncellendi.", 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Token Güncellenemedi", 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
