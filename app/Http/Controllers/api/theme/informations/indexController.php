<?php

namespace App\Http\Controllers\Api\Theme\informations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

class indexController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $viewData = "";

    public function __construct()
    {

    }

    public function city()
    {
        $data["cities"] = DB::table("cities") ->select("name","towns")->get();
        return response()->json(["data"=>$data],200,[],JSON_UNESCAPED_UNICODE);
    }

    public function town(Request $request)
    {

        $data=explode(",",$request->id);

        $town = DB::table("towns")->select("name","districts")->whereIn("_id",$data)->get();
        return response()->json(["towns"=>$town],200,[],JSON_UNESCAPED_UNICODE);
    }

    public function district(Request $request)
    {
        $data=explode(",",$request->id);
        $districts = DB::table("districts")->select("name","neighborhoods")->whereIn("_id",$data)->get();
        return response()->json(["districts"=>$districts],200,[],JSON_UNESCAPED_UNICODE);
    }
    public function neighborhood(Request $request)
    {
        $data=explode(",",$request->id);

        $neighborhoods= DB::table("neighborhoods")->select("name")->whereIn("_id",$data)->get();
        return response()->json(["neighborhoods"=>$neighborhoods],200,[],JSON_UNESCAPED_UNICODE);
    }

}

