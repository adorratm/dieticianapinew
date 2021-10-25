<?php

namespace App\Http\Controllers\Api\Panel\Users;

use App\Http\Controllers\Controller;
use App\Model\Panel\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class indexController extends Controller
{
    public $status = "";
    public function __construct(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = User::where("api_token", $bearer)
            ->first();
        if($user){
            $this->status = $user->status;
        }
    }
    public function index(Request $request)
    {
        if ($this->status != "admin") {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200,[], JSON_UNESCAPED_UNICODE);
        } else {
            $users = DB::table("users")
                ->get();
            if($users){
                return response()->json(["data"=>$users], 200);

            }else{
                return response(["success" => false, "title" => "Başarısız!", "msg" => "Listelenecek Veri Bulunmamaktadır."],200,[],JSON_UNESCAPED_UNICODE);
            }
        }
    }
    public function store(Request $request)
    {
        $data = $request->except("_token");
        $data["password"] = Hash::make($data["password"]);
        $users = DB::table("users")
            ->insert($data);
        return response($users, 200,[], JSON_UNESCAPED_UNICODE);
    }
    public function edit($id)
    {
        if ($this->status != "admin") {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200,[], JSON_UNESCAPED_UNICODE);
        } else {
            $users = DB::table("users")
                ->where("_id",$id)
                ->first();
            if($users){
                return response()->json(["data"=>$users], 200);

            }else{
                return response(["success" => false, "title" => "Başarısız!", "msg" => "Güncellemek İstediğiniz Kullanıcı Bulunamadı."],200,[], JSON_UNESCAPED_UNICODE);
            }
        }
    }
    public function update($id,Request $request)
    {
        if ($this->status != "admin") {
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
                        return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Kullanıcı Bilgileri Başarıyla Güncellendi."], 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Kullanıcı Bilgileri Güncelenirken Hata Oluştu, Lütfen Daha Sonra Tekrar Deneyin."], 200,[], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Güncellemeye Çalıştığınız Kullanıcıya Ait Bilgiler Bulunamadı. Lütfen Daha Sonra Tekrar Deneyin."], 200,[], JSON_UNESCAPED_UNICODE);
                }

            }
        }

    }
    public function destroy($id)
    {
        if ($this->status != "admin") {
            return response()->json(["success" => false,"title" => "Başarısız!","msg" => "Bu İşlem İçin Yetkili Değilsiniz."], 200,[], JSON_UNESCAPED_UNICODE);
        } else {
            User::where("_id", $id)
                ->delete();
            return response()->json(["success"=> true,"title"=> "Başarılı!","msg"=>"Kullanıcı Kaydı Başarıyla Silindi."], 200,[], JSON_UNESCAPED_UNICODE);
        }
    }
	
	public function getAll(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Users;
        if (!empty($request->where_column)) {
            $request->where_column = explode(",", $request->where_column);
            $request->where_value = explode(",", $request->where_value);
            if (!is_array($request->where_column) || !is_array($request->where_value)) {
                $request->where_column = (array)$request->where_column;
                $request->where_value = (array)$request->where_value;
            }
            foreach ($request->where_column as $k => $v) {
                $response = $response->where($v, $request->where_value[$k]);
            }
        }
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->nutrients as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }

        }*/

        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }
	
	public function getBySearch(Request $request)
    {
        if (empty($request->search) || $request->search == "null") {
            return Redirect::to(route("panel.users.getAll", "table={$request->table}&per_page={$request->per_page}"));
        }
        $request->search_columns = explode(",", $request->search_columns);
        if (!is_array($request->search_columns)) {
            $request->search_columns = (array)$request->search_columns;
        }
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Users;
        if (!empty($request->where_column)) {
            $request->where_column = explode(",", $request->where_column);
            $request->where_value = explode(",", $request->where_value);
            if (!is_array($request->where_column) || !is_array($request->where_value)) {
                $request->where_column = (array)$request->where_column;
                $request->where_value = (array)$request->where_value;
            }
            foreach ($request->where_column as $k => $v) {
                $response = $response->where($v, $request->where_value[$k]);
            }
        }
        foreach ($request->search_columns as $column) {
			$response=$response->orwhere($column,"like","%". Str::strto("lower", $request->search)."%")
						->orWhere($column,"like","%".Str::strto("lower|ucfirst", $request->search)."%")
						->orWhere($column,"like","%".Str::strto("lower|ucwords", $request->search)."%")
						->orWhere($column,"like","%".Str::strto("lower|upper", $request->search)."%")
						->orWhere($column,"like","%".Str::strto("lower|capitalizefirst", $request->search)."%");
        }
        $response = $response->paginate($per_page);
		/*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->nutrients as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }
	
	public function getByOrder(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Users;
        if (!empty($request->where_column)) {
            $request->where_column = explode(",", $request->where_column);
            $request->where_value = explode(",", $request->where_value);
            if (!is_array($request->where_column) || !is_array($request->where_value)) {
                $request->where_column = (array)$request->where_column;
                $request->where_value = (array)$request->where_value;
            }
            foreach ($request->where_column as $k => $v) {
                $response = $response->where($v, $request->where_value[$k]);
            }
        }
        $response = $response->orderBy($request->sortBy, $request->direction)->paginate($per_page);
		
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->nutrients as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }
}
