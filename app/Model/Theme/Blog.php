<?php

namespace App\Model\Theme;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Blog extends Model
{
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'blogs';
    protected $guarded = [];
    protected $primarykey = "_id";
    protected $appends="writable";

    public function writable()
    {
        return $this->morphTo();
    }


    public function category()
    {
        return $this->belongsTo(BlogCategory::class,'category_id');
    }
}
