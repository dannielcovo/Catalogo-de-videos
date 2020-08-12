<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Resources\CategoryResource;
use Tests\Stubs\Models\CategoryStub;

class CategoryControllerStub extends BasicCrudController
{

    private $rules = [
        'name' => 'required|max:255',
        'description' => 'nullable'
    ];

    public function model()
    {
        return CategoryStub::class;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function resource()
    {
        return CategoryResource::class;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }
}
