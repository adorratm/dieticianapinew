<?php

namespace App\Model\Theme;

use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;

class recipes_value extends Model
{
    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'recipes_value';
    protected $primarykey = "_id";
    protected $guarded = [];
}
