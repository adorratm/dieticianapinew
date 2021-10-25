<?php

namespace App\Http\Controllers\api\Panel\blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Model\Panel\Dieticians;
use App\Model\Panel\Recipes;
use App\Model\Theme\Blog;
use App\Model\Theme\BlogCategory;
use App\Model\Theme\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class indexController extends Controller
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
        $nutrients = Blog::all();
        if ($nutrients) {
            return response()->json(["success" => true, "data" => BlogResource::collection($nutrients)], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Blog Yazıları Çekilirken Bir Hata Oluştu!"], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function save()
    {
        $data["dieticians"] = Dieticians::all();
        $data["categories"] = BlogCategory::all();
        return response()->json(["data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    { $validator = Validator::make($request->all(), [
        'category_id'=>'required',
        'title'=>'required',
        'content'=>'required'
    ]);
        if($request->file('featureimage')){

            $validator = Validator::make($request->all(), [
                'featureimage'=>'image|max:2000'
            ]);
            $data['featureimage']=$request->file('featureimage')->store('public/posts');
        }
        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data = $request->except("_token");
            $data["status"] = "active";
            $data["slug"] = Str::slug($data["title"], "-");
            $data['writable_type']=User::class;
            $data['writable_id']=$this->user->id;
            $blog=Blog::create($data);
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Yazı Başarıyla Eklendi", "data" => new BlogResource($blog), "title" => $data["title"]], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function fileStore(Request $request, $id)
    {
        if (!empty($request->file())) {
            $status = 1;
            foreach ($request->file("file") as $key => $file):

                $strFileName = Str::slug($request->title);
                $extension = $file->extension();
                $fileNameWithExtension = $strFileName . "-" . rand(0, 99999999999) . "-" . time() . "." . $extension;
                $path = $file->storeAs("uploads/recipes/{$strFileName}/", $fileNameWithExtension, "public");
                $count = DB::table("recipes_file")->where("recipes_id", $id)->count();
                $data["recipes_id"] = $id;
                $data["img_url"] = $path;
                $data["isActive"] = 1;
                $data["rank"] = $count + 1;
                $data["isCover"] = 0;
                $add = DB::table("recipes_file")->insert($data);
                if (!$path || !$add) {
                    $status = 0;
                }
            endforeach;
            if ($status == 0) {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Resimler Eklenirken Bir Hata Oluştu"], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Resimler Başarıyla Eklendi"], 200, [], JSON_UNESCAPED_UNICODE);
            }

        }
    }

    public function edit($id)
    {
        $data['categories'] = BlogCategory::all();
        $data['post']=new BlogResource(Blog::findOrFail($id));
        if ($data['post']) {


            return response(["success" => true, "data" => $data], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Böyle Bir Veri Bulunamadı!"], 200, [], JSON_UNESCAPED_UNICODE);

        }

    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'=>'required',
            'title'=>'required',
            'content'=>'required'
        ]);
        if($request->featureimage)
            $validator = Validator::make($request->all(), [
                'featureimage'=>'image|max:2000'
            ]);
        if ($validator->fails()) {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Girdiğiniz Bilgileri Kontrol Edin", "error" => $validator->messages()], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $data = $request->except("_token");
            $data["status"] = "active";
            $data["slug"] = Str::slug($data["title"], "-");
            $data['writable_type']=User::class;
            $data['writable_id']=$this->user->id;
            $data = Blog::findOrFail($id)->update($data);
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarlarınız Başarıyla Güncellendi", "data" => new BlogResource(Blog::findOrFail($id))], 200, [], JSON_UNESCAPED_UNICODE);
        }

    }

    public function destroy($id)
    {
        $blog=Blog::where('_id',$id)->get();
        if ($blog->count()>0) {
            $title=$blog->first();
            $blog->delete();
            return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Ayarınız Başarıyla Silindi", "data" => new BlogResource($title)], 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Ayarınız Silinirken Bir Hata İle Karşılaşıldı."], 200, [], JSON_UNESCAPED_UNICODE);

        }
    }

    public function getAll(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Recipes;
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
        $response = $response->with("recipes");
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->recipes as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }

        }*/

        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

    public function getBySearch(Request $request)
    {
        if (empty($request->search) || $request->search == "null") {
            return Redirect::to(route("panel.recipes.getAll", "table={$request->table}&per_page={$request->per_page}"));
        }
        $request->search_columns = explode(",", $request->search_columns);
        if (!is_array($request->search_columns)) {
            $request->search_columns = (array)$request->search_columns;
        }
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Recipes;
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
        foreach ($request->search_columns as $k=>$column) {
            $response=$response->where(function($query) use ($column,$request){
                $query->orwhere($column,"like","%". Helper::strto("lower", $request->search)."%")
                    ->orWhere($column,"like","%".Helper::strto("lower|ucfirst", $request->search)."%")
                    ->orWhere($column,"like","%".Helper::strto("lower|ucwords", $request->search)."%")
                    ->orWhere($column,"like","%".Helper::strto("lower|upper", $request->search)."%")
                    ->orWhere($column,"like","%".Helper::strto("lower|capitalizefirst", $request->search)."%");
            });
        }
        $response = $response->with("recipes");
        $response = $response->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->recipes as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }

    public function getByOrder(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response = new Recipes;
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
        $response = $response->with("recipes");
        $response = $response->orderBy($request->sortBy, $request->direction)->paginate($per_page);
        /*foreach ($response as $key => $item) {
            $response[$key]["img_url"] = "uploads/settings/preparing/my.jpg";
            foreach ($item->recipes as $v) {
                $response[$key]["img_url"] = $v->img_url;
            }
        }*/
        return response()->json(["data" => $response,"empty_url" => "uploads/settings/preparing/my.jpg"]);
    }
}
