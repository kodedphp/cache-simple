<?php

namespace Tests\Koded\Caching;

trait ObjectAttributesTrait
{
    public function attributeEquals($expected, string $actualAttributeName, $actualClassOrObject): bool
    {
        $property = new \ReflectionProperty($actualClassOrObject, $actualAttributeName);
        $property->setAccessible(true);
        return $property->getValue($actualClassOrObject) === $expected;
    }
}
