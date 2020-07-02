<?php

namespace App\Http\Controllers\Api;

use App\Models\Gender;

class GenderController extends BasicCrudController
{

    private $rules =  [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];

    protected function model()
    {
        return Gender::class;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }
}
