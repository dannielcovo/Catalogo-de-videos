<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenderResource;
use App\Models\Gender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenderController extends BasicCrudController
{

    private $rules =  [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = DB::transaction(function() use ($request, $validateData, $self){
            $obj = $this->model()::create($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        /* @var Video $obj*/
        $obj =  DB::transaction(function() use($obj, $request, $validateData, $self){
            $obj->update($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });

        $resource = $this->resource();
        return new $resource($obj);
    }

    protected function handleRelations($gender, $request)
    {
        $gender->categories()->sync($request->get('categories_id'));
    }

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

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return GenderResource::class;
    }
}
