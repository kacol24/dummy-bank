<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_deposits', function (Blueprint $table) {
            $table->id();
            $table->snowflake('account_id')->index();

            $table->unsignedTinyInteger('interest_rate');
            $table->unsignedTinyInteger('period')->nullable();
            $table->string('period_unit')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('rollover_instruction')->nullable();
            $table->unsignedTinyInteger('rollover_counter')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_deposits');
    }
};
