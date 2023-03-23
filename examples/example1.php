<?php

use Selfiens\PropertyAlias\PropertyAliasTrait;

/**
 * @property $betterName ==unpronounceableFieldNameFromWackyApi
 * @property $유니코드_이름 ==fieldFromDbThatNobodyShouldRename
 * @property $统一码名称 ==fieldFromDbThatNobodyShouldRename
 */
class MyValueObject1
{
    use PropertyAliasTrait;

    public string $unpronounceableFieldNameFromWackyApi = 'foo';
    public string $fieldFromDbThatNobodyShouldRename = 'bar';
}

$inst = new MyValueObject1();
assert($inst->betterName === 'foo');
assert($inst->유니코드_이름 === 'bar');
assert($inst->统一码名称 === 'bar');
