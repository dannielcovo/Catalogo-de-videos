<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Requests\GenderRequest;
use App\Models\Gender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class GenderControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use DatabaseMigrations;
    public function testIndex()
    {
        $gender = factory (Gender::class)->create();
        $response  = $this->get(route ('genders.index'));

        $gender->refresh ();
        $response
            ->assertStatus(200)
            ->assertJson([$gender->first()->toArray ()]);
    }

    public function testShow()
    {
        $gender = factory (Gender::class)->create();
        $response = $this->get(route ('genders.show',['gender' => $gender->id]));

        $response
            ->assertStatus(200)
            ->assertJson ($gender->toArray ());
    }

    public function testInvalidationData(){
        $response = $this->json ('POST', route ('genders.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json ('POST', route ('genders.store'), [
            'name' => str_repeat ('g', 256),
            'is_active' => 'inativo'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        $gender = factory (Gender::class)->create ();
        $response = $this->json('PUT', route('genders.update', ['gender' => $gender->id]), []);

        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('genders.update', ['gender' => $gender->id]), [
            'name' => str_repeat ('b', 256),
            'is_active' => 'true'
        ]);

        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    public function testStore()
    {
        $response = $this->json ('POST', route ('genders.store'), [
            'name' => 'test Save'
        ]);

        $id = $response->json ('id');
        $gender = Gender::find ($id);

        $response
            ->assertStatus (201)
            ->assertJson ($gender->toArray ());
        $this->assertTrue ($response->json ('is_active'));
        $this->assertEquals ('test Save', $gender->name);

        //test is active false and description not null
        $response = $this->json ('POST', route ('genders.store'),[
            'name' => 'teste gender',
            'is_active' => false,
        ]);

        $response->assertJsonFragment ([
            'is_active' => false,
        ]);

        $this->assertFalse($response->json ('is_active'));

    }

    public function testUpdate()
    {
        $gender = factory (Gender::class)->create([
            'name' => 'teste create',
            'is_active' => false
        ]);

        $response = $this->json ('PUT', route ('genders.update', ['gender' => $gender->id]),
            [
                'name' => 'name updated',
                'is_active' => true
            ]
        );
        $id = $response->json ('id');
        $gender = Gender::find($id);

        $response
            ->assertStatus(200)
            ->assertJson ($gender->toArray ())
            ->assertJsonFragment ([
                'name' => 'name updated',
                'is_active' => true
             ]);

    }

    public function testDelete()
    {
        $gender = factory (Gender::class)->create([
            'name' => 'name teste'
        ]);

        $response = $this->json ('DELETE', route ('genders.destroy', ['gender' => $gender->id]));

        $response->assertStatus (204);
        $this->assertNull (Gender::find($gender->id));
        $this->assertNotNull (Gender::withTrashed()->find($gender->id));

    }
    private function assertInvalidationRequired(TestResponse $response) {
        $response
            ->assertStatus (422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment ([
               \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    private function assertInvalidationMax(TestResponse $response){
        $response
            ->assertStatus (422)
            ->assertJsonValidationErrors (['is_active'])
            ->assertJsonFragment ([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    private function assertInvalidationBoolean(TestResponse $response){
        $response
            ->assertStatus (422)
            ->assertJsonValidationErrors (['is_active'])
            ->assertJsonFragment ([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);

    }
}
