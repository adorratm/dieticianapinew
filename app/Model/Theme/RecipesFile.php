<?php

namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class RecipesFile extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'recipes_file';
    protected $primarykey = "_id";
    protected $casts = [
        'recipes_id' => 'string',
    ];

    public function recipes_file()
    {
        return $this->belongsTo(Recipes::class);
    }
}
