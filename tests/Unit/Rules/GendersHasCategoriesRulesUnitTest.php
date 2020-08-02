<?php

namespace Tests\Unit\Rules;

use App\Rules\GendersHasCategoriesRule;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class GendersHasCategoriesRulesUnitTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GendersHasCategoriesRule([1,1,2,2]);
        $reflectionClass = new \ReflectionClass(GendersHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $categoriesId);
    }

    public function testGendersIdValue()
    {
        $rule = $this->createRuleMock([]);

        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturnNull();

        $rule->passes('', [1,1,2,2]);

        $reflectionClass = new \ReflectionClass(GendersHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('gendersId');
        $reflectionProperty->setAccessible(true);

        $gendersId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $gendersId);
    }

    /* test if empty array*/
    public function testPassesReturnsFalseWhenCategoriesOrGendersIsArrayEmpry()
    {
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenRowsIsEmpty()
    {
        $rule = $this->createRuleMock([]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(Collect());

        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenders()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(Collect(['category_id' => 1]));

        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(Collect([
                ['category_id' => 1],
                ['category_id' => 2]
            ]));

        $this->assertTrue($rule->passes('', [1]));

        // two equuals categories
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(Collect([
                ['category_id' => 1],
                ['category_id' => 2],
                ['category_id' => 1],
                ['category_id' => 2],
            ]));
        $this->assertTrue($rule->passes('', [1]));
    }

    protected function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GendersHasCategoriesRule::class, [$categoriesId])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
