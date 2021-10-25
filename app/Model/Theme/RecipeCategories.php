<?php
namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class RecipeCategories extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'recipe_categories';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function recipeCategoryPhoto()
    {
        return $this->hasMany(RecipeCategoriesFile::class, 'recipe_category_id', "_id")->where(["isCover"=>1]);
    }
	

}
