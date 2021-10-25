<?php

namespace App\Http\Controllers\Api\Panel\Datatables;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Panel\Nutrients;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use App\Helper\tools_helper;

class indexController extends Controller
{
    public function getAll(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response =DB::table($request->table);
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
        return response()->json(["data" => $response]);
    }

    public function getBySearch(Request $request)
    {
        if (empty($request->search) || $request->search == "null") {
            return Redirect::to(route("panel.datatables.index", "table={$request->table}&per_page={$request->per_page}&where_column={$request->where_column}&where_value={$request->where_value}"));
        }
        $request->search_columns = explode(",", $request->search_columns);
        if (!is_array($request->search_columns)) {
            $request->search_columns = (array)$request->search_columns;
        }
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response =DB::table($request->table);
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
            $response=$response->orWhere(function($query) use ($column,$request){
				$query->orWhere($column,"like","%". strtolower($request->search)."%")
						->orWhere($column,"like","%".strtolower(ucfirst($request->search))."%")
						->orWhere($column,"like","%".strtolower(ucwords($request->search))."%")
						->orWhere($column,"like","%".strtolower(strtoupper($request->search))."%")
						->orWhere($column,"like","%".strtolower(lcfirst($request->search))."%")
						->orWhere($column,"like","%".strtoupper(str_replace('i','İ',$request->search))."%");
            });
        }
        $response = $response->paginate($per_page);

        return response()->json(["data" => $response]);
    }

    public function getByOrder(Request $request)
    {
        $per_page = empty($request->per_page) ? 10 : (int)$request->per_page;
        $response =DB::table($request->table);
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

        return response()->json(["data" => $response]);
    }
    public function isActiveSetter(Request $request)
    {
        $data = DB::table($request->table)->where("_id", $request->id)->first();
        if (!empty($data)) {
            $isActive = (empty($data["isActive"]) ? 1 : ($data["isActive"] == 1 ? 0 : 1));
            $update = DB::table($request->table)->where("_id", $request->id)->update(["isActive" => $isActive]);
            if ($update) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Durum Başarıyla Değiştirildi."]);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Durum Değiştirilemedi."]);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Durum Değiştirilemedi."]);
        }
    }

    public function isCoverSetter(Request $request)
    {
        $data = DB::table($request->table)->where("_id", $request->id)->first();
        if (!empty($data)) {
            $foreign_key = $request->foreign_column;
            $data = (array)$data;
            $disable = DB::table($request->table)->where("_id", "!=", $request->id)->where($foreign_key, $data[$foreign_key])->update(["isCover" => 0]);
            $enable = DB::table($request->table)->where("_id", $request->id)->update(["isCover" => 1]);
            if ($enable) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Durum Başarıyla Değiştirildi."]);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Durum Değiştirilemedi."]);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Durum Değiştirilemedi."]);
        }
    }
    public function deleteFile(Request $request)
    {
        $data = DB::table($request->table)->where("_id", $request->id)->first();
        if (!empty($data)) {
            $destroy = DB::table($request->table)->where("_id", $request->id)->delete();
            if ($destroy) {
                return response()->json(["success" => true, "title" => "Başarılı!", "msg" => "Resim Başarıyla Silindi."]);
            } else {
                return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Durum Değiştirilemedi."]);
            }
        } else {
            return response()->json(["success" => false, "title" => "Başarısız!", "msg" => "Durum Değiştirilemedi."]);
        }
    }
}
