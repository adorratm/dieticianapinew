<?php
namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class recipe_categories extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'recipe_categories';
    protected $primarykey = "_id";
    protected $guarded = [];
    protected $appends=['img_url'];
    public function recipe_categories()
    {
        return $this->hasOne(recipe_categories_file::class, 'recipe_category_id', "_id")->select("img_url","recipe_category_id")->where(["isCover"=>1]);
    }

    public function getImgUrlAttribute()
    {
        return url(Storage::url($this->recipe_categories->img_url));
    }
}
