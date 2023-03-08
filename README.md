# Property Alias

[![PHPUnit Status](https://github.com/selfiens/property-alias/actions/workflows/phpunit.yml/badge.svg)](https://github.com/selfiens/property-alias/actions)

This package enables you to create aliases for existing class properties with a simple ClassDoc setup.

Below is an example of creating an alias to `$this->foo`.

```php
/**
 * @property $foo_alias = foo
 */
 class MyClass {
    use \Selfiens\PropertyAliasTrait;

    public string $foo = 'foo';
 }
```

Then you can read/write properties via alias.

```php
$my = new MyClass();

// read
echo $my->foo_alias; // 'foo'

 // write
$my->foo_alias = 'bar';
echo $my->foo; // 'bar';
```

## Install

```sh
composer require selfiens/property-alias:^1.0
```

## Setup

This package's `\Selfiens\PropertyAliasTrait` utilizes ClassDoc as a source of definitions.
You can create aliases using the `@property` syntax, as shown in the following example:

```php
/**
 * @property $alias_name = target_property
 */
```

You can define as many aliases as you desire.

## Background

### Aliases on existing properties

This package aims to assist in accessing poorly named properties by better names.
It is designed to help in situations where you are unable to rename a property
due to reasons such as its origin from an external
API, a poorly named database field, or the high risk associated with refactoring the name.

### Defining aliases in ClassDoc with @property

The `@property` definitions in ClassDoc are understood by many IDEs,
and these IDEs will provide auto-completions and refactorings as if the aliases were real properties.
