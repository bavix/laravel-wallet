<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector;
use RectorLaravel\Rector\ClassMethod\AddParentRegisterToEventServiceProviderRector;
use RectorLaravel\Set\LaravelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $config): void {
    $config->parallel();
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // Define what rule sets will be applied
    $config->import(PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $config->import(PHPUnitSetList::PHPUNIT_100);
    $config->import(LaravelSetList::LARAVEL_100);
    $config->import(SetList::CODE_QUALITY);
    $config->import(SetList::DEAD_CODE);
    $config->import(SetList::PHP_81);

    // get services (needed for register a single rule)
    $services = $config->services();

    // register a single rule
    $services->set(CallOnAppArrayAccessToStandaloneAssignRector::class);
    $services->set(AddParentRegisterToEventServiceProviderRector::class);
};
