<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeTables extends Migration
{

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table(\config('wallet.transaction.table'), function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('wallet_id')->change();
        });

        Schema::table(\config('wallet.transfer.table'), function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            $table->unsignedBigInteger('deposit_id')->change();
            $table->unsignedBigInteger('withdraw_id')->change();
            $table->unsignedBigInteger('fee')->change();
        });

        Schema::table(\config('wallet.wallet.table'), function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table(\config('wallet.transaction.table'), function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('wallet_id')->change();
        });

        Schema::table(\config('wallet.transfer.table'), function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('deposit_id')->change();
            $table->unsignedInteger('withdraw_id')->change();
            $table->bigInteger('fee')->change();
        });

        Schema::table(\config('wallet.wallet.table'), function (Blueprint $table) {
            $table->increments('id')->change();
        });
    }

}
