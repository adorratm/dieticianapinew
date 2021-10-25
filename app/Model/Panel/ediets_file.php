<?php

namespace App\Model\Panel;

use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class ediets_file extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection = "mongodb";
    protected $collection = 'ediets_file';
    protected $primarykey = "_id";
    protected $casts = [
        'ediets_id' => 'string',
    ];

    public function ediets_file()
    {
        return $this->belongsTo(ediets::class);
    }
}
