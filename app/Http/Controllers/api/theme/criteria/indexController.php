<?php

namespace App\Http\Controllers\Api\Theme\Criteria;

use App\Http\Controllers\Controller;
use App\Model\Panel\Criteria;
use App\Model\Theme\Corporate;
use App\Model\Theme\FoodDecided;
use App\Model\Theme\Nutrients;
use Illuminate\Support\Facades\DB;
use App\Model\Theme\Settings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class indexController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $viewData = "";

    public function __construct()
    {
        $this->viewData = new \stdClass();
        $this->viewData->menus = new \stdClass();
        $this->viewData->menus->corporate = Corporate::where("isActive", 1)->get(["title", "seo_url"]);
        $this->viewData->menus->food_decides = FoodDecided::where("isActive", 1)->get(["title", "seo_url"]);
        $this->viewData->settings = Settings::where("isActive", 1)->orderBy("rank")->limit(1)->get();
    }

    public function index(Request $request)
    {

        $per_page = empty($request->per_page) ? 12 : (int)$request->per_page;
        if (!empty($request->search)) {
            $search = $request->search;
            $response = Nutrients::where(["isActive" => 1])
               ->where(function ($query) use($search){
                   $query->where("name","like","%".Str::strto("lower",$search) ."%")
                       ->orWhere("name","like","%".Str::strto("lower|ucfirst",$search) ."%")
                       ->orWhere("name","like","%".Str::strto("lower|ucwords",$search) ."%")
                       ->orWhere("name","like","%".Str::strto("lower|upper",$search) ."%")
                       ->orWhere("name","like","%".Str::strto("lower|capitalizefirst",$search) ."%");
               })
                ->paginate($per_page);

        } else {
            $response = Nutrients::where(["isActive" => 1])->paginate($per_page);
        }
        foreach ($response as $key => $item) {
            $response[$key]["criteria_values"] = "";
            foreach ($item->criteriaValues as $v) {
                $response[$key]["criteria_values"] = $v;
            }

        }
        if (!empty($response)) {
            return response()->json(["data" => $response], 200, [], JSON_UNESCAPED_UNICODE);

        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Gösterilecek Bir Ölçüt Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }

    public function detail($slug)
    {
        if (!empty($slug)) {
            $response = Criteria::where(["isActive" => 1, "slug" => $slug])->first();
            $image = CriteriaFile::where(["isActive" => 1, "criteria_id" => (string)$response->_id])->get();
            $values = CriteriaValues::where(["isActive" => 1, "criteria_id" => (string)$response->_id])->get();
            if (!empty($response)) {
                return response()->json(["data" => $response, "images" => $image, "values" => $values], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["title" => "Başarısız!", "msg" => "Besin Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Veri Yollamadın!", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }


}
