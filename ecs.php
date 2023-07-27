<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $config->paths([
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/resources/lang',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $config->skip([
        GeneralPhpdocAnnotationRemoveFixer::class,
    ]);

    $config->sets([
        SetList::CLEAN_CODE,
        SetList::SYMPLIFY,
        SetList::COMMON,
        SetList::PSR_12,
        SetList::CONTROL_STRUCTURES,
        SetList::NAMESPACES,
        SetList::STRICT,
        SetList::PHPUNIT,
    ]);
};
