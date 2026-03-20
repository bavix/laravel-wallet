<?php

declare(strict_types=1);

use Bavix\Wallet\Test\Infra\PackageModels\WalletState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table((new WalletState())->getTable(), static function (Blueprint $table) {
            $table->string('held_balance')
                ->nullable();

            $table->string('balance_after')
                ->nullable();

            $table->string('state_hash', 64)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table((new WalletState())->getTable(), static function (Blueprint $table) {
            $table->dropColumn(['held_balance', 'balance_after', 'state_hash']);
        });
    }
};
