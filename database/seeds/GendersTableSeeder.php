<?php

use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\Category;

class GendersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        factory (Gender::class, 50)
            ->create()
            ->each(function (Gender $gender) use ($categories){
                // get 5 randon categories
                $categoriesId = $categories->random(5)->pluck('id')->toArray();
                // vincula categoria a genero
                $gender->categories()->attach($categoriesId);
            });
    }
}
