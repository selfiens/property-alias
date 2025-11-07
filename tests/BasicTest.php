<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $bar==foo
 * @property $goo==zoo
 */
#[\AllowDynamicProperties]
class PropertyAliasTester
{
    use PropertyAliasTrait;

    public string          $foo = 'foo';
    public string|int|null $zoo = 'zoo';

    protected function returnNativeProperty(string $property): mixed
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        return "UNDEFINED(UNIT-TEST)";
    }
}

class BasicTest extends TestCase
{
    public function testIo()
    {
        $obj    = new PropertyAliasTester();
        $actual = $obj->bar;
        $this->assertEquals($obj->foo, $actual);

        $rnd      = uniqid();
        $obj->bar = $rnd;
        $this->assertEquals($rnd, $obj->foo);

        $rnd      = uniqid();
        $obj->foo = $rnd;
        $this->assertEquals($rnd, $obj->bar);
    }

    /**
     * Test the behavior of accessing undefined property
     * @return void
     */
    public function testUndefined()
    {
        // accessing undefined property
        $obj    = new PropertyAliasTester();
        $actual = $obj->undefined_property;
        $this->assertEquals("UNDEFINED(UNIT-TEST)", $actual);
    }
}
