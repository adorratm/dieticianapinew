<?php

namespace App\Model\Panel;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Dieticians extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection = "mongodb";
    protected $collection = 'dieticians';
    protected $guarded = [];
    protected $primarykey = "_id";
    public function getProfilePhotoAttribute()
    {
        return isset($this->attributes['profile_photo']) ? url(Storage::url($this->attributes['profile_photo'])) : '';
    }
}
