<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoTestCase extends TestCase
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
}
