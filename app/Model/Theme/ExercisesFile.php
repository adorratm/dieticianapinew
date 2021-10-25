<?php

namespace App\Model\Theme;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class ExercisesFile extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'exercises_file';
    protected $primarykey = "_id";
    protected $casts = [
        'exercise_id' => 'string',
    ];

    public function exercises_file()
    {
        return $this->belongsTo(exercises::class);
    }
}
