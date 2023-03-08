<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $alias_to_non_existing_property == non_existing_property
 */
class BadAliasTestSubject
{
    use PropertyAliasTrait;
}

class AliasToNonExistingPropertyTest extends TestCase
{
    /**
     * __get() an alias pointing to a non-existing property, should be handled correctly
     * @return void
     */
    public function testGet()
    {
        $s = $this->getMockBuilder(BadAliasTestSubject::class)
            ->onlyMethods(['returnProbablyUndefinedProperty'])
            ->getMock();

        $s->expects($this->once())->method('returnProbablyUndefinedProperty');

        $s->alias_to_non_existing_property;
    }

    /**
     * __set() an alias pointing to a non-existing property, should create that class property in the runtime.
     * @return void
     */
    public function testSet()
    {
        $s = new BadAliasTestSubject();
        $s->alias_to_non_existing_property = 'unit-test';
        $this->assertEquals('unit-test', $s->non_existing_property);
    }

    /**
     * __unset() an alias pointing to a non-existing property, should avert an infinite loop.
     * @return void
     */
    public function testUnset()
    {
        $s = new BadAliasTestSubject();
        unset($s->alias_to_non_existing_property);
        // passed infinite-loop risk
        $this->assertTrue(true);
    }
}
