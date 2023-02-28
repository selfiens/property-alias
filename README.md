# Property Alias

[![CI Status](https://github.com/selfiens/property-alias/actions/workflows/ci.yml/badge.svg)](https://github.com/selfiens/property-alias/actions)

This package enables you to create aliases for existing class properties with a simple ClassDoc setup. For example,
instead of accessing the poorly name property `$this->incmAggrgtd`,
you can access it using the alias `$this->income_aggregated`.

```php
/**
 * @property $income_aggregated = incmAggrgtd
 * @property $zzz = income_aggregated
 */
 class MyClass {
    use \Selfiens\PropertyAliasTrait;

    public string $incmAggrgtd = 'foo';
 }

$my = new MyClass();
echo $my->income_aggregated; // 'foo'
echo $my->zzz; // alias of alias, 'foo'
assert($my->incmAggrgtd == $my->income_aggregated);

$my->zzz = 'bar'; // write via alias
assert($my->incmAggrgtd == 'bar');
```

## Setup

This package's `\Selfiens\PropertyAliasTrait` utilizes ClassDoc as a source of definitions.
You can create aliases using the `@property` syntax, as shown in the following example:

```php
/**
 * @property $alias_name = target_property
 */
```

## Why

### Why alias an existing property?

This package aims to assist in accessing poorly named properties by better 'alias' names.
It is designed to help in situations where you are unable to rename a property
due to reasons such as its origin from an ex-employee, an external
API, a poorly named database field, or the high risk associated with refactoring the name.

### Why ClassDoc @property?

The `@property` definitions in ClassDoc are understood by many IDEs,
and these IDEs will provide auto-completions and refactorings as if the aliases were real properties.
