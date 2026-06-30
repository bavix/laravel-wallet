<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/resources/lang',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        GeneralPhpdocAnnotationRemoveFixer::class,
        \PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer::class,
    ])
    ->withSets([
        SetList::CLEAN_CODE,
        SetList::SYMPLIFY,
        SetList::ARRAY,
        SetList::COMMON,
        SetList::PSR_12,
        SetList::CONTROL_STRUCTURES,
        SetList::NAMESPACES,
        SetList::LARAVEL,
    ]);
