<?php

namespace App\Http\Controllers\Api\Theme\informations;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

class indexController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $viewData="";
    public function __construct(){

    }

    public function city(){
        $city=DB::table("cities")->get();
        dd($city);
    }
    public function district(){
        $city=DB::table("cities")->get();
        dd($city);
    }

}

