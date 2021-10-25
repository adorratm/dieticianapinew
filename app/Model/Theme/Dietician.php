<?php

namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Dietician extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'dietician';
    protected $guarded = [];
    protected $hidden =["_id","password"];
    protected $primarykey = "_id";
    public function news()
    {
        return $this->belongsTo(News::class);
    }
    public function profile_photo()
    {
        return $this->hasMany(DieticianFile::class, 'dieticians_id', "_id")->where("type","profile_photo");
    }

}
