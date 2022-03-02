<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateManagersTable extends Migration
{
    public function up(): void
    {
        Schema::create('managers', static function (Blueprint $table) {
            $table->uuid('id')
                ->primary()
            ;
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
}
