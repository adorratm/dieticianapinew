<?php

namespace App\Model\Theme;

use App\Model\Panel\exercises_file;
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
    protected $hidden = ["_id", "password"];
    protected $primarykey = "_id";

    public function news()
    {
        return $this->belongsTo(News::class);
    }
    public function profilePhoto()
    {
        return $this->hasOne(DieticianFile::class, 'dieticians_id', "_id")->where(["type" => "profile_photo"]);
    }
    public function getProfilePhotoAttribute()
    {
        return isset($this->attributes['profile_photo']) ? url(Storage::url($this->attributes['profile_photo'])) : '';
    }
}
