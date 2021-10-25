<?php

namespace App\Model\Panel;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\Model ;
use Illuminate\Auth\Authenticatable as Authenticabletrait;
use Illuminate\Contracts\Auth\Authenticatable;

class exercise_categories_file extends Model implements Authenticatable
{
    use Authenticabletrait;
    use Notifiable;
    protected $connection="mongodb";
    protected $collection = 'exercise_categories_file';
    protected $primarykey = "_id";
    protected $casts = [
        'exercise_categories_id' => 'string',
    ];

    public function exercise_categories_file()
    {
        return $this->belongsTo(exerciseCategories::class);
    }
}
