<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $tableName = $this->table();
        $indexes = $this->indexes();

        Schema::table($tableName, static function (Blueprint $table) use ($tableName, $indexes): void {
            foreach (array_keys($indexes) as $index) {
                if (Schema::hasIndex($tableName, $index)) {
                    $table->dropIndex($index);
                }
            }
        });
    }

    public function down(): void
    {
        $tableName = $this->table();
        $indexes = $this->indexes();

        Schema::table($tableName, static function (Blueprint $table) use ($tableName, $indexes): void {
            foreach ($indexes as $index => $columns) {
                if (! Schema::hasIndex($tableName, $index)) {
                    $table->index($columns, $index);
                }
            }
        });
    }

    /**
     * @return array<string, list<string>>
     */
    private function indexes(): array
    {
        return [
            'payable_type_payable_id_ind' => ['payable_type', 'payable_id'],
            'payable_type_ind' => ['payable_type', 'payable_id', 'type'],
        ];
    }

    private function table(): string
    {
        return (new Transaction())->getTable();
    }
};
