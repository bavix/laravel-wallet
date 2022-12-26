<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $containerConfigurator): void {
    $containerConfigurator->parallel();
    $containerConfigurator->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $containerConfigurator->skip([ExplicitMethodCallOverMagicGetSetRector::class]);

    // Define what rule sets will be applied
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_91);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PHP_80);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(TypedPropertyRector::class);
};
