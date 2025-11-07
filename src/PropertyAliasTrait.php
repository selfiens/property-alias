<?php

/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace Selfiens\PropertyAlias;

/**
 * Enables property I/O via aliases
 */
trait PropertyAliasTrait
{
    /**
     * @var array<string,string>|null
     */
    private ?array $property_aliases = null;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        // First, try resolve alias to original property
        $name = $this->resolveAliasedPropertyName($name);

        // Parent implements __get()?
        if (class_parents($this, false) && is_callable([parent::class, '__get'])) {
            return parent::__get($name); // @phpstan-ignore class.noParent
        }

        return $this->returnNativeProperty($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $name = $this->resolveAliasedPropertyName($name);

        if (class_parents($this, false) && is_callable([parent::class, '__set'])) {
            parent::__set($name, $value); // @phpstan-ignore class.noParent
            return;
        }

        $this->{$name} = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        $name = $this->resolveAliasedPropertyName($name);

        if (class_parents($this, false) && is_callable([parent::class, '__isset'])) {
            return parent::__isset($name);
        }

        return isset($this->{$name});
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        $name = $this->resolveAliasedPropertyName($name);

        if (class_parents($this, false) && is_callable([parent::class, '__unset'])) {
            parent::__unset($name);
            return;
        }

        unset($this->{$name});
    }

    public function resolveAliasedPropertyName(string $name): string
    {
        return ($this->isAliasedPropertyName($name))
            ? $this->unaliasPropertyName($name)
            : $name;
    }

    /**
     * Whether the given name is an alias or not
     */
    public function isAliasedPropertyName(string $name): bool
    {
        $this->preparePropertyAliasMap();
        return array_key_exists($name, $this->property_aliases);
    }

    /**
     * Return non-aliased original property name.
     * Note: This resolves multiple levels of aliasing.
     */
    public function unaliasPropertyName(string $name): string
    {
        if (!$this->isAliasedPropertyName($name)) {
            return $name;
        }

        $unaliased = $this->property_aliases[$name];
        return $this->unaliasPropertyName($unaliased); // to resolve multi-level aliasing
    }

    /**
     * Parse ClassDoc and create an alias-target map
     */
    protected function preparePropertyAliasMap(): void
    {
        $this->property_aliases ??= $this->parsePropertyDefs((new ClassDocPropertyReader($this))->properties());
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
                fn($alias, $def) => [$alias, pregMatcher('/^\s*={1,3}\s*(\w+)/', $def['desc'])[1]],
            ),
            fn($defs) => filter($defs, fn($target, $alias) => $target != $alias, ARRAY_FILTER_USE_BOTH),
        );
    }

    /**
     * For unit-tests to override for certain scenarios
     */
    protected function returnNativeProperty(string $property): mixed
    {
        // undefined prop will emit WARNING or ERROR
        return $this->{$property};
    }
}
