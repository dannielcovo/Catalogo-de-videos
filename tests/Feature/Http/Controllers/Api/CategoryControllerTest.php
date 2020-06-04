<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Carbon\Language;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    public function testIndex()
    {
        $category = factory (Category::class)->create();
        $response = $this->get(route ('categories.index'));
        $response
            ->assertStatus(200)
            ->assertJson ([$category->toArray ()]);

    }

    public function testShow()
    {
        $category = factory (Category::class)->create();
        $response = $this->get(route ('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson ($category->toArray ());

    }

    //ver se aparece erro de validacao dos dados
    public function testInvalidationData()
    {
//        $data = ['name' => ''];
//        $this->assertInvalidationInStoreAction ($data, 'required');

        $data = ['name' => str_repeat ('a', 256)];
        $this->assertInvalidationInStoreAction ($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction ($data, 'boolean');

        $category = factory (Category::class)->create ();
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => str_repeat ('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

    }

    public function testStore()
    {
        $response = $this->json ('POST', route ('categories.store'),[
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertStatus (201)
            ->assertJson ($category->toArray ());
        $this->assertTrue ($response->json ('is_active'));
        $this->assertNull ($response->json ('description'));

        //test is active false and description not null
        $response = $this->json ('POST', route ('categories.store'),[
            'name' => 'test',
            'is_active' => false,
            'description' => 'teste description'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertJsonFragment ([
            'is_active' => false,
            'description' => 'teste description'
        ]);

        $this->assertFalse($response->json ('is_active'));
        $this->assertNotNull($response->json ('description'));
    }

    public function testUpdate()
    {
        $category = factory (Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->json ('PUT', route ('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response->assertStatus (200)
            ->assertJson ($category->toArray ())
            ->assertJsonFragment ([
                'description' => 'test',
                'is_active' => true
            ]);


        $response = $this->json ('PUT', route ('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => '',
            ]
        );

        $response->assertJsonFragment ([
                'description' => null
            ]);

    }

    public function testDelete()
    {
        $category = factory (Category::class)->create([
            'description' => 'name teste'
        ]);

        $response = $this->json ('DELETE', route ('categories.destroy', ['category' => $category->id]));

        $response->assertStatus (204);
        $this->assertNull (Category::find ($category->id));
        $this->assertNotNull (Category::withTrashed()->find($category->id));

    }

    protected function assertInvalidationRequired(TestResponse $response) {

        $this->assertInvalidationFields ($response, ['name'], 'required');
        $response->assertJsonMissingValidationErrors (['is_active']);
//            ->assertStatus(422)
//            ->assertJsonValidationErrors (['name'])
//            ->assertJsonMissingValidationErrors (['is_active'])
//            ->assertJsonFragment ([
//                \Lang::get('validation.required', ['attribute' => 'name'])
//        ]);

    }

    protected function assertInvalidationMax(TestResponse $response) {
        $this->assertInvalidationFields ($response, ['name'], 'max.string', ['max' => 255]);
    }

    protected function assertInvalidationBoolean(TestResponse $response) {
        $this->assertInvalidationFields ($response, ['is_active'], 'boolean');
    }

    protected function routeStore()
    {
        return route('categories.store');
    }
}
