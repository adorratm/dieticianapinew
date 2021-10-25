<?php

namespace App\Model\Theme;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;


class BlogCategory extends Model
{
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'blog_categories';
    protected $guarded = [];
    protected $primarykey = "_id";


    public function posts()
    {
        return $this->hasMany(Blog::class,'category_id');
    }
}
