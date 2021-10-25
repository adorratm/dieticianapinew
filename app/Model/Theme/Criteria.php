<?php
namespace App\Model\Theme;
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
        return $this->hasMany(CriteriaFile::class, 'criteria_id', "_id")->where(["isCover"=>1]);
    }
	    public function criteriaValues()
    {
        return $this->hasMany(CriteriaValues::class, 'criteria_id', "_id")->where(["isActive"=>1]);
    }
}
