<?php

namespace App\Http\Controllers\api\theme\blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Model\Theme\Blog;
use App\Model\Theme\BlogCategory;
use App\Model\Theme\Corporate;
use App\Model\Theme\DieticianFile;
use App\Model\Theme\Dieticians;
use App\Model\Theme\Exercises;
use App\Model\Theme\ExercisesFile;
use App\Model\Theme\FoodDecided;
use App\Model\Theme\Recipes;
use App\Model\Theme\RecipesFile;
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
        $response = Blog::query();
        $response = $response->with("writable");
        $response = $response->where(["status" => "active"]);
        if (!empty($request->search)) {
            $search = $request->search;
            foreach ($request->search_columns as $k=>$column) {
                $response=$response->where(function($query) use ($column,$request){
                    $query->orwhere($column,"like","%". strtolower($request->search)."%")
                        ->orWhere($column,"like","%".strtolower(ucfirst($request->search))."%")
                        ->orWhere($column,"like","%".strtolower(ucwords($request->search))."%")
                        ->orWhere($column,"like","%".strtolower(strtoupper($request->search))."%")
                        ->orWhere($column,"like","%".strtoupper(str_replace('i','İ',$request->search))."%")
                        ->orWhere($column,"like","%".strtolower(lcfirst($request->search))."%");
                });
            }
            $response=$response->paginate($per_page);
        } else {
            $response = $response->paginate($per_page);
        }

        if (!empty($response)) {
            return response()->json(["data" => BlogResource::collection($response),"empty_url"=>"uploads/settings/preparing/my.jpg"], 200, [], JSON_UNESCAPED_UNICODE);

        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Gösterilecek Bir Yazı Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }

    public function getPostsByCategory($id)
    {

        $per_page = empty($request->per_page) ? 12 : (int)$request->per_page;
        $response=BlogCategory::where('_id',$id)->orWhere('slug',$id)->get()->first()->posts()->with('writable')->where('status','active');
//        $response = $response->with("writable");
//        $response = $response->where(["status" => "active"]);
        if (!empty($request->search)) {
            $search = $request->search;
            foreach ($request->search_columns as $k=>$column) {
                $response=$response->where(function($query) use ($column,$request){
                    $query->orwhere($column,"like","%". strtolower($request->search)."%")
                        ->orWhere($column,"like","%".strtolower(ucfirst($request->search))."%")
                        ->orWhere($column,"like","%".strtolower(ucwords($request->search))."%")
                        ->orWhere($column,"like","%".strtolower(strtoupper($request->search))."%")
                        ->orWhere($column,"like","%".strtolower(lcfirst($request->search))."%");
                });
            }
            $response=$response->paginate($per_page);
        } else {
            $response = $response->paginate($per_page);
        }

        if (!empty($response)) {
            return response()->json(["data" => BlogResource::collection($response),"empty_url"=>"uploads/settings/preparing/my.jpg"], 200, [], JSON_UNESCAPED_UNICODE);

        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Gösterilecek Bir Yazı Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }

    public function detail($slug)
    {
        if (!empty($slug)) {
            $response = Blog::where('slug',$slug)->get()->first();
            if (!empty($response)) {
                return response()->json(["data" => new BlogResource($response)], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["title" => "Başarısız!", "msg" => "Yazı Bulunamamıştır.", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["title" => "Başarısız!", "msg" => "Veri Yollamadın!", "success" => false, "data" => null], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
