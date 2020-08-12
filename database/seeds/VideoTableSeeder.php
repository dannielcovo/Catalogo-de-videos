<?php

use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;

class VideoTableSeeder extends Seeder
{
    private $allGenders;
    private $relations = [
        'genders_id' => [],
        'categories_id' => []
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenders = Gender::all();
        Model::reguard(); // mass assigment .. É seguranca caso insira um field errado pro banco
        factory(Video::class, 30)
            ->make()
            ->each(function (Video $video) use ($self) {
                $self->fetchRelations();
                Video::create(
                    array_merge(
                        $video->toArray(), // retorna thumb_file, banner_file...
                        [ // essas chaves vao substituir as anteriores...
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'trailer_file' => $self->getVideoFile(),
                            'video_file' => $self->getVideoFile(),
                        ],
                        $this->relations
                    )
                );
            });
        Model::unguard();
    }

    //create realtions genders -> category
    public function fetchRelations()
    {
        $subGenders = $this->allGenders->random(5)->load('categories'); // load relationship categories
        $categoriesId = [];
        foreach ($subGenders as $subGender) {
            array_push($categoriesId, ...$subGender->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $subGendersId = $subGenders->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genders_id'] = $subGendersId;
    }

    public function getImageFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/thumbs/imagem erro teste.png'),
            'imagem erro teste.png'
        );
    }

    public function getVideoFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/videos/video_disney.mp4'),
            'video_disney.mp4'
        );
    }
}
