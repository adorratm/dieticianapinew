<?php

namespace App\Model\Theme;


use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection = "mongodb";
    protected $collection = "users";

/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function allergens(){
        return  $this->belongsToMany(Nutrients::class, "allergen_user", "user_id", "user_id");
    }

    public function likedFoods(){
        return $this->belongsToMany(FoodLike::class, 'food_like', "user_id","food_id");
    }

    public function unlikedFoods(){
        return $this->belongsToMany(FoodUnlike::class, 'food_unlike', "user_id","food_id");
    }
    public function getImgUrlAttribute()
    {
        return url(Storage::url($this->attributes['img_url']));
    }

//    public function getAddressAttribute()
//    {
//        return 'ssss';
//    }
}
