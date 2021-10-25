<?php

namespace App\Http\Controllers\Api\Dietician\Users;

use App\Http\Controllers\Controller;
use App\Jobs\Panel\MailJobs;
use App\Model\Panel\User;
use App\Model\Dietician\Dieticians;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Model\Theme\Settings;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Model\Panel\Diseases;


class indexController extends Controller
{
    public $status = "";

    public function __construct(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = Dieticians::where("api_token", $bearer)
            ->first();
        if ($user) {
            $this->status = $user->status;
        }
    }

    public function userBirthCalc(Request $request)
    {
        $dateOfBirth = $request->dates;
        $yearsOrmonth = Carbon::parse($dateOfBirth)->age;
        if ($yearsOrmonth > 0)
        {
            $years = Carbon::parse($dateOfBirth)->age;

        }else
        {
            $years = Carbon::parse($dateOfBirth)->diff(Carbon::now())->format('%m months');
        }
        return response($years,200,[],JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    {
        if(User::where('email',$request->email)->get()->count()>0)
            return response()->json([
                'success'=>false,
                'title'=>'Email Kullanılıyor',
                'msg'=>'Farklı bir email deneyin!'
            ], 403, [], JSON_UNESCAPED_UNICODE);
        $data = $request->except("_token");
        $data["password"] = Hash::make($data["password"]);
        DB::table("users")
            ->insert($data);
        $users=User::all()->last();
      //dd($users);
       return response()->json([
           'data'=>$users,
           'success'=>true,
           'title'=>'Başarılı',
           'msg'=>'Başarıyla Eklendi'
       ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function edit($id)
    {

        $users = DB::table("users")
            ->where("_id", $id)
            ->first();
		$user_diseases = DB::table("users_diseases")
		->where("user_id",(string)$users["_id"])->first();
		$user_diseases = (object)$user_diseases;
        if ($users) {
            return response()->json(["data" => $users,"selectedDiseases" => (!empty($user_diseases->userdiseases) ? explode(",",$user_diseases->userdiseases) : []),"selectedAllergenFoods" => (!empty($user_diseases->allergenfoods) ? explode(",",$user_diseases->allergenfoods) : []),"selectedUnlikedFoods" => (!empty($user_diseases->unlovedfoods) ? explode(",",$user_diseases->unlovedfoods) : [])], 200);
        } else {
            return response("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getUser(Request $request)
    {

         $users = User::where('tckn', $request->tc)//where("tc", $request->tc)
            //->where("phone", $request->phone)
            ->first();
		$user_diseases = DB::table("users_diseases")
		->where("user_id",$users["_id"])->get();


       // print_r($users);
        if ($users) {
            return response()->json(["data" => $users,"user_diseases" => $user_diseases], 200);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Bilgilere Ait Bir Kullanıcı Bulunmamaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function userMail(Request $request)
    {
        $users = DB::table("users")
            ->where("tc", $request->tc)
            ->where("phone", $request->phone)
            ->first();

        $dietician = DB::table("dieticians")
            ->where("_id", $request->dietician_id)
            ->first();
        if (!empty($users) && !empty($dietician)) {
            $key = rand(100000, 999999);
            $update = Db::table("users")->where("tc", $request->tc)
                ->where("phone", $request->phone)->update(["dietician_check" => $key]);
            $settings = Settings::where("isActive", 1)->first();


            $data = [
                'name' => $users["name"],
                'mail' => $users["email"],
                "headers" => $request->header("referer"),
                "host" => $request->header("host"),
                "dietician" => $dietician,
                "key" => $key,
                "logo" => asset("storage/" . $settings->logo)
            ];
            $mail = MailJobs::dispatch($data, $users);

            if ($mail) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışan Başvurunuz Başarıyla Danışana Bildirildi."], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "İsteğiniz İletilemedi."], 200, [], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Bilgilere Ait Bir Kullanıcı Bulunmamaktadır.", "data" => $users], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function userDiseases(Request $request)
    {

        $dietician = DB::table("dieticians")
            ->where("_id", $request->dietician_id)
            ->first();


		$datas[] = $request->selectedDiseases;
		foreach($datas as $data)
		{

			$data = array(
				"userdiseases" => $data,
				"ditican_id" => $dietician,
				"user_id" =>  $request->id,
			);
		}



		$diseasesDelete = DB::table("users_diseases")
		->where("user_id",$request->id)->delete();

        $dataInsert = DB::table("users_diseases")
            ->insert($data);
        if ($dataInsert) {
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışan Hastalık Bilgileri Başarıyla Güncellendi.","data" => $data], 200);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Bilgilere Ait Bir Kullanıcı Bulunmamaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
        }

    }



	public function userAllergenFoods(Request $request)
    {

		$datas[] = $request->selectedAllergenFoods;
		foreach($datas as $data)
		{
			$data = array(
				"allergenfoods" => $data,
			);
		}


        $dataInsert = DB::table("users_diseases")->where("user_id",$request->id)
            ->update($data);
		if ($dataInsert) {
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışan Alerjen Besin Bilgileri Başarıyla Güncellendi.","data" => $data], 200);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Bilgilere Ait Bir Kullanıcı Bulunmamaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
        }

    }

	public function userUnlovedFoods(Request $request)
    {

		$datas[] = $request->selectedUnlikedFoods;
		foreach($datas as $data)
		{
			$data = array(
				"unlovedfoods" => $data,
			);
		}


        $dataInsert = DB::table("users_diseases")->where("user_id",$request->id)
            ->update($data);
		if ($dataInsert) {
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışan Sevilmeyen Besin Bilgileri Başarıyla Güncellendi.","data" => $data], 200);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Bilgilere Ait Bir Kullanıcı Bulunmamaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
        }

    }

    public function getAllDiseases()
    {
        $data["diseases"] = DB::table("diseases")->where("isActive", 1)->get();
		$edietFoods =  DB::table("edietfoods")->where("isActive",1)->get();
		$data["unlikedFoods"] = $edietFoods;
		$data["allergenFoods"] = $edietFoods;
        //dd($data["diseases"]);
        return response()->json(["data" => $data], 200);
    }

    public function dietMeal(Request $request)
    {
        $users = DB::table("users")
            ->where("tc", $request->tc)
            ->where("phone", $request->phone)
            ->first();

        $dietician = DB::table("dieticians")
            ->where("_id", $request->dietician_id)
            ->first();

        $dataInsert = [
            "ditican_id" => $dietician,
            "user_id" =>  $users,
            "morning"=> $request->morning,
            "noon" => $request->noon,
            "vesper" =>$request->vesper,
            "birding" => $request->birding, //Çaktırma kuşluk vakti bu :D
            "afternoon" => $request->afernoon,
            "night" => $request->night
        ];
        if ($dataInsert) {
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışan Hastalık Bilgileri Başarıyla Güncellendi.","data" => $dataInsert], 200);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu Bilgilere Ait Bir Kullanıcı Bulunmamaktadır."], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
/*
    public function update($id, Request $request)
    {
        if ($request->id) {
            $user = User::where("_id", $id)->first();
            if ($user) {
                $data = $request->except("_token");
                $update = User::where("_id", $id)
                    ->update($data);
                if ($update) {
                    return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışman Ayarları Başarıyla Güncellendi"], 200, [], JSON_UNESCAPED_UNICODE);
                } else {
                    return response()->json("Güncelleme İşlemi Yapılamadı", 200, [], JSON_UNESCAPED_UNICODE);
                }
            } else {
                return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }
	*/
	public function update($id,Request $request)
    {
        if ($this->status != "dietician") {
            return response()->json(["success" => false,"title" => "Başarısız!", "msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200,[], JSON_UNESCAPED_UNICODE);
        } else {
            if ($request->id) {
                $user = User::where("_id", $id)->first();
                if ($user) {
                    $data = $request->except("_token");
                    if(!empty($data["password"])){
                        $validator =  Validator::make($request->all(),[
                            'password' => 'required|confirmed|min:6'
                        ]);
                        if($validator->fails()){
                            return response()->json($validator->messages(),200,[], JSON_UNESCAPED_UNICODE);
                        }
                        else {
                            unset($data["password_confirmation"]);
                            $data["password"] = Hash::make($data["password"]);
                        }
                    }

                    if ($request->file()) {
                        foreach ($request->file() as $key => $file):
                            $photo = $request->file($key);
                            $path = $request->$key->path();
                            $extension = $request->$key->extension();
                            $fileNameWithExtension = $photo->getClientOriginalName();
                            $fileNameWithExtension = Str::slug($request->name) . "-" . time() . "." . $extension;
                            $path = $request->$key->storeAs("uploads/users/", $fileNameWithExtension, "public");
                            if (!empty($path)) {
                                $data[$key] = $path;
                            }

                        endforeach;

                    }
                    $update = User::where("_id", $id)
                        ->update($data);
                    if ($update) {
                        return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Danışan Bilgileri Başarıyla Güncellendi."], 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Danışan Bilgileri Güncelenirken Hata Oluştu, Lütfen Daha Sonra Tekrar Deneyin."], 200,[], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Güncellemeye Çalıştığınız Danışana Ait Bilgiler Bulunamadı. Lütfen Daha Sonra Tekrar Deneyin."], 200,[], JSON_UNESCAPED_UNICODE);
                }

            }
        }

    }



    public function destroy($id)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            User::where("_id", $id)
                ->delete();
            return response()->json("Silme İşlemi Başarılı", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

}
