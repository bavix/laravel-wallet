<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface JsonServiceInterface
{
    /**
     * Encode an array of data into a JSON string.
     *
     * @param array<mixed>|null $data The data to encode. If null, returns null.
     * @return string|null The JSON encoded string, or null if the input is null.
     *
     * @note The input data is expected to be an array of mixed type data.
     *       If the input is not an array, it will be converted to an array
     *       before being encoded.
     *
     * @see https://www.php.net/manual/en/function.json-encode.php
     * @see https://www.php.net/manual/en/json.constants.php
     * @see https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function encode(?array $data): ?string;
}
