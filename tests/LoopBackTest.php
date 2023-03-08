<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $alias_to_self == alias_to_self
 */
class LoopBackAliasTestSubject
{
    use PropertyAliasTrait;
}

class LoopBackTest extends TestCase
{
    /**
     * Self alias is not an alias
     * @return void
     */
    public function testGetSetUnset()
    {
        $s = new LoopBackAliasTestSubject();
        $this->assertFalse(isset($s->alias_to_self));
        $s->alias_to_self = '1234';
        $this->assertTrue(isset($s->alias_to_self));
        $this->assertEquals('1234', $s->alias_to_self);
        unset($s->alias_to_self);
        $this->assertFalse(isset($s->alias_to_self));
    }
}
