<?php

namespace App\Http\Controllers\Api\Panel\Foods;

use App\Http\Controllers\Controller;
use App\Model\Panel\Foods;
use App\Model\Theme\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class indexController extends Controller
{
    public $status;

    public function __construct(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = User::where("api_token", $bearer)
            ->first();
        if ($user) {
            $this->status = $user->status;
        }
    }

    public function index(Request $request)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $foods = DB::table("Foods")->get();
            if ($foods) {
                return response($foods, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response("Listelenecek Veri Bulunmamaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|min:3",
            "img_url" => "required",
            "isActive" => "required|numeric",
            "description" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $foods = new Foods;
            $foods->title = $request->title;
            $foods->img_url = $request->img_url;
            $foods->isActive = (integer)$request->isActive;
            $foods->description = $request->description;
        }
        $foods->save();
        return response($foods, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function edit($id)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $foods = Foods::where("_id", $id)
                ->first();
            if ($foods) {
                return response($foods, 200);
            } else {
                return response("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function update(Request $request)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            if ($request->id) {
                $user = User::where("_id", $request->id)->first();
                if ($user) {
                    $data = $request->except("_token");
                    if ($data["id"]) {
                        unset($data["id"]);
                    }
                    $foods = Foods::where("_id", $request->id)
                        ->update($data);
                    if ($foods) {
                        return response()->json("Güncelleme İşlemi Başarılı", 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json("Güncelleme İşlemi Yapılamadı", 200, [], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır", 200, [], JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }

    public function destroy($id)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            Foods::where("_id", $id)
                ->delete();
            return response()->json("Silme İşlemi Başarılı", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
