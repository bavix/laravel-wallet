<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Illuminate\Contracts\Translation\Translator;

final class TranslatorService implements TranslatorServiceInterface
{
    public function __construct(
        private Translator $translator
    ) {
    }

    public function get(string $key): string
    {
        $value = $this->translator->get($key);
        assert(is_string($value));

        return $value;
    }
}
