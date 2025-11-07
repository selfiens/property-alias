<?php

declare(strict_types=1);

namespace Selfiens\PropertyAlias;

use ReflectionClass;

/**
 * Reads (at)property lines from ClassDocs of class hierarchy
 */
class ClassDocPropertyReader
{
    /**
     * @var array<string,array{type:string,desc:string}>
     */
    private array $properties;

    /**
     * @param object|class-string $classNameOrInstance
     */
    public function __construct(object|string $classNameOrInstance)
    {
        $ref = new ReflectionClass($classNameOrInstance);

        $defs = [];
        do {
            $doc_comment = $ref->getDocComment() ?: "";
            if (preg_match_all(
                '/\*\s+@property(?<type>\s+[a-zA-Z]\w+)?\s+\$(?<alias>[^ =]+)(?<desc>.*$)?/m',
                $doc_comment,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    $defs[trim($match['alias'])] = [
                        'type' => trim($match['type'] ?? ''), // @phpstan-ignore nullCoalesce.offset (safeguard)
                        'desc' => trim($match['desc'] ?? ''),
                    ];
                }
            }
        } while ($ref = $ref->getParentClass());

        $this->properties = $defs;
    }

    /**
     * @return array<string,array{type:string,desc:string}>
     */
    public function properties(): array
    {
        return $this->properties;
    }
}
