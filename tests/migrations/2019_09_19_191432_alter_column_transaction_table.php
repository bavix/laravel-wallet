<?php

declare(strict_types=1);

use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table((new Transaction())->getTable(), static function (Blueprint $table) {
            $table->string('bank_method')
                ->nullable()
            ;
        });
    }

    public function down(): void
    {
        Schema::table((new Transaction())->getTable(), static function (Blueprint $table) {
            $table->dropColumn('bank_method');
        });
    }
};
