<?php

namespace Tests\Feature\Models;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    /**
     * @var $sendData
    */
    private $sendData;

    /**
     * @var video
     */
    private $video;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

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

    public function testList()
    {
        $videos = Video::all();
        $this->assertCount(1, $videos);

        //testar campos de castMember
        $videosKey = array_keys($videos->first()->getAttributes());

        $this->assertEqualsCanonicalizing(
            [
                'id',
                'title',
                'description',
                'year_launched',
                'opened',
                'rating',
                'duration',
                'video_file',
                'thumb_file',
                'trailer_file',
                'banner_file',
                'deleted_at',
                'created_at',
                'updated_at'
            ],
            $videosKey
        );
    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $video = Video::create($this->sendData + [
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id]
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGender($video->id, $gender->id);
    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $video = factory(Video::class)->create();
        $video = Video::create($this->sendData + [
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id]
            ]
        );

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGender($video->id, $gender->id);
    }

    public function testCreate(){
        $this->video->refresh();

        $this->assertEquals('title', $this->sendData['title']);
        $this->assertEquals('description', $this->sendData['description']);
        $this->assertEquals(2010, $this->sendData['year_launched']);
        $this->assertEquals('10', $this->sendData['rating']);
        $this->assertEquals(90, $this->sendData['duration']);
    }

    public function testRollbackCreate()
    {

        $hasError = false;
        // tem que cair no count pro teste ser validado
        // transacao nao aconteceu
        try {
            Video::create([
                'title' => 'title',
                'description' => 'description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[1],
                'duration' => 90,
                'categories_id' => [0,1,2]
            ]);
        } catch (QueryException $e) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testUpdate(){

        $dataUpdated = [
            'title' => 'title updated',
            'description' => 'Good movie',
            'year_launched' => 2011,
            'rating' => Video::RATING_LIST[0],
            'duration' => 101,
        ];
        $this->video->update($dataUpdated);

        foreach ($dataUpdated as $key => $value){
            $this->assertEquals($value, $this->video->$key);
        }
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;
        $hasError = false;
        try {
            $video->update([
                'title' => 'title',
                'description' => 'description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[1],
                'duration' => 90,
                'categories_id' => [0,1,2]
            ]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle
            ]);
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testCreateUuid(){
        $this->assertIsString($this->video->id);
    }

    public function testDelete() {
        $this->video->delete();
        $this->assertSoftDeleted($this->video);
    }

    protected function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('category_video',[
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGender($videoId, $genderId)
    {
        $this->assertDatabaseHas('gender_video',[
            'video_id' => $videoId,
            'gender_id' => $genderId
        ]);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
           'categories_id' => [$categoriesId[0]]
        ]);
        // asserts
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ]);

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);
    }
}
