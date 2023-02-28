<?php

declare(strict_types=1);

namespace Selfiens\PropertyAlias;

/**
 * Enables property I/O via aliases
 */
trait PropertyAliasTrait
{
    private ?array $_property_aliases = null;

    public function __get($alias)
    {
        if (class_parents($this, false) && is_callable(['parent', '__isset'])) {
            $isset = ['parent', '__isset']($alias);
            if ($isset) {
                return ['parent', '__get']($alias);
            }
        }

        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$alias] ?? null;

        if ($property) {
            return $this->{$property};
        }

        // Instead of returning an arbitrary value such as null,
        // let the PHP native error happen.
        return $this->returnProbablyUndefinedProperty($alias);
    }

    public function __set($alias, $value)
    {
        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$alias] ?? null;
        if ($property) {
            $this->{$property} = $value;
            return;
        }

        if (is_callable(['parent', '__set'])) {
            ['parent', '__set']($alias, $value);
            return;
        }

        $this->{$alias} = $value;
    }

    public function __isset($alias)
    {
        if (is_callable(['parent', '__isset'])) {
            $isset = ['parent', '__isset']($alias);
            if ($isset) {
                return true;
            }
        }

        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$alias] ?? null;
        if ($property) {
            return property_exists($this, $property) && $this->{$property} !== null;
        }

        return false;
    }

    /**
     * Returns defined aliases and their target property names
     * @return array<string,string> alias to target property map
     */
    public function aliasedProperties(): array
    {
        $this->preparePropertyAliasMap();
        return $this->_property_aliases;
    }

    protected function preparePropertyAliasMap(): void
    {
        $this->_property_aliases ??= $this->parsePropertyDefs((new ClassDocPropertyReader($this))->properties());
    }

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

    protected function returnProbablyUndefinedProperty($property)
    {
        return $this->{$property};
    }
}
