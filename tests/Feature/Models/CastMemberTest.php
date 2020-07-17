<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        factory (CastMember::class, 1)->create ();
        $castMember = CastMember::all();
        $this->assertCount (1, $castMember);

        //testar campos de castMember
        $castMemberKey = array_keys($castMember->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $castMemberKey
        );
    }

    public function testCreate(){
        $castMember = CastMember::create ([
           'name' => 'Cast 1',
            'type' => 1
        ]);
        $castMember->refresh();

        $this->assertEquals('Cast 1', $castMember->name);
        $this->assertEquals(1, $castMember->type);
        $this->assertNotNull($castMember->type);
    }

    public function testUpdate(){
        $castMember = factory(CastMember::class)->create([
            'name' => 'test_description 1',
            'type' => 2
        ])->first();

        $data = [
            'name' => 'test_name_update',
            'type' => 2
        ];
        $castMember->update ($data);

        foreach ($data as $key => $value){
            $this->assertEquals ($value, $castMember->$key);
        }
    }

    public function testCreateUuid(){
        $castMember = CastMember::create([
                'name' => 'test1',
                'type' => 1,
            ]
        );

        $this->assertIsString($castMember->id);
    }

    public function testDelete() {
        $data = [
            'name' => 'test_name',
        ];
        $castMember = factory (CastMember::class)->create($data)->first();
        $castMember->delete();

        $this->assertSoftDeleted($castMember);
    }
}
