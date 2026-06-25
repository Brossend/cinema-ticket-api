<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('screenings', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->timestampTz('starts_at');
            $table->smallInteger('total_seats');
            $table->timestampsTz();

            $table->index('starts_at');
        });

        DB::statement(
            'ALTER TABLE screenings
         ADD CONSTRAINT screenings_total_seats_positive
         CHECK (total_seats > 0)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};
