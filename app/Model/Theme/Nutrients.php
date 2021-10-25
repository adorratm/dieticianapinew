<?php
namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Model\Theme\NutrientsFile;
class Nutrients extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'nutrients';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function nutrients()
    {
        return $this->hasOne(NutrientsFile::class, 'nutrients_id', "_id")->select("img_url","nutrients_id")->where(["isCover"=>1]);
    }
    public function criteriaValues()
    {
        return $this->hasMany(NutrientsCriteria::class, 'nutrients_id', "_id");
    }

    public function getImgUrlAttribute()
    {
        return 'ssss';
    }
}
