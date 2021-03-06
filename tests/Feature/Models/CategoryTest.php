<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory (Category::class, 1)->create ();
        $category = Category::all();
        $this->assertCount (1, $category);
        //testar campos de category
        $categoryKey = array_keys ($category->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $categoryKey
        );
    }

    public function testCreate(){
        $category = Category::create ([
           'name' => 'test1',
        ]);
        $category->refresh ();

        $this->assertEquals ('test1', $category->name);
        $this->assertNull ($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create ([
            'name' => 'test1',
            'description' => null
        ]);
        $this->assertNull ($category->description);

        $category = Category::create ([
            'name' => 'test1',
            'description' => 'test desciption'
        ]);
        $this->assertEquals('test desciption', $category->description);

        $category = Category::create ([
            'name' => 'test1',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);

        $category = Category::create ([
            'name' => 'test1',
            'is_active' => true
        ]);
        $this->assertTrue($category->is_active);
    }

    public function testUpdate(){
        $category = factory (Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'test_name_update',
            'description' => 'test_description_update',
            'is_active' => true
        ];
        $category->update ($data);

        foreach ($data as $key => $value){
            $this->assertEquals ($value, $category->$key);
        }
    }

    public function testCreateUuid(){
        $category = Category::create ([
           'name' => 'test1'
        ]);

        $this->assertIsString ($category->id);
    }

    public function testDelete() {
        $data = [
            'name' => 'test_name',
            'is_active' => false
        ];
        $category = factory (Category::class)->create($data)->first();
        $category->delete();

        $this->assertSoftDeleted ($category);
    }
}
