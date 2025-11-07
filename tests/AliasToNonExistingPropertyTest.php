<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $alias_to_non_existing_property == non_existing_property
 */
#[\AllowDynamicProperties]
class BadAliasTestSubject
{
    use PropertyAliasTrait;

    public function returnNativeProperty(string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        return "UNDEFINED(UNIT-TEST)";
    }
}

class AliasToNonExistingPropertyTest extends TestCase
{
    /**
     * __get() an alias pointing to a non-existing property, should be handled correctly
     * @return void
     */
    public function testGet()
    {
        $obj    = new BadAliasTestSubject();
        $actual = $obj->alias_to_non_existing_property;
        $this->assertEquals("UNDEFINED(UNIT-TEST)", $actual);
    }

    /**
     * __set() an alias pointing to a non-existing property, should create that class property in the runtime.
     * @return void
     */
    public function testSet()
    {
        $s                                 = new BadAliasTestSubject();
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
