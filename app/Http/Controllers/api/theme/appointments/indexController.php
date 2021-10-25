<?php

namespace App\Http\Controllers\api\theme\appointments;

use App\Http\Controllers\Controller;
use App\Model\Theme\Appointment;
use App\Model\Theme\Dietician;
use App\Model\Theme\Dieticians;
use App\Model\Theme\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class indexController extends Controller
{


    public function index()
    {
        $appointments=Appointment::all();
        return response()->json([
            'success'=>true,
            'data'=>$appointments
        ],200);
    }

    public function show($slug)
    {
        $dietician=Dieticians::where('slug',$slug)->firstOrFail();
        $appointments=Appointment::where('dietician_id',$dietician->_id)->get();
        return response()->json(['data'=>$appointments]);

    }
    public function store(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = User::where("api_token", $bearer)
            ->first();
        if (!$user) {
            return response()->json([
                'success'=>false,
                'title'=>'Üye Olunuz',
                'msg'=>'Lütfen Randevu Oluşturmak için Üye Olunuz!'
            ]);
        }
        $dieticianid=Dieticians::where('slug',$request->slug)->firstOrFail()->_id;

        if(Appointment::where([
            'consultant_id'=>$user->_id,
            'dietician_id'=>$dieticianid
        ])->where('date','>',Carbon::now())->get()->count()>0){
            return response()->json([
                'success'=>false,
                'title'=>'Randevunuz Var',
                'msg'=>'Zaten Bu Diyetisyene Randevunuz Var'
            ]);
        }
        $appointment=Appointment::create([
            'consultant_id'=>$user->_id,
           'description'=>$request->description ?? '',
           'dietician_id'=>$dieticianid,
           'date'=>$request->date ?? '',
        ]);
        return response()->json([
            'title'=>'Randevu Başarıyla Oluşturuldu!',
            'success'=>true,
            'msg'=>'Randevu Oluşturma Başarılı!',
            'data'=>$appointment]);
    }
}
