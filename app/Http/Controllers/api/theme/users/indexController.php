<?php

namespace App\Http\Controllers\Api\Theme\Users;

use App\Http\Controllers\Controller;
use App\Model\Theme\Dieticians;
use App\Model\Theme\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Integer;
use function Symfony\Component\String\s;

class indexController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $user = User::where("api_token", $token)->first();
            if ($user) {
                return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function login(Request $request)
    {
        $user = User::where("email", $request->email)->first();
        if (!$user) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girmiş Olduğunuz Mail Hesabına Ait Kullanıcı Bilgisi Bulunamadı."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            if (Hash::check($request->password, $user->password)) {
                $update = User::where("_id", $user->_id)->update(["api_token" => Str::random(60)]);
                if ($update) {
                    $user = User::where("email", $request->email)->first();
                    return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Merhaba \"{$user->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.", "user" => $user], 200, [], JSON_UNESCAPED_UNICODE);
                }
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girmiş Olduğunuz Şifre Yanlış Kontrol Edip, Lütfen Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function register(Request $request)
    {
        $user = User::where("email", $request->email)->first();
        if ($user) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu E-posta Adresi Bir Kullanıcı Tarafından Kullanılmaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3|max:70',
                'email' => 'email',
                "phone" => 'required|min:11|max:19',
                'password' => 'required|confirmed|min:6',
            ]);
            if ($validator->fails()) {
                return response()->json(["status" => false, "msg" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                $data = $request->except("_token");
                if (!empty($request->file())) {
                    $status = 1;
                    foreach ($request->file() as $key => $file) :
                        $strFileName = Str::slug($request->title);
                        $extension = $file->extension();
                        $fileNameWithExtension = $strFileName . "-" . rand(0, 99999999999) . "-" . time() . "." . $extension;
                        $path = $file->storeAs("uploads/users/{$strFileName}/", $fileNameWithExtension, "public");
                        $data["img_url"] = $path;
                        if (!$path) {
                            $status = 0;
                        }
                    endforeach;
                }
                $data["status"] = "user";

                $data["password"] = Hash::make($data["password"]);
                unset($data["password_confirmation"]);
                $data["api_token"] = Str::random(60);
                $user = User::insert($data);
                if ($user) {
                    return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Kullanıcı Kaydı Başarılı."], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Kullanıcı Kaydı Başarısız."], 200, [], JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }

    public function profile(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $user = User::where("api_token", $token)->first();
			if($user){
				$data = $user;
				return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Merhaba \"{$user->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.", "user" => $user], 200, [], JSON_UNESCAPED_UNICODE);
			}else{
            if ($user) {
                $data = $user;
                $user["cities"] = DB::table("cities")->select("name", "towns")->where("name", $user->city)->first();
                $user["towns"] = DB::table("towns")->select("name", "districts")->whereIn("_id", $user["cities"]["towns"])->get();
                $towns = DB::table("towns")->select("name", "districts")->where("name", $data->town)->first();
                $user["districts"] = DB::table("districts")->select("name", "neigborhoods")->whereIn("_id", $towns["districts"])->get();
                $districts = DB::table("districts")->where("name", $data->district)->first();
                $user["neighborhoods"] = DB::table("neighborhoods")->select("name")->whereIn("_id", $districts["neighborhoods"])->get();
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Merhaba \"{$user->name}\" Başarıyla Giriş Yaptınız Yönlendiriliyorsunuz.", "user" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
            }
			
			}
        }
    }

    public function update(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
        }
        $user = User::where("api_token", $token)->first();
        if ($user) {
            $data = $request->except("_token");
            if (!empty($data["status"])) {
                unset($data["status"]);
            }
            if ($request->file()) {
                foreach ($request->file() as $key => $file) :
                    $photo = $request->file($key);
                    $path = $request->$key->path();
                    $extension = $request->$key->extension();
                    $fileNameWithExtension = $photo->getClientOriginalName();
                    $fileNameWithExtension = Str::slug($request->name) . "-" . time() . "." . $extension;
                    $path = $request->$key->storeAs("uploads/users/{$user->id}", $fileNameWithExtension, "public");
                    if (!empty($path)) {
                        $data[$key] = $path;
                    }
                endforeach;
            }
            $update = User::where("api_token", $token)->update($data);
            if ($update) {
                $user = User::where("api_token", $token)->first();
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
        $user = User::where("api_token", $token)->first();
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
                    $update = User::where("api_token", $token)->update($data);
                    if ($update) {
                        $user = User::where("api_token", $token)->first();
                        return response()->json(["msg" => "Güncelleme İşlemi Başarılı", "title" => "Başarılı", "success" => true, "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
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
        $user = User::where("api_token", $token)->first();
        if ($user) {
            $data["api_token"] = Str::random(60);
            if (!empty($data["status"])) {
                unset($data["status"]);
            }
            $update = User::where("api_token", $token)->update($data);
            if ($update) {
                return response()->json("Token Güncellendi.", 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json("Token Güncellenemedi", 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function dieticianUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dietician_check' => 'required|min:6|max:6',
            'slug' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $dietician = Dieticians::where("slug", $request->slug)->first();
            if (!empty($dietician)) {
                $auth = $request->header("Authorization");
                if ($auth) {
                    $token = str_replace("Bearer ", "", $auth);
                    $user = User::where("api_token", $token)->first();
                    if ($user->dietician_id == $dietician["_id"]) {
                        return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Şuanki Diyetisyeniniz Zaten \"<b>{$dietician->name}</b>\" "], 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        if ((int)$user->dietician_check == $request->dietician_check) {
                            $update = User::where("api_token", $token)->update(["dietician_id" => $dietician["_id"], "dietician_check" => rand(100000, 999999)]);
                            if ($update) {
                                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Diyetisyen Güncelleme İşlemi Başarılı Yeni Diyetisyenininiz \"<b>{$dietician->name}</b>\" ", "data" => User::where("api_token", $token)->first()], 200, [], JSON_UNESCAPED_UNICODE);
                            } else {
                                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Diyetisyen Güncelleme İşlemi Başarısız"], 200, [], JSON_UNESCAPED_UNICODE);
                            }
                        } else {
                            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Kod Hatalıdır"], 200, [], JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Böyle Bir Diyetisyen Yoktur"], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function storeLike(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $user = User::where("id", $request->user_id)->first();
            $likedIds = $request->get("liked_ids");
            $store = $user->likedFoods()->sync($likedIds);
            if ($store) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Kayıt başarıyla gerçekleştirildi", "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Kayıt gerçekleştirilemedi", "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Belirteç Uyuşmazlığı", "data" =>   []], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function storeUnlike(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $user = User::where("id", $request->user_id)->first();
            $unlikedIds = $request->get("unliked_ids");
            $store = $user->unlikedFoods()->sync($unlikedIds);
            if ($store) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Kayıt başarıyla gerçekleştirildi", "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Kayıt gerçekleştirilemedi", "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Belirteç Uyuşmazlığı", "data" =>   []], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function storeAllergen(Request $request)
    {
        $auth = $request->header("Authorization");
        if ($auth) {
            $token = str_replace("Bearer ", "", $auth);
            $user = User::where("id", $request->user_id)->first();
            $nutrientIds = $request->get("nutrient_ids");
            $store = $user->allergens()->sync($nutrientIds);
            if ($store) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Alerjen kaydı başarıyla gerçekleştirildi", "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Alerjen kaydı gerçekleştirilemedi", "data" => $user], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Belirteç Uyuşmazlığı", "data" =>   []], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getDiseases()
    {
        $diseases = DB::table("diseases")->get();
        return \response()->json(["data" => $diseases, "diseaseCount" => $diseases->count(), "success" => true], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function getMeals()
    {
        $meals = DB::table("meals")->get();
        return \response()->json(["data" => $meals, "mealCount" => $meals->count(), "success" => true], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
