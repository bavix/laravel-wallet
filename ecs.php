<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/resources/lang',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withSkip([GeneralPhpdocAnnotationRemoveFixer::class, FullyQualifiedStrictTypesFixer::class])
    ->withPreparedSets(psr12: true, common: true, symplify: true, laravel: true, strict: true);
