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

class pubertyCalc extends Controller
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
}
