<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $bar==foo
 */
class PropertyAliasTester
{
    use PropertyAliasTrait;

    public string $foo = 'foo';
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
        // Set up a mock to check whether the returnProbablyUndefinedProperty() method is called
        // when probably undefined property is accessed via __get.
        $mock = $this->getMockBuilder(PropertyAliasTester::class)
            ->onlyMethods(['returnProbablyUndefinedProperty'])
            ->getMock();

        // the returnProbablyUndefinedProperty method should be called once
        $mock->expects($this->once())->method('returnProbablyUndefinedProperty');

        // accessing undefined property
        $mock->undefined_property;
    }

    public function testUnaliasProperties()
    {
        $obj     = new PropertyAliasTester();
        $payload = ['bar' => 'bar',];
        $actual  = $obj->unaliasProperties($payload);
        $this->assertEquals(['foo' => 'bar'], $actual);
    }
}
