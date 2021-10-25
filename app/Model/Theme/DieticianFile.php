<?php

namespace App\Model\Theme;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class DieticianFile extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection = "mongodb";
    protected $collection = 'dieticians_file';
    protected $primarykey = "_id";
    protected $casts = [
        'dieticians_id' => 'string',
    ];

    public function dietician()
    {
        return $this->belongsTo(Dieticians::class);
    }

    public function getProfilePhotoAttribute()
    {
        return url(Storage::url($this->attributes['profile_photo']));
    }
}
