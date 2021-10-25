<?php

namespace App\Model\Theme;

use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;

class Appointment extends Model
{

    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'appointments';
    protected $guarded = [];
    protected $primarykey = "_id";
    protected $dates=['date'];

    public function dietician()
    {
        $this->belongsTo(Dieticians::class,'dietician_id');
    }
}
