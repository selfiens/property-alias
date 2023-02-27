<?php

namespace Selfiens\PropertyAlias;

function pipe($data, ...$fns)
{
    return \array_reduce($fns, fn($data, $fn) => $fn($data), $data);
}

function map($array, $callable): array
{
    return \array_map($callable, $array);
}

function mapKeyValue($array, $callable): array
{
    $result = [];
    foreach ($array as $key => $value) {
        [$k, $v] = $callable($key, $value);
        $result[$k] = $v;
    }
    return $result;
}

function filter($array, $callable): array
{
    return \array_filter($array, $callable);
}