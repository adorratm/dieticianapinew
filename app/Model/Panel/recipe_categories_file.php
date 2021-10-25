<?php

namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class recipe_categories_file extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'recipe_categories_file';
    protected $primarykey = "_id";
    protected $casts = [
        'recipe_categories_id' => 'string',
    ];

    public function recipe_categories_file()
    {
        return $this->belongsTo(recipeCategories::class);
    }
}
