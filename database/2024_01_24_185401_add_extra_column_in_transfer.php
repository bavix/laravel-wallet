<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table($this->table(), static function (Blueprint $table) {
            $table->json('extra')
                ->nullable()
                ->after('fee');
        });
    }

    public function down(): void
    {
        Schema::dropColumns($this->table(), ['extra']);
    }

    private function table(): string
    {
        return (new Transfer())->getTable();
    }
};
