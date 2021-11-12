<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

interface TranslatorInterface
{
    public function get(string $key): string;
}
