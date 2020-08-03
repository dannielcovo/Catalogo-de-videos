<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Foundation\Testing\TestResponse;
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

    public function testInvalidateVideoField()
    {
        //Assert
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidateThumbField()
    {
        //Assert
        $this->assertInvalidationFile(
            'Thumb_file',
            'jpg',
            Video::THUMB_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidateBannerField()
    {
        //Assert
        $this->assertInvalidationFile(
            'banner_file',
            'jpg',
            Video::BANNER_FILE_MAX_SIZE,
            'image'
        );
    }

    public function testInvalidateTrailerField()
    {
        //Assert
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();

        $response = $this->json(
            'POST', $this->routeStore(), $this->sendData + $files
        );

        $response->assertStatus(201);
        $this->assertFilesOnPersist($response, $files);
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        $response = $this->json(
            'PUT', $this->routeUpdate(), $this->sendData + $files
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'video_file' => UploadedFile::fake()->create('video_file2.mp4'),
            'thumb_file' => UploadedFile::fake()->image('thumb_file2.jpg')
        ];

        $response = $this->json(
            'PUT', $this->routeUpdate(), $this->sendData + $newFiles
        );

        $response->assertStatus(200);
        $this->assertFilesOnPersist(
            $response,
            \Arr::except($files, ['thumb_file', 'video_file']) + $newFiles // tirou os artuivos antigos e concatenando com files novos
        );

        $id = $response->json('id');
        $video = Video::find($id);
        \Storage::assertMissing($video->relativeFilePath($files['thumb_file']->hashName()));
        \Storage::assertMissing($video->relativeFilePath($files['video_file']->hashName()));
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

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id');
        $video = Video::find($id);
        $this->assertFilesExistsInStorage($video, $files);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg')
        ];
    }
}
