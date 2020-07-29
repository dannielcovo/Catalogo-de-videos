<?php

namespace Tests\Stubs\Models;

use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;

class UploadFilesStub extends Model
{
    use UploadFiles;

    public static $fileFields = ['filme', 'banner', 'trailer'];

    protected function uploadDir()
    {
        return "1";
    }
}
