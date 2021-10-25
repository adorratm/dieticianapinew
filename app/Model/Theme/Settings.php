<?php

namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Settings extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'settings';
    protected $hidden =["_id"];
    protected $guarded = ["_id"];
    protected $primarykey = "_id";

    public function getLogoAttribute()
    {
        return $this->attributes['logo'] ? url(Storage::url($this->attributes['logo'])) : url('public/storage/uploads/settings/preparing/my.jpg');
    }

    public function getFaviconAttribute()
    {
        return $this->attributes['favicon'] ? url(Storage::url($this->attributes['favicon'])) : url('public/storage/uploads/settings/preparing/my.jpg');
    }
}
