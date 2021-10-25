<?php

namespace App\Model\Theme;

use App\Model\Panel\Foods;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FoodLike extends Model
{
    use Authenticabletrait;
    use Notifiable;

    protected $connection="mongodb";
    protected $collection = 'food_like';
    protected $primarykey = "_id";

}
