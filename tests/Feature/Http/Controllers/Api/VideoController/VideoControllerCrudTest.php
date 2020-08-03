<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Video;
use Illuminate\Support\Arr;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use DatabaseMigrations;

    public function testIndex()
    {
        $response  = $this->get(route ('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->first()->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationDataRequired(){

        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genders_id' => ''
        ];

        $this->assertInvalidationInUpdateAction($data, 'required');
        $this->assertInvalidationInStoreAction($data, 'required');
    }

    public function testInvalidationCategoriesIdField()
    {
        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInUpdateAction($data, 'array');
        $this->assertInvalidationInStoreAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];

        $this->assertInvalidationInUpdateAction($data, 'exists');
        $this->assertInvalidationInStoreAction($data, 'exists');

        // test softDeletes
        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInUpdateAction($data, 'exists');
        $this->assertInvalidationInStoreAction($data, 'exists');
    }

    public function testInvalidationGendersIdField()
    {
        $data = [
            'genders_id' => 'a'
        ];
        $this->assertInvalidationInUpdateAction($data, 'array');
        $this->assertInvalidationInStoreAction($data, 'array');

        $data = [
            'genders_id' => [100]
        ];

        $this->assertInvalidationInUpdateAction($data, 'exists');
        $this->assertInvalidationInStoreAction($data, 'exists');
    }

    public function testInvalidationMax()
    {
        $data = ['title' => str_repeat ('t', 256)];
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 'S'];

        $this->assertInvalidationInUpdateAction($data, 'integer');
        $this->assertInvalidationInStoreAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = ['year_launched' => 'a'];

        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationRationField()
    {
        $data = ['rating' => 0];

        $this->assertInvalidationInUpdateAction($data, 'in');
        $this->assertInvalidationInStoreAction($data, 'in');
    }

    public function testSave()
    {
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $gender->categories()->sync($category->id);

        $data = [
            [
                'send_data' => $this->sendData + [
                        'categories_id' => [$category->id],
                        'genders_id' => [$gender->id],
                    ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData +
                    [
                        'opened' => true,
                        'categories_id' => [$category->id],
                        'genders_id' => [$gender->id],
                    ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData +
                    [
                        'rating' => Video::RATING_LIST[1],
                        'categories_id' => [$category->id],
                        'genders_id' => [$gender->id],
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ],

        ];

        foreach($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );

            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );

            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $this->assertHasCategory(
                $response->json('id'),
                $value['send_data']['categories_id'][0]
            );

            $this->assertHasGender(
                $response->json('id'),
                $value['send_data']['genders_id'][0]
            );
        }
    }

//    public function testSaveWithoutFiles()
//    {
//        $testData = Arr::except($this->sendData, ['categories_id', 'gender_id']);
//    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route ('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));

    }

    protected function assertHasCategory($videoId, $categoryID)
    {
        $this->assertDatabaseHas('category_video',
            [
                'video_id' => $videoId,
                'category_id' => $categoryID,
            ]
        );
    }

    protected function assertHasGender($videoId, $genderId)
    {
        $this->assertDatabaseHas('gender_video',
            [
                'video_id' => $videoId,
                'gender_id' => $genderId,
            ]
        );
    }
}
