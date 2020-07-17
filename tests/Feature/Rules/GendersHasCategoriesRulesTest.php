<?php

namespace Tests\Feature\Rules;

use App\Models\Category;
use App\Models\Gender;
use App\Rules\GendersHasCategoriesRule;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GendersHasCategoriesRulesTest extends TestCase
{
    use DatabaseMigrations;

    /*
     * @var collection
     */
    private $categories;
    private $genders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categories = factory(Category::class, 4)->create();
        $this->genders = factory(Gender::class, 2)->create();

        $this->genders[0]->categories()->sync([
           $this->categories[0]->id,
           $this->categories[1]->id,
        ]);
        $this->genders[1]->categories()->sync([
            $this->categories[2]->id
        ]);
        //category 4 (3) ficou sem ligacao com generos
    }

    public function testPassesIsValid()
    {
        $rule = new GendersHasCategoriesRule(
            [
                $this->categories[2]->id
            ]
        );
        $isValid = $rule->passes('', [
           $this->genders[1]->id ,
        ]);
        $this->assertTrue($isValid);

        $rule = new GendersHasCategoriesRule(
            [
                $this->categories[0]->id,
                $this->categories[2]->id,
            ]
        );
        $isValid = $rule->passes('', [
            $this->genders[0]->id,
            $this->genders[1]->id,
        ]);
        $this->assertTrue($isValid);

        $rule = new GendersHasCategoriesRule(
            [
                $this->categories[0]->id,
                $this->categories[1]->id,
                $this->categories[2]->id,
            ]
        );
        $isValid = $rule->passes('', [
            $this->genders[0]->id,
            $this->genders[1]->id,
        ]);
        $this->assertTrue($isValid);
    }

    public function testPassesInvalid()
    {
        $rule = new GendersHasCategoriesRule(
            [
                $this->categories[0]->id,
            ]
        );
        $isValid = $rule->passes('', [
            $this->genders[0]->id,
            $this->genders[1]->id,
        ]);
        $this->assertFalse($isValid);
    }
}
