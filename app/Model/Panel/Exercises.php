<?php
namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Exercises extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'exercises';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function exercises()
    {
        return $this->hasOne(exercises_file::class, 'exercise_id', "_id")->select("img_url","exercise_id")->where(["isCover"=>1]);
    }
}
