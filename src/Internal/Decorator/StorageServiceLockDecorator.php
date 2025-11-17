<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Decorator;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\StateServiceInterface;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;

final readonly class StorageServiceLockDecorator implements StorageServiceInterface
{
    public function __construct(
        private StorageServiceInterface $storageService,
        private StateServiceInterface $stateService,
        private LockServiceInterface $lockService,
        private MathServiceInterface $mathService
    ) {
    }

    public function flush(): bool
    {
        return $this->storageService->flush();
    }

    public function forget(string $uuid): bool
    {
        return $this->storageService->forget($uuid);
    }

    public function get(string $uuid): string
    {
        return current($this->multiGet([$uuid]));
    }

    public function sync(string $uuid, float|int|string $value): bool
    {
        return $this->multiSync([
            $uuid => $value,
        ]);
    }

    /**
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string
    {
        return current($this->multiIncrease([
            $uuid => $value,
        ]));
    }

    /**
     * @param non-empty-array<non-empty-string> $uuids
     * @return non-empty-array<non-empty-string, non-empty-string>
     *
     * @throws RecordNotFoundException
     */
    public function multiGet(array $uuids): array
    {
        $missingKeys = [];
        $results = [];
        foreach ($uuids as $uuid) {
            $item = $this->stateService->get($uuid);
            if ($item === null) {
                $missingKeys[] = $uuid;

                continue;
            }

            $results[$uuid] = $item;
        }

        if ($missingKeys !== []) {
            $foundValues = $this->storageService->multiGet($missingKeys);
            foreach ($foundValues as $key => $value) {
                $results[$key] = $value;
            }
        }

        /** @var non-empty-array<non-empty-string, non-empty-string> $results */
        return $results;
    }

    public function multiSync(array $inputs): bool
    {
        return $this->storageService->multiSync($inputs);
    }

    public function multiIncrease(array $inputs): array
    {
        return $this->lockService->blocks(array_keys($inputs), function () use ($inputs): array {
            $multiGet = $this->multiGet(array_keys($inputs));
            $results = [];
            foreach ($multiGet as $uuid => $item) {
                /** @var non-empty-string $itemValue */
                $itemValue = $item;
                /** @var float|int|non-empty-string $inputValue */
                $inputValue = $inputs[$uuid];
                $added = $this->mathService->add($itemValue, $inputValue);
                $rounded = $this->mathService->round($added);
                $results[$uuid] = $rounded;
            }

            $this->multiSync($results);

            return $results;
        });
    }
}
