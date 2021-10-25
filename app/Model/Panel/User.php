<?php

namespace App\Model\Panel;


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
    protected $collection = 'users';
    protected $guarded = [];
    protected $primarykey = "_id";


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = [
//        'name', 'email', 'password','tc','phone','address','birthDate','chest','city','district','gender','hip','lowerArm'
//    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',"_id"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getImgUrlAttribute()
    {
        return url(Storage::url($this->attributes['img_url']));
    }

//    public function getAddressAttribute()
//    {
//        return 'ssss';
//    }
}
