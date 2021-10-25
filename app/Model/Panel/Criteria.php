<?php
namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Criteria extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'criteria';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function criteria()
    {
        return $this->hasOne(Criteria_file::class, 'criteria_id', "_id")->select("img_url","criteria_id")->where(["isCover"=>1]);
    }
}
