<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface TranslatorServiceInterface
{
    /**
     * Returns a translated string for the given key.
     *
     * @param string $key The key of the translation. This is the identifier of the
     *                    translation string in the translation files.
     * @return string The translated string. This is the translated version of the
     *                translation string specified by the given key. If the translation
     *                string does not exist, the key itself is returned.
     */
    public function get(string $key): string;
}
