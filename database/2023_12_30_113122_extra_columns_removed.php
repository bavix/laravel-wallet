<?php

declare(strict_types=1);

namespace database;

use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::dropColumns($this->table(), ['from_type', 'to_type']);
    }

    public function down(): void
    {
        Schema::table($this->table(), static function (Blueprint $table) {
            $table->string('from_type')
                ->after('from_id')
            ;
            $table->string('to_type')
                ->after('to_id')
            ;
        });
    }

    private function table(): string
    {
        return (new Transfer())->getTable();
    }
};
