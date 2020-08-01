<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use Illuminate\Database\Events\TransactionCommitted;
use Tests\Exceptions\TestException;
use Tests\Traits\TestUpload;
use App\Models\Category;
use App\Models\Gender;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestValidations;
use App\Models\Video;
use function foo\func;

class VideoControllerUploadsTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestUpload;

    public function testInvalidateVideoFile()
    {
        //Assert
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            12,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        //vincula categoria e genero
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $gender->categories()->sync($category->id);

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData +
            [
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id],
            ] +
            $files
        );

        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }

//        $video = Video::create(
//            $this->sendData + [
//                'video_file' => UploadedFile::fake()->create('video_file.mp4'),
//                'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg')
//            ]
//        );
//        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
//        \Storage::assertExists("{$video->id}/{$video->video_file}");
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        //vincula categoria e genero
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $gender->categories()->sync($category->id);

        $response = $this->json(
            'PUT',
            $this->routeUpdate(),
            $this->sendData +
            [
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id],
            ] +
            $files
        );

        $response->assertStatus(200);

        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testCreateIfRollbackFiles()
    {
        \Storage::fake();

        /* Esse evento é disparado em toda transacao */
        \Event::listen(TransactionCommitted::class, function(){
            throw new TestException();
        });
        $hasError = false;

        try{
            Video::create(
                $this->sendData + [
                    'video_file' => UploadedFile::fake()->create('video_file.mp4'),
                    'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg')
                ]
            );
        } catch(TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg')
        ];
    }
}
