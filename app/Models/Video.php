<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid;

    const NO_RATING = 'L';
    const RATING_LIST = [self::NO_RATING, '10', '12', '14', '16', '18'];

    protected $fillable = [
      'title',
      'description',
      'year_launched',
      'opened',
      'rating',
      'duration',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
      'id' => 'string',
      'opened' => 'boolean',
      'year_launched' => 'integer',
      'duration' => 'integer',
    ];

    public $incrementing = false;

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genders()
    {
        return $this->belongsToMany(Gender::class)->withTrashed();
    }
}
