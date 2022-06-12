<?php

declare(strict_types=1);

namespace Flow\ETL;

use Flow\ETL\Exception\MissingDependencyException;

final class Dependency
{
    public static function assertClassExists(string $className, string $name, string $composerPackage) : void
    {
        if (!\class_exists($className)) {
            throw new MissingDependencyException($name, $composerPackage);
        }
    }
}
