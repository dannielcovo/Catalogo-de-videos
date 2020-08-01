<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Video;

class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;
    /**
     * @var array $sendData
     */
    protected $sendData;

    /**
     * @var $video
     */
    protected $video;

    protected function setUp(): void
    {
        parent::setUp();

        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);

        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[1],
            'duration' => 90,
        ];
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
