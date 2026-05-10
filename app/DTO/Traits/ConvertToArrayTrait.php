<?php

declare(strict_types=1);

namespace App\DTO\Traits;

use ReflectionClass;
use ReflectionProperty;

/**
 * Provides a method to convert the properties of a class to an associative array.
 */
trait ConvertToArrayTrait
{
    /**
     * Converts the properties of the class to an associative array.
     *
     * @return array An associative array representation of the class properties
     */
    public function toArray(): array
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        $result = [];

        foreach ($properties as $property) {
            $camelCaseName = $property->getName();
            $snakeCaseName = $this->camelToSnake($camelCaseName);

            $result[$snakeCaseName] = $property->getValue($this);
        }

        return $result;
    }

    /**
     * Converts camelCase string to snake_case.
     *
     * @param string $input
     *
     * @return string
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $input));
    }
}
