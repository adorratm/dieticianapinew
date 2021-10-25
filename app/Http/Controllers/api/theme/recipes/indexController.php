<?php

namespace App\Http\Controllers\Api\Theme\Recipes;

use App\Http\Controllers\Controller;
use App\Model\Theme\Corporate;
use App\Model\Theme\DieticianFile;
use App\Model\Theme\Dieticians;
use App\Model\Theme\FoodDecided;
use App\Model\Theme\Recipes;
use App\Model\Theme\RecipesFile;

use App\Model\Theme\Settings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

    public function show($slug)
    {
        $recipe=\App\Model\Panel\Recipes::where('slug',$slug)->firstOrFail();
        return response()->json([
            'success'=>true,
           'data'=>$recipe
        ],200);
    }

    public function index(Request $request)
    {
        $response = Recipes::where(["isActive" => 1])->where("slug", $request->slug)->first();
        $response["photos"] = RecipesFile::where("isActive", 1)->where("recipe_id", $response->id)->get();
        if (!empty($response->dietician_id)) :
            $response["dietician"] = Dieticians::where("isActive", 1)->where("_id", $response->dietician_id)->first();
            $response["dietician"]["img_url"] = DieticianFile::where("isActive", 1)->where("type", "profile_photo")->where("dieticians_id", $response["dietician"]->id)->first()->img_url;
        endif;
        if (!empty($response)) {
            return response()->json(["data" => $response], 200, [], JSON_UNESCAPED_UNICODE);
        } else {

            return response()->json(["title" => "Başarısız!", "msg" => "Gösterilecek Bir Besin Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
