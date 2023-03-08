<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace Selfiens\PropertyAlias;

/**
 * Enables property I/O via aliases
 */
trait PropertyAliasTrait
{
    private ?array $_property_aliases = null;

    public function __get($name)
    {
        if (class_parents($this, false) && is_callable(['parent', '__isset'])) {
            if (parent::__isset($name)) {
                return parent::__get($name);
            }
        }

        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$name] ?? null;
        if ($property) {
            return $this->{$property};
        }

        // Instead of returning an arbitrary value such as null,
        // let the PHP native error happen.
        return $this->returnProbablyUndefinedProperty($name);
    }

    public function __set($name, $value)
    {
        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$name] ?? null;
        if ($property) {
            $this->{$property} = $value;
            return;
        }

        if (is_callable(['parent', '__set'])) {
            parent::__set($name, $value);
            return;
        }

        $this->{$name} = $value;
    }

    public function __isset($name)
    {
        if (is_callable(['parent', '__isset'])) {
            if (parent::__isset($name)) {
                return true;
            }
        }

        $this->preparePropertyAliasMap();
        if ($this->isAliasedProperty($name)) {
            $name = $this->unaliasPropertyName($name);
        }

        return isset($this->{$name});
    }

    public function __unset($name)
    {
        if (is_callable(['parent', '__isset'])) {
            if (parent::__isset($name)) {
                parent::__unset($name);
                return;
            }
        }

        $this->preparePropertyAliasMap();
        if ($this->isAliasedProperty($name)) {
            $name = $this->unaliasPropertyName($name);
        }

        // According to the PHP manual https://www.php.net/manual/en/function.unset.php
        // - It is possible to unset even object properties visible in current context.
        // Unsetting a class property will introduce side effects, but let's do not block intended unset() calls.
        // However, the following behavior requires more attention.
        // - When using unset() on inaccessible object properties, the __unset() overloading method will be called, if declared.
        // This could lead to an infinite call.

        unset($this->{$name});
    }

    /**
     * Whether the given name is an alias or not
     * @param string $name
     * @return bool
     */
    public function isAliasedProperty(string $name): bool
    {
        return array_key_exists($name, $this->_property_aliases);
    }

    /**
     * Return defined aliases and their target property names
     * @return array<string,string> alias to target property map
     */
    public function aliasedProperties(): array
    {
        $this->preparePropertyAliasMap();
        return $this->_property_aliases;
    }

    /**
     * Return an array with keys converted to their non-aliased target property names.
     * Note: This resolves multiple levels of aliasing. Aliased keys will be resolved to their final targets.
     *
     * @param array<string|int,mixed> $kvp
     * @return array<string|int,mixed>
     */
    public function unaliasProperties(array $kvp): array
    {
        return mapKeyValue($kvp, fn($name, $value) => [$this->unaliasPropertyName($name), $value]);
    }

    /**
     * Return non-aliased original property name.
     * Note: This resolves multiple levels of aliasing.
     *
     * @param string $name
     * @return string
     */
    public function unaliasPropertyName(string $name): string
    {
        if (array_key_exists($name, $this->_property_aliases)) {
            $unaliased = $this->_property_aliases[$name];
            return $this->unaliasPropertyName($unaliased);
        }
        return $name;
    }

    /**
     * Parse ClassDoc and create an alias-target map
     * @return void
     */
    protected function preparePropertyAliasMap(): void
    {
        $this->_property_aliases ??= $this->parsePropertyDefs((new ClassDocPropertyReader($this))->properties());
    }

    /**
     * Parse ClassDoc data and return alias-target map
     * @param array<string,array{type:string,desc:string}> $property_defs
     * @return array<string,string> ['alias1'=>'target1', ...]
     */
    protected function parsePropertyDefs(array $property_defs): array
    {
        return pipe(
            $property_defs,
            fn($defs) => filter($defs, fn($def) => $def['desc'] ?? false),
            fn($defs) => map($defs, function ($def) {
                $def['desc'] = trim($def['desc']);
                return $def;
            }),
            fn($defs) => filter($defs, fn($def) => preg_match('/^\s*={1,3}\s*[a-zA-Z]/', $def['desc'])),
            fn($defs) => mapKeyValue(
                $defs,
                fn($alias, $def) => [$alias, pregMatcher('/^\s*={1,3}\s*(\w+)/', $def['desc'])[1]]
            )
        );
    }

    /**
     * Just to make it obvious that we are trying to return a probably undefined property.
     * @param string $property
     * @return mixed
     */
    protected function returnProbablyUndefinedProperty(string $property): mixed
    {
        return $this->{$property};
    }
}
