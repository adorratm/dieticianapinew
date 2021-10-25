<?php

namespace App\Http\Controllers\Api\Dietician\Recipes;

use App\Http\Controllers\Controller;
use App\Model\Panel\Dieticians;
use App\Model\Panel\Recipes;
use App\Model\Theme\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\tools_helper;

class indexController extends Controller
{
    public $user = "";

    public function __construct(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = Dieticians::where("api_token", $bearer)
            ->first();
        if ($user) {
            $this->user = $user;
        }
    }

    public function index()
    {
        $data["criterias"] = DB::table("criteria")->where("isActive", 1)->get();
        $data["nutrients"] = DB::table("nutrients")->where("isActive", 1)->get();
        return response()->json(["data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function save()
    {
        $data["criterias"] = DB::table("criteria")->where("isActive", 1)->get();
        $data["nutrients"] = DB::table("nutrients")->where("isActive", 1)->get();
        return response()->json(["data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|unique:recipes",
            "description" => "required",
            "vitaminName" => "required",
            "vitaminValue" => "required",
            "vitaminType" => "required",
            "criteriaName" => "required",
            "criteriaValue" => "required",
            "criteriaType" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data = $request->except("_token");
            $count = DB::table("recipes")->count();
            $data["rank"] = $count + 1;
            $data["isActive"] = 1;
            $data["slug"] = Str::slug($data["name"], "-");
            $data["createdAt"] = date("d/m/Y", strtotime("now"));

            $data["dietician_id"] = $this->user->_id;
            unset($data["vitaminName"]);
            unset($data["vitaminValue"]);
            $nutrients = DB::table("recipes")->insertGetId($data);
            foreach ($request->vitaminName as $key => $vitamin) {
                $add_data["title"] = $vitamin;
                $add_data["value"] = $request->vitaminValue[$key];
                $add_data["type"] = $request->vitaminType[$key];
                $add_data["isActive"] = 1;
                $add_data["recipes_id"] = (string)$nutrients;
                $add_data["rank"] = $key + 1;

                $recipes_value = DB::table("recipes_value")->insert($add_data);
            }
            foreach ($request->criteriaName as $key => $criteria) {
                $add_data["title"] = $criteria;
                $add_data["value"] = $request->criteriaValue[$key];
                $add_data["type"] = $request->criteriaType[$key];
                $add_data["recipes_id"] = (string)$nutrients;
                $add_data["isActive"] = 1;
                $add_data["recipe_criteria_id"] = $request->criteriaNutrient[$key];
                $add_data["rank"] = $key + 1;

                $recipes_criteria = DB::table("recipes_criteria_value")->insert($add_data);
            }
            unset($request->vitaminName);
            unset($request->criteriaName);
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Besin Başarıyla Eklendi", "data" => $nutrients, "name" => $data["name"]], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getFile($id)
    {

        if (!empty($id)) {
            $data = DB::table("nutrients")->where("nutrients_id", $id)->get();
            if (!empty($data)) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Verileriniz Geldi", "data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Kayıda Ait Bir Dosya Bulunamadı."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "İd Paremetresi Boş Olamaz!"], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function fileStore(Request $request, $id)
    {
        if (!empty($request->file())) {
            $status = 1;
            foreach ($request->file("file") as $key => $file) :

                $strFileName = Str::slug($request->title);
                $extension = $file->extension();
                $fileNameWithExtension = $strFileName . "-" . rand(0, 99999999999) . "-" . time() . "." . $extension;
                $path = $file->storeAs("uploads/recipes/{$strFileName}/", $fileNameWithExtension, "public");
                $count = DB::table("recipes_file")->where("recipes_id", $id)->count();
                $data["recipes_id"] = $id;
                $data["img_url"] = $path;
                $data["isActive"] = 1;
                $data["rank"] = $count + 1;
                $data["isCover"] = 0;
                $add = DB::table("recipes_file")->insert($data);
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
    }

    public function edit($id)
    {
        $nutrients = DB::table("recipes")
            ->where("_id", $id)->first();

        if ($nutrients) {

            $nutrients["recipes"] = DB::table("recipes")->where("isActive", 1)->get();
            $nutrients["criterias"] = DB::table("criteria")->where("isActive", 1)->get();
            $nutrients["nutrients"] = DB::table("nutrients")->where("isActive", 1)->get();
            $nutrients["images"] = DB::table("recipes_file")->where("recipes_id", $id)->get();
            $nutrients["values"] = DB::table("recipes_value")->where("recipes_id", $id)->get();
            $nutrients["recipes_criteria_values"] = DB::table("recipes_criteria_value")->where("recipes_id", $id)->get();
            return response(["success" => true, "data" => $nutrients], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Böyle Bir Veri Bulunamadı!"], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update($id, Request $request)
    {
        $data = $request->except("_token");
        if (!empty($data["_id"])) {
            unset($data["_id"]);
        }
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "description" => "required",
            "vitaminName" => "required",
            "vitaminValue" => "required",
            "vitaminType" => "required",
            "criteriaName" => "required",
            "criteriaValue" => "required",
            "criteriaType" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $control = DB::table("recipes")->where("_id", $id)->where("dietician_id", $this->user->_id)->first();
            if ($control) {
                $data = $request->except("_token");
                $data["slug"] = Str::slug($data["name"], "-");

                $destroy = DB::table("recipes_value")->where("recipes_id", $id)->delete();

                foreach ($request->vitaminName as $key => $vitamin) {
                    $add_data["title"] = $vitamin;
                    $add_data["value"] = $request->vitaminValue[$key];
                    $add_data["type"] = $request->vitaminType[$key];
                    $add_data["isActive"] = 1;
                    $add_data["recipes_id"] = $id;
                    $add_data["rank"] = $key + 1;

                    $recipes_value = DB::table("recipes_value")->insert($add_data);
                }
                $destroy = DB::table("recipes_criteria_value")->where("recipes_id", $id)->delete();

                foreach ($request->criteriaName as $key => $criteria) {
                    $add_data["title"] = $criteria;
                    $add_data["value"] = $request->criteriaValue[$key];
                    $add_data["type"] = $request->criteriaType[$key];
                    $add_data["recipes_id"] = $id;
                    $add_data["isActive"] = 1;
                    $add_data["recipe_criteria_id"] = $request->criteriaNutrient[$key];
                    $add_data["rank"] = $key + 1;

                    $recipes_criteria = DB::table("recipes_criteria_value")->insert($add_data);
                }
                $data = DB::table("recipes")->where("_id", $id)->where("dietician_id", $this->user->_id)->update($data);
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarlarınız Başarıyla Güncellendi", "data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function destroy($id)
    {
        $nutrients = DB::table("recipes")
            ->where("_id", $id)->delete();
        if ($nutrients) {
            $nutrients = DB::table("recipes")->get();
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarınız Başarıyla Silindi", "data" => $nutrients], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Ayarınız Silinirken Bir Hata İle Karşılaşıldı."], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getAll(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Recipes;
        $response = $response->with("recipes");
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
            foreach ($item->recipes as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }

        }*/

        return response()->json(["data" => $response, "empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

    public function getBySearch(Request $request)
    {
        if (empty($request->search) || $request->search == "null") {
            return Redirect::to(route("dietician.recipes.getAll", "table={$request->table}&per_page={$request->per_page}"));
        }
        $request->search_columns = explode(",", $request->search_columns);
        if (!is_array($request->search_columns)) {
            $request->search_columns = (array)$request->search_columns;
        }
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Recipes;
        $response = $response->with("recipes");
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
        foreach ($request->search_columns as $k => $column) {
            $response = $response->where(function ($query) use ($column, $request) {
                $query->orwhere($column, "like", "%" . tools_helper::strto("lower", $request->search) . "%")
                    ->orWhere($column, "like", "%" . tools_helper::strto("lower|ucfirst", $request->search) . "%")
                    ->orWhere($column, "like", "%" . tools_helper::strto("lower|ucwords", $request->search) . "%")
                    ->orWhere($column, "like", "%" . tools_helper::strto("lower|upper", $request->search) . "%")
                    ->orWhere($column, "like", "%" . tools_helper::strto("lower|capitalizefirst", $request->search) . "%");
            });
        }
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->recipes as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response, "empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

    public function getByOrder(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Recipes;
        $response = $response->with("recipes");
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
            foreach ($item->recipes as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response, "empty_url" => "uploads/settings/preparing/my.jpg"]);
    }
}
