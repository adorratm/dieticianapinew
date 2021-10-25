<?php

namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class CriteriaValues extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'criteria_value';
    protected $primarykey = "_id";
    protected $casts = [
        'criteria_id' => 'string',
    ];

    public function criteria_value()
    {
        return $this->belongsTo(Criteria::class);
    }
}
