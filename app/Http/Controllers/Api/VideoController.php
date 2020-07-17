<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GendersHasCategoriesRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/*
 * - begin transaction - Marca Inicio da transascao
 * - transaction - executa todas as transacoes
 * - commit - persiste as transacoes
 * -rollback - desfaz todas as transacoes do checkpoint
 *
 * */
class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required', //type text
            'year_launched' => 'required|date_format:Y', //verify year only
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genders_id' => [
                'required',
                'array',
                'exists:genders,id,deleted_at,NULL'
            ]
        ];
    }

    /* Relacionamentos ... Tem que Sobrescrever metodos*/

    public function store(Request $request)
    {
        $this->addRuleIfGenderHasCategories($request);
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;
        /* @var Video $obj*/
        $obj = DB::transaction(function() use ($request, $validateData, $self){
            $obj = $this->model()::create($validateData);

            //create relationship
            $self->handleRelations($obj, $request);
            return $obj;
        });

        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);

        //validate GendersHAsCategoriesRules
        $this->addRuleIfGenderHasCategories($request);
        $validateData = $this->validate($request, $this->rulesUpdate());

        $self = $this;
        /* @var Video $obj*/
        $obj =  DB::transaction(function() use($obj, $request, $validateData, $self){
            $obj->update($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });

        return $obj;
    }

    protected function addRuleIfGenderHasCategories(Request $request)
    {
        $categoriesId = $request->get('categories_id');

        //because test invalidation
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genders_id'][] = new GendersHasCategoriesRule(
            $categoriesId
        );
    }

    protected function handleRelations($video, Request $request)
    {
        // sincroniza o array com minha tabela
        $video->categories()->sync($request->get('categories_id'));
        $video->genders()->sync($request->get('genders_id'));
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
