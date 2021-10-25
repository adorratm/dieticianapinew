<?php
namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;
class Diseases extends Model implements Authenticatable
{

    use Authenticabletrait;
    use Notifiable;
	    
	protected $connection="mongodb";
    protected $collection = 'diseases';
    protected $primarykey = "_id";
    protected $guarded = [];

}
