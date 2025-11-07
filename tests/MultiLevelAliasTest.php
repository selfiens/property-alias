<?php

namespace Selfiens\PropertyAlias;

use PHPUnit\Framework\TestCase;

/**
 * @property $alias_of_foo==foo
 * @property $alias_of_alias_of_foo==alias_of_foo
 * @property $alias_of_alias_of_alias_of_foo==alias_of_alias_of_foo
 */
#[\AllowDynamicProperties]
class MultiLevelAliasTest extends TestCase
{
    public string $foo = 'foo';

    use PropertyAliasTrait;

    public function testIo()
    {
        $actual = $this->alias_of_alias_of_alias_of_foo;
        $this->assertEquals($this->foo, $actual);

        $rnd = uniqid();
        $this->alias_of_alias_of_alias_of_foo = $rnd;
        $this->assertEquals($rnd, $this->foo);

        $rnd = uniqid();
        $this->foo = $rnd;
        $this->assertEquals($rnd, $this->alias_of_alias_of_alias_of_foo);

        unset($this->alias_of_alias_of_alias_of_foo);
        $this->assertFalse(isset($this->alias_of_alias_of_alias_of_foo));
    }
}
