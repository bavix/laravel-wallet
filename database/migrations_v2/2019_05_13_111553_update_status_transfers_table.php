<?php

use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateStatusTransfersTable extends Migration
{

    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Transfer())->getTable();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        $enums = [
            Transfer::STATUS_TRANSFER,
            Transfer::STATUS_PAID,
            Transfer::STATUS_REFUND,
            Transfer::STATUS_GIFT,
        ];

        if (DB::connection() instanceof MySqlConnection) {
            $table = $this->table();
            $enumString = implode('\', \'', $enums);
            $default = Transfer::STATUS_TRANSFER;
            DB::statement("ALTER TABLE $table CHANGE COLUMN status status ENUM('$enumString') NOT NULL DEFAULT '$default'");
            DB::statement("ALTER TABLE $table CHANGE COLUMN status_last status_last ENUM('$enumString') NULL");
            return;
        }

        if (DB::connection() instanceof PostgresConnection) {
            $this->alterEnum($this->table(), 'status', $enums);
            $this->alterEnum($this->table(), 'status_last', $enums);
            return;
        }

        Schema::table($this->table(), function (Blueprint $table) use ($enums) {
            $table->string('status')
                ->default(Transfer::STATUS_TRANSFER)
                ->change();

            $table->string('status_last')
                ->nullable()
                ->change();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        DB::table($this->table())
            ->where('status', Transfer::STATUS_TRANSFER)
            ->update(['status' => Transfer::STATUS_PAID]);

        DB::table($this->table())
            ->where('status_last', Transfer::STATUS_TRANSFER)
            ->update(['status_last' => Transfer::STATUS_PAID]);

        $enums = [
            Transfer::STATUS_PAID,
            Transfer::STATUS_REFUND,
            Transfer::STATUS_GIFT,
        ];

        if (DB::connection() instanceof MySqlConnection) {
            $table = $this->table();
            $enumString = implode('\', \'', $enums);
            $default = Transfer::STATUS_PAID;
            DB::statement("ALTER TABLE $table CHANGE COLUMN status status ENUM('$enumString') NOT NULL DEFAULT '$default'");
            DB::statement("ALTER TABLE $table CHANGE COLUMN status_last status_last ENUM('$enumString') NULL");
            return;
        }

        if (DB::connection() instanceof PostgresConnection) {
            $this->alterEnum($this->table(), 'status', $enums);
            $this->alterEnum($this->table(), 'status_last', $enums);
            return;
        }

        Schema::table($this->table(), function (Blueprint $table) use ($enums) {
            $table->string('status')
                ->default(Transfer::STATUS_PAID)
                ->change();

            $table->string('status_last')
                ->nullable()
                ->change();
        });
    }

    /**
     * Alter an enum field constraints
     * @param $table
     * @param $field
     * @param array $options
     */
    protected function alterEnum($table, $field, array $options): void
    {
        $check = "${table}_${field}_check";

        $enumList = [];

        foreach ($options as $option) {
            $enumList[] = sprintf("'%s'::CHARACTER VARYING", $option);
        }

        $enumString = implode(', ', $enumList);

        DB::transaction(function () use ($table, $field, $check, $options, $enumString) {
            DB::statement(sprintf('ALTER TABLE %s DROP CONSTRAINT %s;', $table, $check));
            DB::statement(sprintf('ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s::TEXT = ANY (ARRAY[%s]::TEXT[]))', $table, $check, $field, $enumString));
        });
    }

}
