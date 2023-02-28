# Property Alias

[![CI Status](https://github.com/selfiens/property-alias/actions/workflows/ci.yml/badge.svg)](https://github.com/selfiens/property-alias/actions)

This package allows you to create aliases for existing class properties through a simple ClassDoc setup. For instance,
you can access the property $this->foo by using the alias $this->bar instead.

```php

/**
 * @property $bar = foo
 * @property $zzz = bar
 */
 class MyClass {
    use \Selfiens\PropertyAlias;

    public string $foo = 'foo';
 }

$my = new MyClass();
// read via an alias
echo $my->bar; // 'foo'
// allows multiple level of aliasing
echo $my->zzz; // 'foo'
assert($my->foo == $my->bar);

// write via alias
$my->zzz = 'bar';
assert($my->foo == 'bar');
```