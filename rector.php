<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelLevelSetList;

return static function (RectorConfig $config): void {
    $config->parallel();
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // Define what rule sets will be applied
    $config->import(PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $config->import(LaravelLevelSetList::UP_TO_LARAVEL_110);
    $config->import(PHPUnitSetList::PHPUNIT_100);
    $config->import(SetList::STRICT_BOOLEANS);
    $config->import(SetList::PRIVATIZATION);
    $config->import(SetList::EARLY_RETURN);
    $config->import(SetList::INSTANCEOF);
    $config->import(SetList::CODE_QUALITY);
    $config->import(SetList::DEAD_CODE);
    $config->import(SetList::PHP_82);
};
