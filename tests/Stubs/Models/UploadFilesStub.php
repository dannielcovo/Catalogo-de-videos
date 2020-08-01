<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;

class UploadFilesStub extends Model
{
    use UploadFiles;

    protected $table = 'upload_file_stubs';
    protected $fillable = ['name', 'filme', 'banner', 'trailer'];
    public static $fileFields = ['filme', 'banner', 'trailer'];

    public static function makeTable()
    {
        \Schema::create('upload_file_stubs', function ($table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('filme')->nullable();
            $table->string('banner')->nullable();
            $table->string('trailer')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        \Schema::dropIfExists('upload_file_stubs');
    }

    protected function uploadDir()
    {
        return "1";
    }
}
