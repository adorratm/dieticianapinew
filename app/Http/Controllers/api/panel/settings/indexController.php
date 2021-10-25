<?php

namespace App\Http\Controllers\Api\Panel\Settings;

use App\Http\Controllers\Controller;
use App\Model\Theme\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class indexController extends Controller
{
    public $user = "";

    public function __construct(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = User::where("api_token", $bearer)
            ->first();
        if ($user) {
            $this->user = $user;
        }
    }

    public function index()
    {
            $settings = DB::table("settings")->get();

        if ($settings) {
            return response()->json(["success"=>true,"data"=>$settings], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success"=>false,"title"=>"Başarısız!","msg"=>"Site Ayarları Listelenirken Bir Hata Olşutu!"], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "company_name" => "required",
            "phone" => "required",
            "email" => "required|email"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data = $request->except("_token");
            if ($request->file()) {
                $photos = [];
                foreach ($request->file() as $key => $file):
                    $photo = $request->file($key);
                    $path = $request->$key->path();
                    $extension = $request->$key->extension();
                    $fileNameWithExtension = $photo->getClientOriginalName();
                    $fileNameWithExtension = Str::slug($request->company_name) . "-" . time() . "." . $extension;
                    $path = $request->$key->storeAs("uploads/settings/{$key}", $fileNameWithExtension, "public");
                    if (!empty($path)) {
                        $data[$key] = $path;
                        array_push($photos, $fileNameWithExtension);
                    }
                    if (is_dir(storage_path("app/public/uploads/settings/{$key}"))) {
                        $files = opendir(storage_path("app/public/uploads/settings/{$key}"));
                        while ($item = readdir($files)) {
                            if (!in_array($item, $photos)) {
                                if (!is_dir(storage_path("app/public/uploads/settings/{$key}/{$item}"))) {
                                    unlink(storage_path("app/public/uploads/settings/{$key}/{$item}"));
                                }
                            }
                        }
                    }
                endforeach;

            }
            $count=DB::table("settings")->count();
            $data["rank"]=$count+1;
            $settings = DB::table("settings")->insertGetId($data);
            $data = DB::table("settings")->where("_id", $settings)->first();

            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarlarınız Başarıyla Eklendi", "data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function edit($id)
    {
        $settings = DB::table("settings")
            ->where("_id", $id)->first();
        if($settings){
            return response(["success"=>true,"data"=>$settings], 200,[],JSON_UNESCAPED_UNICODE);
        }else{
            return response()->json(["success"=>false,"title"=>"Başarısız!","msg"=>"Böyle Bir Veri Bulunamadı!"], 200, [], JSON_UNESCAPED_UNICODE);

        }

    }

    public function update($id, Request $request)
    {
        $data = $request->except("_token");
        if (!empty($data["_id"])) {
            unset($data["_id"]);
        }
        $validator = Validator::make($request->all(), [
            "company_name" => "required",
            "phone" => "required",
            "email" => "required|email"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data = $request->except("_token");
            if ($request->file()) {
                $photos = [];
                foreach ($request->file() as $key => $file):
                    $photo = $request->file($key);
                    $path = $request->$key->path();
                    $extension = $request->$key->extension();
                    $fileNameWithExtension = $photo->getClientOriginalName();
                    $fileNameWithExtension = Str::slug($request->company_name) . "-" . time() . "." . $extension;
                    $path = $request->$key->storeAs("uploads/settings/{$key}", $fileNameWithExtension, "public");
                    if (!empty($path)) {
                        $data[$key] = $path;
                        array_push($photos, $fileNameWithExtension);
                    }
                    if (is_dir(storage_path("app/public/uploads/settings/{$key}"))) {
                        $files = opendir(storage_path("app/public/uploads/settings/{$key}"));
                        while ($item = readdir($files)) {
                            if (!in_array($item, $photos)) {
                                if (!is_dir(storage_path("app/public/uploads/settings/{$key}/{$item}"))) {
                                    unlink(storage_path("app/public/uploads/settings/{$key}/{$item}"));
                                }
                            }
                        }
                    }
                endforeach;

            }
            $data = DB::table("settings")->where("_id",$id)->update($data);
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarlarınız Başarıyla Güncellendi", "data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy($id)
    {
        $settings = DB::table("settings")
            ->where("_id", $id)->delete();
        if($settings){
            $settings = DB::table("settings")->get();
            return response()->json(["success"=>true,"title"=>"Başarılı!","msg"=>"Ayarınız Başarıyla Silindi","data"=>$settings],200,[],JSON_UNESCAPED_UNICODE);
        }else{
            return response()->json(["success"=>false,"title"=>"Başarısız!","msg"=>"Ayarınız Silinirken Bir Hata İle Karşılaşıldı."],200,[],JSON_UNESCAPED_UNICODE);

        }
    }
}
