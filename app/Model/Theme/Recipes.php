<?php

namespace App\Model\Theme;

use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class Recipes extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'recipes';
    protected $primarykey = "_id";
    protected $guarded = [];
    public function recipePhoto()
    {
        return $this->hasMany(RecipesFile::class, 'recipes_id', "_id")->where(["isCover" => 1]);
    }
    public function recipeDietician()
    {
        return $this->hasOne(Dieticians::class, '_id', "dietician_id");
    }
}
