<?php

namespace App\Model\Theme;

use App\Model\Panel\Foods;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FoodUnlike extends Model
{
    use Authenticabletrait;
    use Notifiable;

    protected $connection="mongodb";
    protected $collection = 'food_unlike';
    protected $primarykey = "_id";

}
