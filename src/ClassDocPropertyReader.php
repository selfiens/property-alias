<?php

namespace Selfiens\PropertyAlias;

use ReflectionClass;
use ReflectionException;

class ClassDocPropertyReader
{
    private array $properties;

    /**
     * @param  $classNameOrInstance
     * @throws ReflectionException
     */
    public function __construct($classNameOrInstance)
    {
        $ref = new ReflectionClass($classNameOrInstance);

        $defs = [];
        do {
            $doc_comment = $ref->getDocComment();
            if (preg_match_all(
                '/\*\s+@property(?<type>\s+[a-zA-Z]\w+)?\s+\$(?<alias>[^ =]+)(?<desc>.*$)?/m',
                $doc_comment,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    $defs[trim($match['alias'])] = [
                        'type' => trim($match['type'] ?? ''),
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
