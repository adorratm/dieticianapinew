<?php

namespace App\Model\Panel;

use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class ediets extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'ediets';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function ediets()
    {
        return $this->hasOne(ediets_file::class, 'ediets_id', "_id")->select("img_url", "ediets_id")->where(["isCover" => 1]);
    }
}
