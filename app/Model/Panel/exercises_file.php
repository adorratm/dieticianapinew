<?php

namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class exercises_file extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'exercises_file';
    protected $primarykey = "_id";
    protected $casts = [
        'exercise_id' => 'string',
    ];

    public function exercises_file()
    {
        return $this->belongsTo(exercises::class);
    }

    public function getImgUrlAttribute()
    {

        return "https://api.klinikdiyetisyen.com/public".Storage::url($this->attributes['img_url']);
        return url();
    }
}
