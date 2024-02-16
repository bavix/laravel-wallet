<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\External\Api\TransferQuery;
use Bavix\Wallet\External\Api\TransferQueryHandlerInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Test\Infra\Factories\BuyerFactory;
use Bavix\Wallet\Test\Infra\Models\Buyer;
use Bavix\Wallet\Test\Infra\PackageModels\Transfer;
use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
use Bavix\Wallet\Test\Infra\TestCase;
use Illuminate\Database\Eloquent\Builder;
use function now;

/**
 * @internal
 */
final class TransfersFilterTest extends TestCase
{
    /**
     * @see https://github.com/bavix/laravel-wallet/issues/888
     */
    public function testFilterByHolderName(): void
    {
        /** @var TransferQueryHandlerInterface $transferQueryHandler */
        $transferQueryHandler = app(TransferQueryHandlerInterface::class);

        do {
            /**
             * @var Buyer $from
             * @var Buyer $to
             */
            [$from, $to] = BuyerFactory::times(2)->create();
        } while ($from->name === $to->name);

        // init
        $transferQueryHandler->apply([
            new TransferQuery($from, $to, 10, null),
            new TransferQuery($from, $to, 20, null),
            new TransferQuery($from, $to, 30, null),
            new TransferQuery($to, $from, 60, null),
        ]);

        $filter = static fn (Buyer $buyer): Builder => Transfer::query()
            ->whereHas(
                'from',
                fn (Builder $q) => $q->whereHas(
                    'holder',
                    fn (Builder $q) => $q->where('name', $buyer->name),
                )
            );

        $fromFiltered = $filter($from)->get()->all();
        self::assertCount(3, $fromFiltered);

        $toFiltered = $filter($to)->get()->all();
        self::assertCount(1, $toFiltered);
    }
}
