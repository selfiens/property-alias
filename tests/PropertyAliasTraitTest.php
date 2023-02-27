<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $bar==foo
 */
class PropertyAliasTraitTest extends TestCase
{
    public string $foo = 'foo';

    use PropertyAliasTrait;

    public function testIo()
    {
        $actual = $this->bar;
        $this->assertEquals($this->foo, $actual);

        $rnd = uniqid();
        $this->bar = $rnd;
        $this->assertEquals($rnd, $this->foo);

        $rnd = uniqid();
        $this->foo = $rnd;
        $this->assertEquals($rnd, $this->bar);
    }
}
