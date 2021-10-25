<?php

namespace App\Http\Controllers\Api\Dietician\Profile;

use App\Http\Controllers\Controller;
use App\Model\Theme\Dieticians;
use App\Model\Theme\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class indexController extends Controller
{
    public function profile(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $dietician = Dieticians::where("api_token", $token)->first();
            $dietician["company_logo"] = DB::table("dieticians_file")->where("dieticians_id", $dietician->_id)->where("type", "company_logo")->first();
            $dietician["clinic_photos"] = DB::table("dieticians_file")->where("dieticians_id", $dietician->_id)->where("type", "clinic_photos")->get();
            $dietician["profile_photo"] = DB::table("dieticians_file")->where("dieticians_id", $dietician->_id)->where("type", "profile_photo")->first();

            if (!empty($dietician["company_logo"])) :
                $dietician["company_logo"] = $dietician["company_logo"]["img_url"];
            endif;
            if (!empty($dietician["profile_photo"])) :
                $dietician["profile_photo"] = $dietician["profile_photo"]["img_url"];
            endif;
            if ($dietician) {
                $data = $dietician;
                $dietician["cities"] = DB::table("cities")->select("name", "towns")->where("name", $dietician->city)->first();
                $dietician["company_cities"] = DB::table("cities")->select("name", "towns")->where("name", $dietician->city)->first();
                if (!empty($dietician["cities"])) {
                    $dietician["towns"] = DB::table("towns")->select("name", "districts")->whereIn("_id", $dietician["cities"]["towns"])->get();
                    $towns = DB::table("towns")->select("name", "districts")->where("name", $data->town)->first();
                    $dietician["districts"] = DB::table("districts")->select("name", "neigborhoods")->whereIn("_id", $towns["districts"])->get();
                    $districts = DB::table("districts")->where("name", $data->district)->first();
                    $dietician["neighborhoods"] = DB::table("neighborhoods")->select("name")->whereIn("_id", $districts["neighborhoods"])->get();
                } else {
                    $dietician["towns"] = [];
                    $dietician["districts"] = [];
                    $dietician["neighborhoods"] = [];
                    $dietician["cities"] = [];
                }
                $dietician["company_city"] = DB::table("cities")->select("towns", "name")->where("name", $dietician->company_city)->first();
                if (!empty($dietician["company_city"])) {
                    $dietician["company_cities"] = DB::table("cities")->select("name", "towns")->get();
                    $dietician["company_towns"] = DB::table("towns")->select("name", "districts")->whereIn("_id", $dietician["company_city"]["towns"])->get();
                    $dietician["company_city"] = $dietician["company_city"]["name"];
                    $towns = DB::table("towns")->select("name", "districts")->where("name", $data->company_town)->first();
                    $dietician["company_districts"] = DB::table("districts")->select("name", "neigborhoods")->whereIn("_id", $towns["districts"])->get();
                    $districts = DB::table("districts")->where("name", $data->company_district)->first();
                    $dietician["company_neighborhoods"] = DB::table("neighborhoods")->select("name")->whereIn("_id", $districts["neighborhoods"])->get();
                } else {
                    $dietician["company_towns"] = [];
                    $dietician["company_districts"] = [];
                    $dietician["company_neighborhoods"] = [];
                    $dietician["company_cities"] = [];
                }
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Merhaba \"{$dietician->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.", "user" => $dietician], 200, [], JSON_UNESCAPED_UNICODE);
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
        $user = Dieticians::where("api_token", $token)->first();
        if ($user) {
            $data = $request->except("_token");
            if (!empty($data["status"])) {
                unset($data["status"]);
            }
            $update = Dieticians::where("api_token", $token)->update($data);
            unset($data);
            if ($update) {

                if (!empty($request->file())) {
                    $status = 1;
                    foreach ($request->file() as $key => $file) :
                        if (is_array($file)) :
                            foreach ($file as  $v) {
                                $strFileName = Str::slug($request->title);
                                $extension = $v->extension();
                                $fileNameWithExtension = $strFileName . "-" . rand(0, 99999999999) . "-" . time() . "." . $extension;
                                $path = $v->storeAs("uploads/dietician/{$user->id}/{$strFileName}/", $fileNameWithExtension, "public");
                                $count = DB::table("dieticians_file")->where("dietician_id", $user->id)->count();
                                $data["dieticians_id"] = (string)$user->id;
                                $data["img_url"] = $path;
                                $data["isActive"] = 1;
                                $data["type"] = $key;
                                $data["rank"] = $count + 1;
                                $data["isCover"] = 0;
                                $add = DB::table("dieticians_file")->insert($data);
                                if (!$path || !$add) {
                                    $status = 0;
                                }
                            }
                        else :
                            $strFileName = Str::slug($request->title);
                            $extension = $file->extension();
                            $fileNameWithExtension = $strFileName . "-" . rand(0, 99999999999) . "-" . time() . "." . $extension;
                            $path = $file->storeAs("uploads/dietician/{$user->id}//{$strFileName}/", $fileNameWithExtension, "public");
                            $count = DB::table("dieticians_file")->where("dietician_id", $user->id)->count();
                            $data["dieticians_id"] = (string)$user->id;
                            $data["img_url"] = $path;
                            $data["isActive"] = 1;
                            $data["type"] = $key;
                            $data["rank"] = $count + 1;
                            $data["isCover"] = 0;
                            $add = DB::table("dieticians_file")->insert($data);
                            if (!$path || !$add) {
                                $status = 0;
                            }
                        endif;

                    endforeach;
                }
                $user = Dieticians::where("api_token", $token)->first();
                $user["company_logo"] = DB::table("dieticians_file")->where("dieticians_id", $user->_id)->where("type", "company_logo")->first();
                $user["clinic_photos"] = DB::table("dieticians_file")->where("dieticians_id", $user->_id)->where("type", "clinic_photos")->get();
                $user["profile_photo"] = DB::table("dieticians_file")->where("dieticians_id", $user->_id)->where("type", "profile_photo")->first();

                if (!empty($user["company_logo"])) :
                    $user["company_logo"] = $user["company_logo"]["img_url"];
                endif;
                if (!empty($user["profile_photo"])) :
                    $user["profile_photo"] = $user["profile_photo"]["img_url"];
                endif;
                return response()->json(["msg" => "Güncelleme İşlemi Başarılı", "title" => "Başarılı", "success" => true, "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
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
        $user = Dieticians::where("api_token", $token)->first();
        if ($user) {
            $data = $request->except("_token");
            if (Hash::check($data["current_password"], $user->password)) {
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
                        $dietician = Dieticians::where("api_token", $token)->first();
                        return response()->json(["msg" => "Güncelleme İşlemi Başarılı", "title" => "Başarılı", "success" => true, "data" => $dietician], 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json(["msg" => "Güncelleme İşlemi Başarısız", "title" => "Başarısız", "success" => false], 200, [], JSON_UNESCAPED_UNICODE);
                    }
                }
            } else {
                return response()->json(["msg" => "Mevcut Şifreniz Hatalı", "title" => "Başarısız", "success" => false], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["msg" => "Böyle Bir Kullanıcı Yoktur", "title" => "Başarısız", "success" => false], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function logout(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
        }
        $user = Dieticians::where("api_token", $token)->first();
        if ($user) {
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


    public function appointment_conf(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            dd($request->all());
        } else {
            return response()->json("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
