<?php

namespace App\Http\Controllers\Api\Dietician\Ediets;

use App\Http\Controllers\Controller;
use App\Model\Panel\edietfoods;
use App\Model\Panel\ediets;
use App\Model\Theme\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Model\Theme\NutrientsValues;
use App\Model\Panel\Diseases;
use Carbon\Carbon;
use App\Helpers\tools_helper;


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
        $ediets = DB::table("ediets")->get();
        if ($ediets) {
            return response()->json(["success" => true, "data" => $ediets], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "E-Diyetler Listelenirken Bir Hata Olşutu!"], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }


    public function save($id, Request $request)
    {
        $user = DB::table("users")->where("_id", $id)->first();
        $currentDate = new Carbon($user["birthDate"]);
        $monthDifference = $currentDate->diffInMonths(Carbon::now(), false);
        $yearDifferance = $currentDate->diffInYears(Carbon::now(), false);
        $data["user"] = $user;
        $data["year"] = $yearDifferance;
        $data["month"] = $monthDifference;
        $data["criteria"] = DB::table("criteria")->where("isActive", 1)->get();
        $data["diseases"] = DB::table("diseases")->where("isActive", 1)->get();
        $data["meals"] = DB::table("meals")->get();
        $data["edietfoods"] = DB::table("edietfoods")->where("isActive", 1)->get();
        $stressFacLabel = DB::table("factors")->get();
        $data["exercises"] = DB::table("exercises")->get();


        $result = [];
        $type = "Normal";
        if (!empty($user["special_case"]) && $user["special_case"] == "YOK"):
            $type = "Normal";
        endif;
        if (!empty($user["special_case"]) && $user["special_case"] == "EMZİKLİ"):
            $type = "Emzikli";
        endif;
        if (!empty($user["special_case"]) && $user["special_case"] == "HAMİLE"):
            $type = "Hamile";
        endif;


        if ($yearDifferance > 18) {
            $data["adultCalorieCalc"] = $this->adultCalorieCalc($user["_id"]);

        } elseif ($yearDifferance > 10 && $yearDifferance <= 18) {
            $data["adultCalorieCalc"] = $this->adultCalorieCalc($user["_id"]);
        } elseif ($yearDifferance > 1 && $yearDifferance <= 10) {
            $data["adultCalorieCalc"] = $this->adultCalorieCalc($user["_id"]);
        } elseif ($yearDifferance > 0 && $yearDifferance <= 1) {
            $data["adultCalorieCalc"] = $this->adultCalorieCalc($user["_id"]);
        }

        foreach ($stressFacLabel as $stressFacLabels) {

            $stressFacValue = DB::table("factors_value")->where(["factors_id" => (string)$stressFacLabels['_id'], "type" => $type])
                ->where("minAge", "<=", $yearDifferance)
                ->where("maxAge", ">=", $yearDifferance)
                ->get();
            if (!in_array(["title" => $stressFacLabels["title"], "values" => $stressFacValue], $result)) {
                array_push($result, ["title" => $stressFacLabels["title"], "values" => $stressFacValue]);
            }
        }
        $data["factors"] = $result;
        $data["test"] = "test";
        if ($request->method() === "POST") {
            $data["bmh"] = $this->HerrisBendricFormul($user);
            $data["factores"] = $this->stresfaccalc($request->selectedFactors);
            $factorFirst = $data["factores"][3]["value"];
            $factorSecond = $data["factores"][4]["value"];
            $factorThird = $data["factores"][5]["value"];


            $data["factorFirst"] = $calcX = $data["bmh"] * $factorFirst;
            $data["factorSecond"] = $calcX = $data["bmh"] * $factorSecond;
            $data["factorThird"] = $calcX = $data["bmh"] * $factorThird;
            $data["factorFour"] = $data["bmh"] + (($data["factorFirst"] - $data["bmh"]) + ($data["factorSecond"] - $data["bmh"]) + ($data["factorThird"] - $data["bmh"]));

            $data["dietFoods"] = $this->dietPlans($request->selectedMeals, $data["year"]);
            $data["mealss"] = $this->calcMeals($data["factorFour"], $request->selectedMeals, $data["dietFoods"]);


        }


        return response()->json(["data" => $data], 200);
    }



    public function calcMeals($totalcalorie, $selectedMeals, $dietFoods)
    {
        $meals = [];
        foreach ($selectedMeals as $key => $meal) {
            $mealcalorie = intval($totalcalorie / count($selectedMeals));
            $mealfoods = [];
            $calorie = 0;
//                $dietFoods=(array)$dietFoods;
//                shuffle($dietFoods);
//            $array = (array)$dietFoods;
//            shuffle($array);
//
//            $object = new \stdClass();
//            foreach ($array as $key => $value)
//            {
//                $object->$key = $value;
//            }
//            return $object;
//            $dietFoods=array($dietFoods);
//            return $dietFoods;
//            $array = json_decode(json_encode($dietFoods), true);
//            return $array;

            foreach ($dietFoods as $food) {
                if (in_array($meal['_id']['$oid'], $food->selectedMeals)) {
                    $bool=false;
                    foreach ($mealfoods as $mealfood){
                        if(explode(' ',$food->name)[0]==explode(' ',$mealfood->name)[0]){
                            $bool=true;
                            break;
                        }
                        }
                    if(random_int(1,3)==1 && $bool==false){
                        array_push($mealfoods, $food);
                        $calorie = $calorie + $food->calorie;
                    }
                }
                if ($calorie + 100 > $mealcalorie)
                    break;
            }
//                $meals[$meal['_id']['$oid']]=$mealfoods;
            $meals[$meal['name']]['foods'] = $mealfoods;
            $meals[$meal['name']]['mealname'] = $meal['name'];
            $meals[$meal['name']]['maxmealcalorie'] = $mealcalorie;
            $meals[$meal['name']]['totalmealcalorie'] = $calorie;
            $meals[$meal['name']]['rank'] = $meal['rank'];
        }
        usort($meals,function($a,$b){

            return strcmp($a['rank'],$b['rank']);
        });
        return $meals;

    }

    public function stresfaccalc($selectedFactors)
    {
        foreach ($selectedFactors as $key => $value) {
            if (!in_array(DB::table("factors_value")->where("_id", $value)->get(), $selectedFactors)):
                $stressValue = array_push($selectedFactors, DB::table("factors_value")->where("_id", $value)->first());
            endif;

        }
        return $selectedFactors;
    }

    public function HerrisBendricFormul($user)
    {
        $currentDate = new Carbon($user["birthDate"]);
        $yearDifferance = $currentDate->diffInYears(Carbon::now(), false);
        if ($user["gender"] === 'Erkek') {
            $bmh = 66 + (13.7 * $user["weight"]) + (5 * $user["size"]) - (6 * $yearDifferance);
        } else {
            $bmh = 655 + (9.6 * $user["weight"]) + (1.8 * $user["size"]) - (4.7 * $yearDifferance);
        }
        return $bmh;
    }

    public function adultCalorieCalc($id)
    {
        $user = DB::table("users")->where("_id", $id)->first();
        $currentDate = new Carbon($user["birthDate"]);
        $monthDifference = $currentDate->diffInMonths(Carbon::now(), false);
        $yearDifferance = $currentDate->diffInYears(Carbon::now(), false);

        //BKİ HESAPLADIK
        $data["bki"] = round($user["weight"] / (($user["size"] / 100) * ($user["size"] / 100)), 2);
        //Erkekler için BKİ Heris Bendric Formülü
        if ($data["bki"] < 17.99 && $user["gender"] === 'Erkek') {
            $bkifiveplus = $data["bki"] + 5;
            //OGA HESAPLA
            $data["oga"] = $bkifiveplus * (($user["size"] / 100) * ($user["size"] / 100));
            //$data["bkiprint"] = echo "";
        } elseif (18 < $data["bki"] && $data["bki"] < 27.99 && $user["gender"] === 'Erkek') {
            $bkidefault = $data["bki"];
            //OGA HESAPLA
            $data["oga"] = $bkidefault * (($user["size"] / 100) * ($user["size"] / 100));
        } elseif ($data["bki"] > 28 && $user["gender"] === 'Erkek') {
            $bkifiveminus = $data["bki"] - 5;
            //OGA HESAPLA
            $data["oga"] = $bkifiveminus * (($user["size"] / 100) * ($user["size"] / 100));
        }
        //Kadınlar için BKİ Heris Bendric Formülü
        if ($data["bki"] < 16.99 && $user["gender"] === 'Kadın') {
            $bkifourplusgirl = $data["bki"] + 4;
            //OGA HESAPLA
            $data["oga"] = $bkifourplusgirl * (($user["size"] / 100) * ($user["size"] / 100));
            //$data["bkiprint"] = echo "";
        } elseif (17 < $data["bki"] && $data["bki"] < 25.99 && $user["gender"] === 'Kadın') {
            $bkidefaultgirl = $data["bki"];
            //OGA HESAPLA
            $data["oga"] = $bkidefaultgirl * (($user["size"] / 100) * ($user["size"] / 100));
        } elseif ($data["bki"] > 26 && $user["gender"] === 'Kadın') {
            $bkifiveminusgirl = $data["bki"] - 5;
            //OGA HESAPLA
            $data["oga"] = $bkifiveminusgirl * (($user["size"] / 100) * ($user["size"] / 100));
        }


        return response()->json(["data" => $data], 200);


    }

    public function dietPlans($selectedMeals, $year)
    {
        $selectedMealsIds = [];
        foreach ($selectedMeals as $key => $value):
            array_push($selectedMealsIds, (string)$value["_id"]["\$oid"]);
        endforeach;

        $selectedFoods = edietfoods::whereIn("selectedMeals", $selectedMealsIds)->AgeFilter($year)->get();
        foreach ($selectedFoods as $row) {

            $foodsitems[] = array(
                'meals' => $row['meals']
            );

        }


        return $selectedFoods;

    }


    public function store(Request $request)
    {
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
            $data = $request->except("_token");
            $count = DB::table("ediets")->count();
            $data["rank"] = $count + 1;
            $data["isActive"] = 1;
            $data["slug"] = Str::slug($data["name"], "-");
            unset($data["vitaminName"]);
            unset($data["vitaminValue"]);
            $nutrients = DB::table("ediets")->insertGetId($data);
            foreach ($request->vitaminName as $key => $vitamin) {
                $add_data["title"] = $vitamin;
                $add_data["value"] = $request->vitaminValue[$key];
                $add_data["type"] = $request->vitaminType[$key];
                $add_data["isActive"] = 1;
                $add_data["ediets_id"] = (string)$nutrients;
                $add_data["rank"] = $key + 1;
                $nutrients_value = DB::table("ediets_value")->insert($add_data);
            }
            foreach ($request->criteriaName as $key => $criteria) {
                $add_data["title"] = $criteria;
                $add_data["value"] = $request->criteriaValue[$key];
                $add_data["type"] = $request->criteriaType[$key];
                $add_data["isActive"] = 1;
                $add_data["ediets_id"] = (string)$nutrients;
                $add_data["rank"] = $key + 1;
                $nutrients_criteria = DB::table("ediets_criteria")->insert($add_data);
            }
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Besin Başarıyla Eklendi", "data" => $nutrients, "name" => $data["name"]], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getFile($id)
    {

        if (!empty($id)) {
            $data = DB::table("ediets")->where("ediets_id", $id)->get();
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
                $path = $file->storeAs("uploads/ediets/{$strFileName}/", $fileNameWithExtension, "public");
                $count = DB::table("ediets_file")->where("ediets_id", $id)->count();
                $data["ediets_id"] = $id;
                $data["img_url"] = $path;
                $data["isActive"] = 1;
                $data["rank"] = $count + 1;
                $data["isCover"] = 0;
                $add = DB::table("ediets_file")->insert($data);
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
        $nutrients = DB::table("ediets")
            ->where("_id", $id)->first();

        if ($nutrients) {

            $nutrients["criterias"] = DB::table("criteria")->where("isActive", 1)->get();
            $nutrients["images"] = DB::table("ediets_file")->where("ediets_id", $id)->get();
            $nutrients["values"] = DB::table("ediets_value")->where("ediets_id", $id)->get();
            $nutrients["diseases"] = DB::table("diseases")->get();
            $nutrients["meals"] = DB::table("meals")->get();
            $nutrients["criteria_values"] = DB::table("ediets_criteria")->where("ediets_id", $id)->get();
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
            $data = $request->except("_token");
            $data["slug"] = Str::slug($data["name"], "-");
            $destroy = DB::table("ediets_value")->where("ediets_id", $id)->delete();
            foreach ($request->vitaminName as $key => $vitamin) {
                $add_data["title"] = $vitamin;
                $add_data["value"] = $request->vitaminValue[$key];
                $add_data["type"] = $request->vitaminType[$key];
                $add_data["isActive"] = 1;
                $add_data["ediets_id"] = $id;
                $add_data["rank"] = $key + 1;

                $nutrients_value = DB::table("ediets_value")->insert($add_data);
            }
            $destroy = DB::table("ediets_criteria")->where("ediets_id", $id)->delete();

            foreach ($request->criteriaName as $key => $criteria) {
                $add_data["title"] = $criteria;
                $add_data["value"] = $request->criteriaValue[$key];
                $add_data["type"] = $request->criteriaType[$key];
                $add_data["isActive"] = 1;
                $add_data["ediets_id"] = $id;
                $add_data["rank"] = $key + 1;

                $nutrients_criteria = DB::table("ediets_criteria")->insert($add_data);
            }
            $data = DB::table("ediets")->where("_id", $id)->update($data);
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarlarınız Başarıyla Güncellendi", "data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy($id)
    {
        $nutrients = DB::table("ediets")
            ->where("_id", $id)->delete();
        if ($nutrients) {
            $nutrients = DB::table("ediets")->get();
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarınız Başarıyla Silindi", "data" => $nutrients], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Ayarınız Silinirken Bir Hata İle Karşılaşıldı."], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }


    public function getAll(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new ediets;
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
        $response = $response->with("ediets");
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->ediets as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }

        }*/

        return response()->json(["data" => $response, "empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

    public function getBySearch(Request $request)
    {
        if (empty($request->search) || $request->search == "null") {
            return Redirect::to(route("panel.ediets.getAll", "table={$request->table}&per_page={$request->per_page}"));
        }
        $request->search_columns = explode(",", $request->search_columns);
        if (!is_array($request->search_columns)) {
            $request->search_columns = (array)$request->search_columns;
        }
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new ediets;
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
        $response = $response->with("ediets");
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->ediets as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response, "empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

    public function getByOrder(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new ediets;
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
        $response = $response->with("ediets");
        $response = $response->orderBy($request->sortBy, $request->direction)->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->ediets as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response, "empty_url" => "uploads/settings/preparing/my.jpg"]);
    }


}
