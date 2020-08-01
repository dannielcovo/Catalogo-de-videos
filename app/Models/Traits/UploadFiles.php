<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

trait UploadFiles
{
    /* Quem usar a trait determina onde fica o arquivo*/
    protected abstract function uploadDir();

    public $oldFiles = [];

    public static function bootUploadFiles()
    {
        //antes de processar o sql no banco de dados
        static::updating(function(Model $model){
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::$fileFields);
            $filesFiltered = Arr::where($filesUpdated, function ($fileField) use($model) {
                return $model->getOriginal($fileField); // retornar os valores antigos que nao forem nulos
            });

            // guarda cada valor de cada arquivo que foi atualizado
            $model->oldFiles = array_map(function ($fileField) use ($model){
                return $model->getOriginal($fileField); // get value of attribute $filesFiltered and return value not null
            }, $filesFiltered);

        });
    }

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

    public function deleteOldfiles()
    {
        $this->deleteFiles($this->oldFiles);
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

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
