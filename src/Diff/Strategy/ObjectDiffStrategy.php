<?php

namespace JDS\Diff\Strategy;

use JDS\Contracts\Diff\DiffStrategyInterface;
use JDS\Http\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class ObjectDiffStrategy implements DiffStrategyInterface
{

    /**
     * @inheritDoc
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function diff(mixed $before, mixed $after): array
    {
        if (!is_object($before) || !is_object($after)) {
            throw new InvalidArgumentException("Both values must be objects.");
        }

        $class = new ReflectionClass($before);
        $props = $class->getProperties();

        $diff = [];

        foreach ($props as $property) {
            $property->setAccessible(true);

            $b = $property->getValue($before);
            $a = $property->getValue($after);

            $diff[$property->getName()] = [
                'before' => $b,
                'after' => $a,
                'changed' => $b != $a
            ];
        }

        return $diff;
    }
}

