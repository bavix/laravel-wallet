<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Bavix\Wallet\Models\Transaction;

class CreateTransactionsTable extends Migration
{

    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Transaction())->getTable();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->table(), function(Blueprint $table) {
            $table->increments('id');
            $table->morphs('payable');
            $table->enum('type', ['deposit', 'withdraw'])->index();
            $table->bigInteger('amount');
            $table->boolean('confirmed');
            $table->json('meta')->nullable();
            $table->uuid('uuid')->unique();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id', 'type']);
            $table->index(['payable_type', 'payable_id', 'confirmed']);
            $table->index(['payable_type', 'payable_id', 'type', 'confirmed']);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::drop($this->table());
    }

}
