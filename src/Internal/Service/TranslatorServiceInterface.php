<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface TranslatorServiceInterface
{
    public function get(string $key): string;
}
