<?php

use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent as ColumnDefinition;

class AddMetaWalletsTable extends Migration
{
    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Wallet())->getTable();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $this->json($table, 'meta')
                ->nullable()
                ->after('description');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }

    /**
     * @param Blueprint $table
     * @param string $column
     * @return ColumnDefinition
     */
    public function json(Blueprint $table, string $column): ColumnDefinition
    {
        $conn = DB::connection();
        if ($conn instanceof MySqlConnection || $conn instanceof PostgresConnection) {
            $pdo = $conn->getPdo();
            try {
                $sql = 'SELECT JSON_EXTRACT(\'[10, 20, [30, 40]]\', \'$[1]\');';
                $prepare = $pdo->prepare($sql);
                $prepare->fetch();
            } catch (\Throwable $throwable) {
                return $table->text($column);
            }
        }

        return $table->json($column);
    }
}
