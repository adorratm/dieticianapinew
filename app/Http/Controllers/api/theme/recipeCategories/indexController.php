<?php

namespace App\Http\Controllers\Api\Theme\recipeCategories;

use App\Http\Controllers\Controller;
use App\Model\Theme\Corporate;
use App\Model\Theme\RecipeCategories;
use App\Model\Theme\Recipes;
use Illuminate\Support\Facades\DB;

use App\Model\Theme\Settings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $this->viewData->settings = Settings::where("isActive", 1)->orderBy("rank")->limit(1)->get();
    }

    public function index(Request $request)
    {
        $per_page = empty($request->per_page) ? 12 : (int)$request->per_page;
        if (!empty($request->search)) {
            $search = $request->search;
            $response = RecipeCategories::where(["isActive" => 1])
                ->where(function ($query) use ($search) {
                    $query->where("name", "like", "%" . Str::strto("lower", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|ucfirst", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|ucwords", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|upper", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|capitalizefirst", $search) . "%");
                })
                ->paginate($per_page);
        } else {
            $response = RecipeCategories::where(["isActive" => 1])->paginate($per_page);
        }
        foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            if (!empty($item->recipeCategoryPhoto)):
                foreach ($item->recipeCategoryPhoto as $v) {
                    $response[$key]["img_url"] = url(Storage::url($v->img_url));
                }
            endif;
        }
        if (!empty($response)) {
            return response()->json(["data" => $response], 200, [], JSON_UNESCAPED_UNICODE);

        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Gösterilecek Bir Besin Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }

    public function detail($slug, Request $request)
    {
        $per_page = empty($request->per_page) ? 12 : (int)$request->per_page;
        if (!empty($slug)) {
            $response["categories"] = RecipeCategories::where("isActive", 1)->where("slug", $slug)->first();
            if (!empty($request->search)):
                $response["recipes"] = Recipes::where("category_id", $response["categories"]->id)
                    ->where(["isActive" => 1])
                    ->where(function ($query) use ($request) {
                        $query->where("name", "like", "%" . Str::strto("lower", $request->search) . "%")
                            ->orWhere("name", "like", "%" . Str::strto("lower|ucfirst", $request->search) . "%")
                            ->orWhere("name", "like", "%" . Str::strto("lower|ucwords", $request->search) . "%")
                            ->orWhere("name", "like", "%" . Str::strto("lower|upper", $request->search) . "%")
                            ->orWhere("name", "like", "%" . Str::strto("lower|capitalizefirst", $request->search) . "%");
                    })
                    ->paginate($per_page);
            else:
                $response["recipes"] = Recipes::where("category_id", $response["categories"]->id)->where("isActive", 1)->paginate($per_page);

            endif;
            if (!empty($response["recipes"])):
                foreach ($response["recipes"] as $key => $item) {

                    $response["recipes"][$key]["img_url"] = "uploads/settings/preparing/my.jpg";
                    $response["recipes"][$key]["dietician"] = "Anonim";
                    if (!empty($item->recipePhoto)):
                        foreach ($item->recipePhoto as $v) {
                            $response["recipes"][$key]["img_url"] = $v->img_url;
                        }
                    endif;

                    if (!empty($item->recipeDietician)):
                        $response["recipes"][$key]["dietician"] = new \stdClass();
						$response["recipes"][$key]["dietician"]->name = $item->recipeDietician->name;
						$response["recipes"][$key]["dietician"]->slug = $item->recipeDietician->slug;
						$response["recipes"][$key]["dietician"]->department = $item->recipeDietician->department;
                        $photo= DB::table("dieticians_file")->where("dieticians_id", $item->recipeDietician->_id)->where("type", "profile_photo")->first();
                        if (!empty($photo)):
                          $response["recipes"][$key]["dietician"]->profile_photo = $photo["img_url"];
                        endif;
					endif;
                    	unset($response["recipes"][$key]->dietician_id);
                }
            endif;
            if (!empty($response)) {
                return response()->json(["data" => $response], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["title" => "Başarısız!", "msg" => "Besin Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Veri Yollamadın!", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }
}
