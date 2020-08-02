<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;

class VideoUploadTest extends VideoCrudTest
{
    /**
     * @var \Illuminate\Http\Testing\File $thumbFile
     */
    private $thumbFile;

    /**
     * @var \Illuminate\Http\Testing\File $videoFile
     */
    private $videoFile;

    /**
     * @var \Illuminate\Http\Testing\File $bannerFile
     */
    private $bannerFile;

    /**
     * @var \Illuminate\Http\Testing\File $trailerFile
     */
    private $trailerFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->thumbFile = UploadedFile::fake()->image('thumb.jpj');
        $this->videoFile = UploadedFile::fake()->create('video.mp4');
        $this->bannerFile = UploadedFile::fake()->image('banner.jpj');
        $this->trailerFile = UploadedFile::fake()->create('trailer.mp4');
    }

    public function testCreateWithFiles()
    {
        \Storage::fake();

        $video = Video::create($this->sendData + [
                'thumb_file' => $this->thumbFile,
                'video_file' => $this->videoFile,
                'banner_file' => $this->bannerFile,
                'trailer_file' => $this->trailerFile
            ]);

        //if file exists
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");

        $video = Video::create($this->sendData + [
                'video_file' => $this->videoFile,
            ]);

        // verify if olfFile was deleted
        \Storage::assertExists("{$video->id}/{$this->videoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->thumbFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->bannerFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->trailerFile->hashName()}");
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $video = factory(Video::class)->create();

        $video->update($this->sendData + [
                'thumb_file' => $this->thumbFile,
                'video_file' => $this->videoFile,
                'banner_file' => $this->bannerFile,
                'trailer_file' => $this->trailerFile
            ]);

        //if file exists
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");

        $newVideoFile = UploadedFile::fake()->image('video.mp4');
        $newBannerFile = UploadedFile::fake()->image('banner.jpg');
        $newTrailerFile= UploadedFile::fake()->create('trailer.mp4');
        $video->update($this->sendData + [
                'video_file' => $newVideoFile,
                'banner_file' => $newBannerFile,
                'trailer_file' => $newTrailerFile,
                'thumb_file' => '',
            ]);

        // verify if olfFile was deleted
        \Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newBannerFile->hashName()}");
        \Storage::assertExists("{$video->id}/{$newTrailerFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->videoFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->trailerFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->bannerFile->hashName()}");
        \Storage::assertMissing("{$video->id}/{$this->thumbFile->hashName()}");
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
