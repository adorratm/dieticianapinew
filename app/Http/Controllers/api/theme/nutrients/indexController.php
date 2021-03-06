<?php

namespace App\Http\Controllers\Api\Theme\Nutrients;

use App\Http\Controllers\Controller;
use App\Model\Theme\Corporate;
use App\Model\Theme\FoodDecided;
use App\Model\Theme\Nutrients;
use App\Model\Theme\NutrientsFile;
use App\Model\Theme\NutrientsValues;
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
		$response = new Nutrients;
		$response = $response->with("nutrients");
		$response = $response->where(["isActive" => 1]);
        if (!empty($request->search)) {
            $search = $request->search;
                $reponse=$response->where(function($query) use($search){
					return $query->where("name","like","%". strtolower($search)."%")
						->orWhere("description","like","%". strtolower($search)."%");
//						->orWhere("name","like","%".Str::strto("lower|ucwords", $search)."%")
//						->orWhere("name","like","%".Str::strto("lower|upper", $search)."%")
//						->orWhere("name","like","%".Str::strto("lower|capitalizefirst", $search)."%");
				});
			$response=$response->paginate($per_page);
        } else {
            $response = $response->paginate($per_page);
        }

        if (!empty($response)) {
            return response()->json(["data" => $response,"empty_url"=>"uploads/settings/preparing/my.jpg"], 200, [], JSON_UNESCAPED_UNICODE);

        } else {
            return response()->json(["title" => "Ba??ar??s??z!", "msg" => "G??sterilecek Bir Besin Bulunamam????t??r.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }

    public function detail($slug)
    {
        if (!empty($slug)) {
            $response = Nutrients::where(["isActive" => 1, "slug" => $slug])->first();
            $image = NutrientsFile::where(["isActive" => 1, "nutrients_id" => (string)$response->_id])->get();
            $values = NutrientsValues::where(["isActive" => 1, "nutrients_id" => (string)$response->_id])->get();
            $criteria = DB::table("nutrients_criteria")->where(["isActive" => 1, "nutrients_id" => (string)$response->_id])->get();
            if (!empty($response)) {
                return response()->json(["data" => $response, "criterias" => $criteria, "images" => $image, "values" => $values, "fordata" => $values], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["title" => "Ba??ar??s??z!", "msg" => "Besin Bulunamam????t??r.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["title" => "Ba??ar??s??z!", "msg" => "Veri Yollamad??n!", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }
}
