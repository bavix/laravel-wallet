<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table($this->walletTable(), static function (Blueprint $table) {
            $table->softDeletesTz();
        });
        Schema::table($this->transferTable(), static function (Blueprint $table) {
            $table->softDeletesTz();
        });
        Schema::table($this->transactionTable(), static function (Blueprint $table) {
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::table($this->walletTable(), static function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table($this->transferTable(), static function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table($this->transactionTable(), static function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    private function walletTable(): string
    {
        /** @var string $table */
        $table = config('wallet.wallet.table', 'wallets');

        return $table;
    }

    private function transferTable(): string
    {
        /** @var string $table */
        $table = config('wallet.transfer.table', 'transfers');

        return $table;
    }

    private function transactionTable(): string
    {
        /** @var string $table */
        $table = config('wallet.transaction.table', 'transactions');

        return $table;
    }
};
