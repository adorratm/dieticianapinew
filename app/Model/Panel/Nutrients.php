<?php
namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Nutrients extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'nutrients';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function nutrients()
    {
        return $this->hasOne(nutrients_file::class, 'nutrients_id', "_id")->select("img_url","nutrients_id")->where(["isCover"=>1]);
    }
}
