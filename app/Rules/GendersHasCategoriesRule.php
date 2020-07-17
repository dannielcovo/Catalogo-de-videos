<?php

namespace App\Rules;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Validation\Rule;

class GendersHasCategoriesRule implements Rule
{
    /**
     * @var array
     */
    private $categoriesId;

    /**
     * @var array
     */
    private $gendersId;

    /**
     * Create a new rule instance.
     *
     * @return void
     * @var array $categoriesId
     */
    public function __construct(array $categoriesId)
    {
        $this->categoriesId = array_unique($categoriesId);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(!is_array($value)) {
            $value = [];
        }
        $this->gendersId = array_unique($value);

        //verify if empyt arrays
        if(!count($this->gendersId) || !count($this->categoriesId)) {
            return false;
        }

        //verify if $categories belongs genders
        $categoriesFound = [];
        foreach ($this->gendersId as $genderId) {
            $rows = $this->getRows($genderId);
            if(!$rows->count()){
                return false;
            }
            array_push($categoriesFound, ...$rows->pluck('category_id')->toArray());
        }
        $categoriesFound = array_unique($categoriesFound);
        if(count($categoriesFound) !== count($this->categoriesId)) {
            return false;
        }
        return true;
    }

    protected function getRows($genderId): Collection
    {
        return \DB::table('category_gender')
            ->where('gender_id', $genderId)
            ->whereIn('category_id', $this->categoriesId)
            ->get();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.gender_has_categories');
    }
}
