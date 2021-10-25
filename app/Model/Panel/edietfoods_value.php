<?php

namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;




class edietfoods_value extends Model
{
    use Authenticabletrait;
    use Notifiable;
    protected $collection = 'edietfoods_value';
    protected $primarykey = "_id";
    protected $guarded = [];


    public function getValueeAttribute()
    {
        return $this->attributes['value'];
    }

}
