<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

/**
 * Interface for wallets that deduct fees from merchant's payout instead of adding to customer's payment.
 *
 * When a wallet implements this interface, the fee is deducted from the merchant's deposit
 * instead of being added to the customer's withdrawal. This allows customers to pay only
 * the listed product price, while merchants receive the product price minus the fee.
 *
 * This interface extends Taxable to reuse the fee calculation logic (getFeePercent).
 * It can be used alongside MinimalTaxable and MaximalTaxable interfaces.
 *
 * @example
 *
 * Without MerchantFeeDeductible (current Taxable behavior):
 * - Product price: $100
 * - Fee: 5%
 * - Customer pays: $105 ($100 + $5 fee)
 * - Merchant receives: $100
 *
 * With MerchantFeeDeductible:
 * - Product price: $100
 * - Fee: 5%
 * - Customer pays: $100
 * - Merchant receives: $95 ($100 - $5 fee)
 */
interface MerchantFeeDeductible extends Taxable
{
}
