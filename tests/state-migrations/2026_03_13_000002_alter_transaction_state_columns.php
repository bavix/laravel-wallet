<?php

declare(strict_types=1);

use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table((new TransactionState())->getTable(), static function (Blueprint $table) {
            $table->string('balance_before')
                ->nullable();

            $table->string('balance_after')
                ->nullable();

            $table->string('state_hash', 64)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table((new TransactionState())->getTable(), static function (Blueprint $table) {
            $table->dropColumn(['balance_before', 'balance_after', 'state_hash']);
        });
    }
};
