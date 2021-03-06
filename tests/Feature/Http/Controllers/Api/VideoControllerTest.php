<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Gender;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;
use Tests\Traits\TestUpload;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestUpload;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use DatabaseMigrations;
    /**
     * @var Gender $gender
     */
    private $video;
    private $sendData;
    private $serializedFields;

    protected function setUp(): void
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

        $this->serializedFields = [
            'id',
            'title',
            'description',
            'year_launched',
            'rating',
            'duration',
            'created_at',
            'updated_at',
            'deleted_at',
            'video_file',
            'thumb_file',
            'banner_file',
            'trailer_file',
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15],
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'meta' => [],
                'links' => []
            ]);
    }

    //    public function testRolbackStore()
    //    {
    //        $controller = \Mockery::mock(VideoController::class)
    //            ->makePartial()  // simula os metodos publicos
    //            ->shouldAllowMockingProtectedMethods();
    //
    //        // simulate validate method
    //        $controller
    //            ->shouldReceive('validate')
    //            ->withAnyArgs()
    //            ->andReturn($this->sendData);
    //
    //        // simulate rulesStore method
    //        $controller
    //            ->shouldReceive('rulesStore')
    //            ->withAnyArgs()
    //            ->andReturn([]);
    //
    //        // ter a excessao
    //        $controller
    //            ->shouldReceive('handleRelations')
    //            ->once()
    //            ->andThrow( new TestException());
    //
    //        //chamar o store direto mock da request
    //        $request = \Mockery::mock(Request::class);
    //
    //        $request->shouldReceive('get')
    //            ->withAnyArgs()
    //            ->andReturnNull();
    //
    //        $hasError = false;
    //        // tem que cair no count pro teste ser validado
    //        // transacao nao aconteceu
    //        try {
    //            $controller->store($request);
    //        } catch (TestException $e) {
    //            $this->assertCount(1, Video::all());
    //            $hasError = true;
    //        }
    //
    //        $this->assertTrue($hasError);
    //    }
    //    public function testRollbackUpdate()
    //    {
    //        $controller = \Mockery::mock(VideoController::class)
    //            ->makePartial()
    //            ->shouldAllowMockingProtectedMethods();
    //
    //        $controller
    //            ->shouldReceive('validate')
    //            ->withAnyArgs()
    //            ->andReturn($this->sendData);
    //
    //        $controller
    //            ->shouldReceive('findOrFail')
    //            ->withAnyArgs()
    //            ->andReturn($this->video);
    //
    //        $controller
    //            ->shouldReceive('rulesUpdate')
    //            ->withAnyArgs()
    //            ->andReturn([]);
    //
    //        $controller
    //            ->shouldReceive('handleRelations')
    //            ->once()
    //            ->andThrow(new TestException());
    //
    //        $request = \Mockery::mock(Request::class);
    //
    //        $request->shouldReceive('get')
    //            ->withAnyArgs()
    //            ->andReturnNull();
    //
    //        $hasError = false;
    //
    //        try{
    //            $controller->update($request, 1); // qualquer param
    //        }
    //        catch (TestException $exception){
    //            $this->assertCount(1, Video::all());
    //            $hasError = true;
    //        }
    //
    //        $this->assertTrue($hasError);
    //    }f
    //    public function testSyncCategories()
    //    {
    //        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
    //        $gender = factory(Gender::class)->create();
    //        $gender->categories()->sync($categoriesId);
    //        $genderId = $gender->id;
    //
    //        //action
    //        $response = $this->json(
    //            'POST',
    //            $this->routeStore(),
    //            $this->sendData + [
    //                'genders_id' => [$genderId],
    //                'categories_id' => [$categoriesId[0]]
    //            ]
    //        );
    //
    //        // asserts
    //        $this->assertDatabaseHas('category_video', [
    //            'category_id' => $categoriesId[0],
    //            'video_id' => $response->json('id')
    //        ]);
    //
    //        //test update - action
    //        $response = $this->json(
    //            'PUT',
    //            route('videos.update', ['video' => $response->json('id')]),
    //            $this->sendData + [
    //                'genders_id' => [$genderId],
    //                'categories_id' => [$categoriesId[1], $categoriesId[2]]
    //            ]
    //        );
    //
    //        //assert data category_video in database
    //        $this->assertDatabaseHas('category_video', [
    //            'category_id' => $categoriesId[1],
    //            'video_id' => $response->json('id')
    //        ]);
    //        $this->assertDatabaseHas('category_video', [
    //            'category_id' => $categoriesId[2],
    //            'video_id' => $response->json('id')
    //        ]);
    //    }
    //    public function testSyncGenders()
    //    {
    //        $genders = factory(Gender::class, 3)->create();
    //        $gendersId = $genders->pluck('id')->toArray();
    //        $categoryId = factory(Category::class)->create()->id;
    //
    //        //relacionar o genero com a categoria
    //        $genders->each(function ($gender) use ($categoryId){
    //            $gender->categories()->sync($categoryId);
    //        });
    //
    //        //action
    //        $response = $this->json(
    //            'POST',
    //            $this->routeStore(),
    //            $this->sendData + [
    //                'categories_id' => [$categoryId],
    //                'genders_id' => [$gendersId[0]]
    //            ]
    //        );
    //
    //        // asserts
    //        $this->assertDatabaseHas('gender_video', [
    //            'gender_id' => $gendersId[0],
    //            'video_id' => $response->json('id')
    //        ]);
    //
    //        //test update - action
    //        $response = $this->json(
    //            'PUT',
    //            route('videos.update', ['video' => $response->json('id')]),
    //            $this->sendData + [
    //                'categories_id' => [$categoryId],
    //                'genders_id' => [$gendersId[1], $gendersId[2]]
    //            ]
    //        );
    //        $this->assertDatabaseMissing('gender_video', [
    //            'gender_id' => $gendersId[0],
    //            'video_id' => $response->json('id')
    //        ]);
    //
    //        //assert data category_video in database
    //        $this->assertDatabaseHas('gender_video', [
    //            'gender_id' => $gendersId[1],
    //            'video_id' => $response->json('id')
    //        ]);
    //        $this->assertDatabaseHas('gender_video', [
    //            'gender_id' => $gendersId[2],
    //            'video_id' => $response->json('id')
    //        ]);
    //    }

    public function testInvalidationDataRequired()
    {

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

    public function testInvalidateVideoFile()
    {
        //Assert
        $this->assertInvalidationFile('video_file', 'mp4', 12, 'mimetypes', ['values' => 'video/mp4']);
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        //vincula categoria e genero
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $gender->categories()->sync($category->id);
        $response = $this->json('POST', $this->routeStore(), $this->sendData + [
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id],
            ] + $files);
        $response->assertStatus(201);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        //vincula categoria e genero
        $category = factory(Category::class)->create();
        $gender = factory(Gender::class)->create();
        $gender->categories()->sync($category->id);
        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + [
                'categories_id' => [$category->id],
                'genders_id' => [$gender->id],
            ] + $files);
        $response->assertStatus(200);
        $id = $response->json('id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
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
        $data = ['title' => str_repeat('t', 256)];
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
                'send_data' => $this->sendData + [
                        'opened' => true,
                        'categories_id' => [$category->id],
                        'genders_id' => [$gender->id],
                    ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                        'rating' => Video::RATING_LIST[1],
                        'categories_id' => [$category->id],
                        'genders_id' => [$gender->id],
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure([
               'data' => $this->serializedFields
            ]);
            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertHasCategory($response->json('data.id'), $value['send_data']['categories_id'][0]);
            $this->assertHasGender($response->json('data.id'), $value['send_data']['genders_id'][0]);
        }
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function assertHasCategory($videoId, $categoryID)
    {
        $this->assertDatabaseHas('category_video', [
                'video_id' => $videoId,
                'category_id' => $categoryID,
            ]);
    }

    protected function assertHasGender($videoId, $genderId)
    {
        $this->assertDatabaseHas('gender_video', [
                'video_id' => $videoId,
                'gender_id' => $genderId,
            ]);
    }

    protected function getFiles()
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg')
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
