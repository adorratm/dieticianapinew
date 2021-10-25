<?php

namespace App\Http\Controllers\Api\Theme\Home;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Model\Panel\exercise_categories;
use App\Model\Panel\Recipes;
use App\Model\Theme\Blog;
use App\Model\Theme\Corporate;
use App\Model\Theme\Dieticians;
use App\Model\Theme\Exercises;
use App\Model\Theme\FoodDecided;
use App\Model\Theme\News;
use App\Model\Theme\Settings;
use App\Model\Theme\Sliders;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
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
        $this->viewData->settings = Settings::where("isActive", 1)->orderBy("rank")->limit(1)->first();
        $this->viewData->baseURL = urlencode(url("/"));
    }

    public function searchdieticians(Request $request)
    {
        $searchKey=$request->searchText;

        strlen($searchKey) > 0 ? $dieticians=Dieticians::where(function ($query) use ($searchKey) {
            $query->orWhere('name', 'like', '%' . $searchKey . '%');
            $query->orWhere('email', 'like', '%' . $searchKey . '%');
            $query->orWhere('phone', 'like', '%' . $searchKey . '%');
            $query->orWhere('hospitalName', 'like', '%' . $searchKey . '%');
        })
            ->paginate(2) : $dieticians=Dieticians::paginate(2);
        return response()->json([
           'data'=>$dieticians
        ],200);
    }

    public function dieticians()
    {
		/**
		*  Diyetisyenlerin Profil Fotoğrafları Çekildi.
		**/
		$per_page = empty($request->per_page) ? 2 : (int)$request->per_page;
        if (!empty($request->search)) :
            $search = $request->search;

            $dieticians = Dieticians::where(["isActive" => 1])
                ->where(function ($query) use ($search) {
                    $query->where("name", "like", "%" . Str::strto("lower", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|ucfirst", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|ucwords", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|upper", $search) . "%")
                        ->orWhere("name", "like", "%" . Str::strto("lower|capitalizefirst", $search) . "%");
                })
                ->paginate($per_page);
        else :
            $dieticians = Dieticians::where("isActive", 1)->paginate($per_page);
        endif;
        foreach ($dieticians as $dietician) {
            if (!empty($dietician->profilePhoto)) {
                $dieticians->profile_photo = $dietician->profilePhoto;
            }
        }
        return response()->json([
            'status'=>'success',
            'data'=>$dieticians
        ],200);
    }

    public function recipes()
    {
        $recipes=Recipes::paginate(2);
        return response()->json([
            'status'=>'success',
            'data'=>$recipes
        ],200);
    }

    public function searchrecipes(Request $request)
    {
        $searchKey=$request->searchText;

        strlen($searchKey) > 0 ? $recipes=Recipes::where(function ($query) use ($searchKey) {
            $query->orWhere('name', 'like', '%' . $searchKey . '%');
            $query->orWhere('description', 'like', '%' . $searchKey . '%');
            $query->orWhere('portion', 'like', '%' . $searchKey . '%');
            $query->orWhere('calorie', 'like', '%' . $searchKey . '%');
        })
            ->paginate(2) : $recipes=Recipes::paginate(2);
        return response()->json([
            'data'=>$recipes
        ],200);
    }

    public function categoryexercises($slug)
    {
        $exercises=exercise_categories::where('slug',$slug)->firstOrFail()->exercises()->orderBy('rank','ASC')->paginate(9);
        return response()->json([
            'status'=>'success',
            'data'=>$exercises
        ],200);
    }

    public function searchcategoryexercises(Request $request,$slug)
    {
        $searchKey=$request->searchText;

        strlen($searchKey) > 0 ? $exercisecategories=exercise_categories::where('slug',$slug)->firstOrFail()->exercises()->where(function ($query) use ($searchKey) {
            $query->orWhere('name', 'like', '%' . $searchKey . '%');
        })
            ->orderBy('rank','ASC')->paginate(9) : $exercisecategories=exercise_categories::where('slug',$slug)->firstOrFail()->exercises()->orderBy('rank','ASC')->paginate(9);
        return response()->json([
            'data'=>$exercisecategories
        ],200);
    }

    public function exercisecategories()
    {
        $exercisecategories=exercise_categories::orderBy('rank','ASC')->paginate(3);
        return response()->json([
            'status'=>'success',
            'data'=>$exercisecategories
        ],200);
    }

    public function searchexercisecategories(Request $request)
    {
        $searchKey=$request->searchText;

        strlen($searchKey) > 0 ? $exercisecategories=exercise_categories::where(function ($query) use ($searchKey) {
            $query->orWhere('name', 'like', '%' . $searchKey . '%');
        })
            ->orderBy('rank','ASC')->paginate(3) : $exercisecategories=exercise_categories::orderBy('rank','ASC')->paginate(2);
        return response()->json([
            'data'=>$exercisecategories
        ],200);
    }

    public function index()
    {
        $this->viewData->sliders = Sliders::where("isActive", 1)->get();
        $dcount = Dieticians::count();
        $start = rand(0, $dcount - 8);
        $dieticians = Dieticians::where("isActive", 1)->skip($start)->take(8)->get();
        $dieticians->makeHidden(["api_token", "updated_at", "isActive"]);
        $this->viewData->dieticians = $dieticians;
        $this->viewData->blog=BlogResource::collection(Blog::orderBy('created_at','DESC')->take(10)->get());
        foreach ($this->viewData->dieticians as $dietician) {
            $this->viewData->dieticians->profile_photo = $dietician->profilePhoto;
            /*
             * if (isset($dietician->profilePhoto) && !empty($dietician->profilePhoto)) {
                $this->viewData->dieticians->profile_photo = $dietician->profilePhoto;
            } else {
                $this->viewData->dieticians->profile_photo->img_url = "";
            }*/
        }

        $this->viewData->news = News::where("isActive", 1)->orderByDesc("rank")->limit(8)->get();
        foreach ($this->viewData->news as $news) {
            $this->viewData->news->dieticians = $news->dieticians;
        }

        return response()->json(["data" => $this->viewData], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function search(Request $request)
    {
        $per_page = (!empty($request->per_page) ? (int)$request->per_page : 12);
        if (!empty($request->table) && !empty($request->column)) :
            foreach ($request->table as $key => $item) :
                $response[$request->table] = DB::table($request->table)
                    ->where(["isActive" => 1])
                    ->where(function ($query) use ($request, $key) {
                        $query->where($request->column[$key], "like", "%" . strtolower($request->search) . "%")
                            ->orWhere($request->column[$key], "like", "%" . strtolower($request->search) . "%")
                            ->orWhere($request->column[$key], "like", "%" . strtolower($request->search) . "%")
                            ->orWhere($request->column[$key], "like", "%" . strtolower($request->search) . "%")
                            ->orWhere($request->column[$key], "like", "%" . strtolower($request->search). "%");
                    })
                    ->paginate($per_page);
            endforeach;
        endif;
        return response()->json(["data" => $response], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
