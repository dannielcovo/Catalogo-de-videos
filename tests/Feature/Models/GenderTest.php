<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Gender;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenderTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreate()
    {
        $gender = Gender::create ([
           'name' => 'name_test',
            'is_active' => true
        ]);

        $gender->refresh ();
        $this->assertEquals ('name_test', $gender->name);
        $this->assertTrue ($gender->is_active);

    }

    public function testList()
    {
        factory (Gender::class, 1)->create ();
        $gender = Gender::all();
        $this->assertCount (1, $gender);

        $genderKey = array_keys ($gender->first ()->getAttributes());
        $attributes = ['id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'];

        $this->assertEqualsCanonicalizing ($attributes, $genderKey);
    }

    public function testUpdate()
    {
        $gender = factory (Category::class)->create ([
            'name' => 'test_name',
            'is_active' => false
        ]);

        $data = [
            'name' => 'name_updated',
            'is_active' => true
        ];

        $gender->update ($data);

        foreach ($data as $k => $value){
            $this->assertEquals ($value, $gender->$k);
        }
    }

    public function testCreateUuid(){
        $gender = Gender::create ([
            'name' => 'test1'
        ]);

        $this->assertIsString ($gender->id);
    }

    public function testDelete() {
        $data = [
            'name' => 'test_name',
            'is_active' => false
        ];
        $gender = factory (Category::class)->create($data)->first();
        $gender->delete();

        $this->assertSoftDeleted ($gender);
    }
}
