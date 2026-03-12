<?php

declare(strict_types=1);

use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table((new Wallet())->getTable(), static function (Blueprint $table) {
            $table->string('frozen_balance')
                ->nullable();

            $table->string('final_balance')
                ->nullable();

            $table->string('checksum', 64)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table((new Wallet())->getTable(), static function (Blueprint $table) {
            $table->dropColumn(['frozen_balance', 'final_balance', 'checksum']);
        });
    }
};
