<?php

namespace App\Model\Theme;

use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;

class recipes_criteria_value extends Model
{
    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'recipes_criteria_value';
    protected $primarykey = "_id";
    protected $guarded = [];
    protected $with=['nutrient'];

    public function nutrient()
    {
        return $this->belongsTo(Nutrients::class,'recipe_criteria_id');
    }
}
