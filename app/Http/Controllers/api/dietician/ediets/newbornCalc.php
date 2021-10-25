<?php
namespace App\Http\Controllers\Api\Dietician\Ediets;

use App\Http\Controllers\Controller;
use App\Model\Panel\ediets;
use App\Model\Theme\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Model\Theme\NutrientsValues;
use App\Model\Panel\Diseases;
use Carbon\Carbon;

class newbornCalc extends Controller
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

    public function calorieCalculatorNewBorn(Request $request)
    {
        $user = DB::table("users")->where("_id", $request->id)->first();



        if($user->birthDate  == 1)
        {
            $oga = 800*$user->birthDate+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 2)
        {
            $oga = 800*$user->birthDate+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 3)
        {
            $oga = 800*$user->birthDate+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 4)
        {
            $oga = 800*$user->birthDate+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 5)
        {
            $oga = 800*$user->birthDate+$user->weight/1000;
            return $oga;
        }elseif ($user->birtDate == 6)
        {
            $oga = 800*$user->birthDate+$user->weight/1000;
            return $oga;
        }elseif ($user->birhDate == 7)
        {
            $oga = ($user->birthDate-6)*500+4800+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 8)
        {
            $oga = ($user->birthDate-6)*500+4800+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 9)
        {
            $oga = ($user->birthDate-6)*500+4800+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 10)
        {
            $oga = ($user->birthDate-6)*500+4800+$user->weight/1000;
            return $oga;
        }elseif ($user->birthDate == 11)
        {
            $oga = ($user->birthDate-6)*500+4800+$user->weight/1000;
            return $oga;
        }else{
            $oga = ($user->birthDate-6)*500+4800+$user->weight/1000;
            return $oga;
        }


    }

    public function nrp(Request $request)
    {
        $user = DB::table("users")->where("_id", $request->id)->first();
        $nrp = ($user->weight/$this->oga)*100;
        return $nrp;
    }

    public function newBornEnergy()
    {
        $nbe = $this->oga * 100;
        return $nbe;
    }
    public function newBurnProtein(Request $request)
    {
        $user = DB::table("users")->where("_id", $request->id)->first();
        $nbpMin = $user->weight*2;
        $nbpMax = $user->weight*4;

        return $nbpMin.$nbpMax;
    }
    public function newBurnLiquid(Request $request)
    {
        $user = DB::table("users")->where("_id", $request->id)->first();
        $nbl = $user->weight*150;
        return $nbl;
    }
}
