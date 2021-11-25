<?php

declare(strict_types=1);

use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnTransactionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table((new Transaction())->getTable(), function (Blueprint $table) {
            $table->string('bank_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table((new Transaction())->getTable(), function (Blueprint $table) {
            $table->dropColumn('bank_method');
        });
    }
}
