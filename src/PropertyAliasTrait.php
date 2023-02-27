<?php

namespace Selfiens\PropertyAlias;


trait PropertyAliasTrait
{
    private ?array $_property_aliases = null;

    public function __get($alias)
    {
        if (is_callable('parent::__isset')) {
            $isset = parent::__isset($alias);
            if ($isset) {
                return parent::__get($alias);
            }
        }

        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$alias] ?? null;
        if (!$property) {
            return null;
        }

        return $this->{$property};
    }

    public function __set($alias, $value)
    {
        $this->preparePropertyAliasMap();
        $property = $this->_property_aliases[$alias] ?? null;
        if ($property) {
            $this->{$property} = $value;
            return;
        }

        if (is_callable('parent::__set')) {
            parent::__set($alias, $value);
            return;
        }

        $this->{$alias} = $value;
    }

    public function __isset($alias)
    {
        if (is_callable('parent::__isset')) {
            $isset = parent::__isset($alias);
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
            fn($defs) => filter($defs, fn($def) => str_starts_with($def['desc'], '==')),
            fn($defs) => mapKeyValue($defs, fn($alias, $def) => [$alias, ltrim(explode(" ", $def['desc'])[0], '= ')])
        );
    }
}
