<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table($this->table(), static function (Blueprint $table) {
            $table->string('bank_method')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table($this->table(), static function (Blueprint $table) {
            $table->dropColumn('bank_method');
        });
    }

    private function table(): string
    {
        /** @var string $table */
        $table = config('wallet.transaction.table', 'transactions');

        return $table;
    }
};
