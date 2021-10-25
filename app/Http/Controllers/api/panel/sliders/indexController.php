<?php

namespace App\Http\Controllers\Api\Panel\Sliders;

use App\Http\Controllers\Controller;
use App\Model\Panel\Dieticians;
use App\Model\Panel\Sliders;
use App\Model\Theme\User;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/*
 *@author Umut Can Güngörmüş <umutcangungormus@mutfakyapim.com>
 */

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
            return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $sliders = DB::table("sliders")->get();
            if ($sliders) {
                return response($sliders, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response(["success" => false, "title" => "Başarısız!","msg" => "Listelenecek Veri Bulunmamaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|min:3",
            "img_url" => "required",
            //"description" => "required" Kaldırıldı
        ]);

        if ($validator->fails()) {
            //return response()->json($validator->messages(), 200, [], JSON_UNESCAPED_UNICODE); Aşağıdaki ile değiştirildi.
			return response()->json(["success" => false, "title" => "Başarısız!","msg" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $path = "";
            if ($request->file()) {
                foreach ($request->file() as $key => $file):
                    $photo = $request->file($key);
                    $path = $request->$key->path();
                    $extension = $request->$key->extension();
                    $fileNameWithExtension = $photo->getClientOriginalName();
                    $fileNameWithExtension = Str::slug($request->title) . "-" . time() . "." . $extension;
                    $path = $request->$key->storeAs("uploads/sliders/", $fileNameWithExtension, "public");

                endforeach;

            }
            $sliders = new Sliders;
            $sliders->title = $request->title;
            $sliders->img_url = $path;
            $sliders->isActive = (int)1;
            $sliders->description = $request->description;
        }
        $sliders->save();
        return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Slayt Başarıyla Eklendi.", "data" => $sliders], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function edit($id)
    {
        if ($this->status != "admin") {
            return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $sliders = Sliders::where("_id", $id)
                ->first();
            if ($sliders) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Slayt Başarıyla Güncelleştirildi.", "data" => $sliders],200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response(["success" => false,"title" => "Başarısız!","msg" => "Güncellemek İstediğiniz Slayt Bulunamadı. Lütfen Daha Sonra Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function update(Request $request)
    {
        if ($this->status != "admin") {
            return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            if ($request->id) {
                $user = Sliders::where("_id", $request->id)->first();
                if ($user) {
                    $data = $request->except("_token");
                    if ($request->file()) {
                        foreach ($request->file() as $key => $file):
                            $photo = $request->file($key);
                            $path = $request->$key->path();
                            $extension = $request->$key->extension();
                            $fileNameWithExtension = $photo->getClientOriginalName();
                            $fileNameWithExtension = Str::slug($request->title) . "-" . time() . "." . $extension;
                            $path = $request->$key->storeAs("uploads/sliders/", $fileNameWithExtension, "public");

                        endforeach;
                        $data["img_url"] = $path;
                    }
                    if (!empty($data["id"])) {
                        unset($data["id"]);
                    }
                    $sliders = Sliders::where("_id", $request->id)
                        ->update($data);
                    if ($sliders) {
                        $sliders=Sliders::where("_id", $request->id)->first();
                        return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Slayt Başarıyla Güncelleştirildi.", "data" => $sliders], 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Slayt Güncelleştirilirken Hata Oluştu, Lütfen Daha Sonra Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Slayt Güncelleştirilirken Hata Oluştu, Lütfen Daha Sonra Tekrar Deneyin."], 200, [], JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }

    public function destroy($id)
    {
        if ($this->status != "admin") {
            return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            Sliders::where("_id", $id)->delete();
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Slider Başarıyla Slindi"], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

	public function getAll(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Sliders;
        if (!empty($request->where_column)) {
            $request->where_column = explode(",", $request->where_column);
            $request->where_value = explode(",", $request->where_value);
            if (!is_array($request->where_column) || !is_array($request->where_value)) {
                $request->where_column = (array)$request->where_column;
                $request->where_value = (array)$request->where_value;
            }
            foreach ($request->where_column as $k => $v) {
                $response = $response->where($v, $request->where_value[$k]);
            }
        }
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->nutrients as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }

        }*/

        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

	public function getBySearch(Request $request)
    {
        if (empty($request->search) || $request->search == "null") {
            return Redirect::to(route("panel.sliders.getAll", "table={$request->table}&per_page={$request->per_page}"));
        }
        $request->search_columns = explode(",", $request->search_columns);
        if (!is_array($request->search_columns)) {
            $request->search_columns = (array)$request->search_columns;
        }
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Sliders;
        if (!empty($request->where_column)) {
            $request->where_column = explode(",", $request->where_column);
            $request->where_value = explode(",", $request->where_value);
            if (!is_array($request->where_column) || !is_array($request->where_value)) {
                $request->where_column = (array)$request->where_column;
                $request->where_value = (array)$request->where_value;
            }
            foreach ($request->where_column as $k => $v) {
                $response = $response->where($v, $request->where_value[$k]);
            }
        }
        foreach ($request->search_columns as $column) {
			$response=$response->orWhere($column,"like","%". strtolower($request->search)."%")
                ->orWhere($column,"like","%".strtolower(ucfirst($request->search))."%")
                ->orWhere($column,"like","%".strtolower(ucwords($request->search))."%")
                ->orWhere($column,"like","%".strtolower(strtoupper($request->search))."%")
                ->orWhere($column,"like","%".strtoupper(str_replace('i','İ',$request->search))."%")
                ->orWhere($column,"like","%".strtolower(lcfirst($request->search))."%");
        }
        $response = $response->paginate($per_page);
		/*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->nutrients as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

	public function getByOrder(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Sliders;
        if (!empty($request->where_column)) {
            $request->where_column = explode(",", $request->where_column);
            $request->where_value = explode(",", $request->where_value);
            if (!is_array($request->where_column) || !is_array($request->where_value)) {
                $request->where_column = (array)$request->where_column;
                $request->where_value = (array)$request->where_value;
            }
            foreach ($request->where_column as $k => $v) {
                $response = $response->where($v, $request->where_value[$k]);
            }
        }
        $response = $response->orderBy($request->sortBy, $request->direction)->paginate($per_page);

        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->nutrients as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }
}
