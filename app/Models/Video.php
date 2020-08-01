<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles;

    const NO_RATING = 'L';
    const RATING_LIST = [self::NO_RATING, '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
        'thumb_file'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
      'id' => 'string',
      'opened' => 'boolean',
      'year_launched' => 'integer',
      'duration' => 'integer',
    ];

    public $incrementing = false;
    public static $fileFields = ['video_file', 'thumb_file'];

    //sobrescrever store
    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            /** @var Video $obj */
            $obj = static::query()->create($attributes); //filme
            static::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
            //uploads aqui
            \DB::commit();
            return $obj;
        } catch (\Exception $e) {
            if(isset($obj)) {
                $obj->deleteFiles($files);
            }
            \DB::rollBack();
            throw $e;
        }
    }

    // sobrescrever updated
    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);

        // PHP ~/tmp - renomeia
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if($saved) {
                $this->uploadFiles($files);
            }

            \DB::commit();

            if($saved && count($files)) {
                $this->deleteOldfiles();
            }
            return $saved;
        } catch (\Exception $e) {
            $this->deleteFiles($files);
            \DB::rollBack();
            throw $e;
        }
    }

    //sendo estatico precisa receber o Video
    public static function handleRelations(Video $video, array $attributes)
    {
        if(isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if(isset($attributes['genders_id'])) {
            $video->genders()->sync($attributes['genders_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genders()
    {
        return $this->belongsToMany(Gender::class)->withTrashed();
    }

    protected function uploadDir()
    {
        return $this->id;
    }
}