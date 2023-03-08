<?php

declare(strict_types=1);

namespace Selfiens\PropertyAlias;

/**
 * Collection of sidekick functions
 */

/**
 * Pass data through functions
 * @param mixed $data
 * @param ...$fns
 * @return mixed
 */
function pipe(mixed $data, ...$fns): mixed
{
    return \array_reduce($fns, fn($data, $fn) => $fn($data), $data);
}

/**
 * An alias of array_map with different signature
 * @param array $array
 * @param callable $callable
 * @return array
 */
function map(array $array, callable $callable): array
{
    return \array_map($callable, $array);
}

/**
 * Maps key and value
 * @param iterable $iterable
 * @param callable $callable fn($key, $value)
 * @return array
 */
function mapKeyValue(iterable $iterable, callable $callable): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        [$k, $v] = $callable($key, $value);
        if ($k === null) {
            $result[] = $v;
        } else {
            $result[$k] = $v;
        }
    }
    return $result;
}

/**
 * An alias of array_filter
 * @param array $array
 * @param callable $callable
 * @param int $mode
 * @return array
 */
function filter(array $array, callable $callable, int $mode = 0): array
{
    return \array_filter($array, $callable, $mode);
}

function pregMatcher(string $regexp, string $subject): array
{
    if (preg_match($regexp, $subject, $matches)) {
        return $matches;
    }
    return [];
}