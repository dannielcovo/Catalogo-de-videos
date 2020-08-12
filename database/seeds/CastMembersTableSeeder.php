<?php

use Illuminate\Database\Seeder;

class CastMembersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //get root do filesystem
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        factory(\App\Models\CastMember::class, 20)->create();
    }
}
