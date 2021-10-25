<?php

namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Sliders extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $hidden =["_id"];
    protected $connection="mongodb";


    public function getImgUrlAttribute()
    {
        return $this->attributes['img_url'] ? url(Storage::url($this->attributes['img_url'])) : url('public/storage/uploads/settings/preparing/my.jpg');
    }
}
