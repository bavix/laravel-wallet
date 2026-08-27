<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $transactionTable = $this->transactionTable();
        $walletTable = $this->walletTable();

        Schema::table($transactionTable, static function (Blueprint $table) use ($transactionTable): void {
            $indexName = $transactionTable.'_payable_type_payable_id_index';

            if (Schema::hasIndex($transactionTable, $indexName)) {
                $table->dropIndex($indexName);
            }
        });

        Schema::table($walletTable, static function (Blueprint $table) use ($walletTable): void {
            $indexName = $walletTable.'_holder_type_holder_id_index';

            if (Schema::hasIndex($walletTable, $indexName)) {
                $table->dropIndex($indexName);
            }
        });
    }

    public function down(): void
    {
        $transactionTable = $this->transactionTable();
        $walletTable = $this->walletTable();

        Schema::table($transactionTable, static function (Blueprint $table) use ($transactionTable): void {
            $indexName = $transactionTable.'_payable_type_payable_id_index';

            if (! Schema::hasIndex($transactionTable, $indexName)) {
                $table->index(['payable_type', 'payable_id'], $indexName);
            }
        });

        Schema::table($walletTable, static function (Blueprint $table) use ($walletTable): void {
            $indexName = $walletTable.'_holder_type_holder_id_index';

            if (! Schema::hasIndex($walletTable, $indexName)) {
                $table->index(['holder_type', 'holder_id'], $indexName);
            }
        });
    }

    private function transactionTable(): string
    {
        return (new Transaction())->getTable();
    }

    private function walletTable(): string
    {
        return (new Wallet())->getTable();
    }
};
