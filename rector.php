<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use RectorLaravel\Rector\ClassMethod\MigrateToSimplifiedAttributeRector;
use RectorLaravel\Set\LaravelLevelSetList;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([MigrateToSimplifiedAttributeRector::class, RemoveDeadZeroAndOneOperationRector::class])
    ->withSets([LaravelLevelSetList::UP_TO_LARAVEL_110])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withPhpSets(php83: true);
