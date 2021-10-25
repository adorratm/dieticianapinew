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

class childrenCalc extends Controller
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

    public function childrenWeight(Request $request)
    {

        $user = DB::table("users")->where("_id", $request->id)->first();
        $userAge = User::userBirthCalc($user->birthDay);
        $ogaTable = DB::table("oga_table")->where("age", $userAge)->first();
        return $ogaTable;
    }

    public function childrenlength (Request $request)
    {
        $user = DB::table("users")->where("_id", $request->id)->first();
        $userAge = User::userBirthCalc($user->birthDay);
        $childrenLeng = DB::table("oga_bki")->where("age", $userAge)->first();
        return $childrenLeng;
    }

    public function childrenNutritionalRisk(Request $request)
    {
        $user = DB::table("users")->where("_id", $request->id)->first();
        $userAge = User::userBirthCalc($user->birthDay);
        $ogaTable = DB::table("oga_table")->where("age", $userAge)->first();
        $calcNrisk = ($user->weight / $ogaTable->value)*100;
        $dbNrisk = DB::table("users")->where("max", $calcNrisk)->first();
        if ($dbNrisk >= $calcNrisk)
        {
            return $dbNrisk->status;

        }else{

            return $dbNrisk->status;
        }
    }
}
