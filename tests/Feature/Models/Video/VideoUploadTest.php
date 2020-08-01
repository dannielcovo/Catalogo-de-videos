<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;

class VideoUploadTest extends VideoCrudTest
{
    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        $thumbFile = UploadedFile::fake()->image('thumb.jpj');
        $videoFile = UploadedFile::fake()->create('video.mp4');

        $video->update($this->sendData + [
                'thumb_file' => $thumbFile,
                'video_file' => $videoFile,
            ]);

        //if file exists
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");

        $newVideoFile = UploadedFile::fake()->image('video.mp4');
        $video->update($this->sendData + [
                'video_file' => $newVideoFile,
            ]);

        // verify if olfFile was deleted
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$videoFile->hashName()}");
    }

    public function testUpdateIfRollbackFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();
        \Event::listen(TransactionCommitted::class, function(){
            throw new TestException();
        });
        $hasError = false;
        try{
            $video->update(
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
}
