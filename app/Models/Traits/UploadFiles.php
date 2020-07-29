<?php

namespace App\Models\Traits;

use Illuminate\Http\UploadedFile;

trait UploadFiles
{
    /* Quem usar a trait determina onde fica o arquivo*/
    protected abstract function uploadDir();

    /**
     * @param UploadedFile[] $files
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    public function uploadFile(UploadedFile $file)
    {
        // determinar qual caminho interno do video
        $file->store($this->uploadDir());
    }

    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * @param string|UploadedFile $file
     */
    public function deleteFile($file)
    {
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;
        //complete path
        \Storage::delete("{$this->uploadDir()}/{$fileName}");
    }

    public static function extractFiles(array &$attributes = [])
    {
        //UploadedFile
        $files = [];
        foreach (self::$fileFields as $file) {
            //Somente se for uploadedFile
            if(isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile) {
                $files[] = $attributes[$file];
                $attributes[$file] = $attributes[$file]->hashName(); //altera par nome do arquivo apenas
            }
        }
        return $files;
    }
}
