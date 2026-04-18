<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use RectorLaravel\Rector\ClassMethod\MigrateToSimplifiedAttributeRector;
use RectorLaravel\Set\LaravelLevelSetList;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([__DIR__.'/src', __DIR__.'/tests'])
    ->withRootFiles()
    ->withSkip([MigrateToSimplifiedAttributeRector::class, RemoveDeadZeroAndOneOperationRector::class])
    ->withSets([LaravelLevelSetList::UP_TO_LARAVEL_130])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withPhpSets(php84: true);
