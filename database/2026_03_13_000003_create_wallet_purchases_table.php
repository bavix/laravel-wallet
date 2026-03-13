<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Purchase;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('transfer_id')
                ->unique();
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('to_id');
            $table
                ->enum('status', ['exchange', 'transfer', 'paid', 'refund', 'gift'])
                ->default('transfer');
            $table->timestamps();

            $table->foreign('transfer_id')
                ->references('id')
                ->on($this->transferTable())
                ->onDelete('cascade');

            $table->index(['from_id', 'to_id', 'status', 'id'], 'wallet_purchases_from_to_status_id');
            $table->index(['to_id', 'status', 'id'], 'wallet_purchases_to_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table());
    }

    private function table(): string
    {
        return (new Purchase())->getTable();
    }

    private function transferTable(): string
    {
        return (new Transfer())->getTable();
    }
};
