<?php

namespace App\Http\Controllers\Api\Panel\Invoices;

use App\Http\Controllers\Controller;
use App\Model\Panel\Invoice;
use App\Model\Theme\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class indexController extends Controller
{
    public $status;

    public function __construct(Request $request)
    {
        $bearer = $request->header("Authorization");
        $bearer = str_replace("Bearer ", "", $bearer);
        $user = User::where("api_token", $bearer)
            ->first();
        if ($user) {
            $this->status = $user->status;
        }
    }

    public function index(Request $request)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $invoice = DB::table("Invoice")->get();
            if ($invoice) {
                return response($invoice, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response("Listelenecek Veri Bulunmamaktadır.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|min:3",
            "img_url" => "required",
            "isActive" => "required|numeric",
            "description" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $invoice = new Invoice;
            $invoice->title = $request->title;
            $invoice->img_url = $request->img_url;
            $invoice->isActive = (integer)$request->isActive;
            $invoice->description = $request->description;
        }
        $invoice->save();
        return response($invoice, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function edit($id)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            $invoice = Invoice::where("_id", $id)
                ->first();
            if ($invoice) {
                return response($invoice, 200);
            } else {
                return response("Böyle Bir Kullanıcı Yoktur.", 200, [], JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function update(Request $request)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            if ($request->id) {
                $user = User::where("_id", $request->id)->first();
                if ($user) {
                    $data = $request->except("_token");
                    if ($data["id"]) {
                        unset($data["id"]);
                    }
                    $invoice = Invoice::where("_id", $request->id)
                        ->update($data);
                    if ($invoice) {
                        return response()->json("Güncelleme İşlemi Başarılı", 200, [], JSON_UNESCAPED_UNICODE);
                    } else {
                        return response()->json("Güncelleme İşlemi Yapılamadı", 200, [], JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    return response()->json("Böyle Bir Kullanıcı Bulunmamaktadır", 200, [], JSON_UNESCAPED_UNICODE);
                }
            }
        }
    }

    public function destroy($id)
    {
        if ($this->status != "admin") {
            return response()->json("Bu İşlem İçin Yetkili Değilsiniz", 200, [], JSON_UNESCAPED_UNICODE);
        } else {
            Invoice::where("_id", $id)
                ->delete();
            return response()->json("Silme İşlemi Başarılı", 200, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
