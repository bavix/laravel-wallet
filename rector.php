<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Rector\ClassMethod\MigrateToSimplifiedAttributeRector;
use RectorLaravel\Set\LaravelLevelSetList;

return static function (RectorConfig $config): void {
    $config->parallel();
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // remove it in next version
    $config->skip([
        MigrateToSimplifiedAttributeRector::class,
        RemoveDeadZeroAndOneOperationRector::class,
    ]);

    // Define what rule sets will be applied
    $config->import(LaravelLevelSetList::UP_TO_LARAVEL_110);
    $config->import(SetList::STRICT_BOOLEANS);
    $config->import(SetList::PRIVATIZATION);
    $config->import(SetList::EARLY_RETURN);
    $config->import(SetList::INSTANCEOF);
    $config->import(SetList::CODE_QUALITY);
    $config->import(SetList::DEAD_CODE);
    $config->import(SetList::PHP_82);
};
