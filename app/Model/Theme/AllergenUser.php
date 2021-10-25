<?php

namespace App\Model\Theme;

use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AllergenUser extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'allergen_user';
    protected $primarykey = "_id";
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', "_id");
    }

    public function nutrient()
    {
        return $this->hasOne(\App\Model\Theme\Nutrients::class, 'nutrient_id', "_id");
    }
}
