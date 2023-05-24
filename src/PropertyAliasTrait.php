<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types = 1);

namespace Selfiens\PropertyAlias;

/**
 * Enables property I/O via aliases
 */
trait PropertyAliasTrait
{
    private ?array $_property_aliases = null;

    public function __get($name)
    {
        // First, resolve alias to original property
        if ($this->isAliasedProperty($name)) {
            $name = $this->unaliasPropertyName($name);
        }

        // Now, the property is not an alias.
        // Beyond this is up to parent class' __get/__set/__isset implementation.

        // Parent implemented __get()?
        if (class_parents($this, false) && is_callable(['parent', '__get'])) {
            return parent::__get($name);
        }

        // Now it must be a member field, otherwise an error will occur.
        return $this->returnMemberField($name);
    }

    public function __set($name, $value)
    {
        if ($this->isAliasedProperty($name)) {
            $property = $this->unaliasPropertyName($name);
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

        if ($this->isAliasedProperty($name)) {
            $name = $this->unaliasPropertyName($name);
        }

        return isset($this->{$name});
    }

    public function __unset($name)
    {
        // currently processing $name
        static $processingName = "";

        if (is_callable(['parent', '__isset'])) {
            if (parent::__isset($name)) {
                parent::__unset($name);
                return;
            }
        }

        // Do not allow the same "$name" enters this method consecutively
        // via the last __unset() statement in this method.
        if ($processingName === $name) {
            return;
        }

        if ($this->isAliasedProperty($name)) {
            $name = $this->unaliasPropertyName($name);
        }

        // According to the PHP manual https://www.php.net/manual/en/function.unset.php
        // - It is possible to unset even object properties visible in current context.
        // Unsetting a class property will introduce side effects, but let's do not block intentional unset() calls.
        // However, the following behavior requires more attention.
        // - When using unset() on inaccessible object properties, the __unset() overloading method will be called, if declared.
        // This could lead to an infinite-call.

        // $processingName is an infinite-call guard
        $processingName = $name;
        unset($this->{$name});
        $processingName = "";
    }

    /**
     * Whether the given name is an alias or not
     * @param  string  $name
     * @return bool
     */
    public function isAliasedProperty(string $name): bool
    {
        $this->preparePropertyAliasMap();
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
     * @param  array<string|int,mixed>  $kvp
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
     * @param  string  $name
     * @return string
     */
    public function unaliasPropertyName(string $name): string
    {
        if (!$this->isAliasedProperty($name)) {
            return $name;
        }

        $unaliased = $this->_property_aliases[$name];
        return $this->unaliasPropertyName($unaliased); // to resolve multi-level aliasing
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
     * @param  array<string,array{type:string,desc:string}>  $property_defs
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
            ),
            fn($defs) => filter($defs, fn($target, $alias) => $target != $alias, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * Just to make it obvious that we are trying to return a member field.
     * @param  string  $property
     * @return mixed
     */
    protected function returnMemberField(string $property): mixed
    {
        return $this->{$property};
    }
}
