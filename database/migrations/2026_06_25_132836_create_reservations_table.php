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
        Schema::create('reservations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignId('screening_id')
                ->constrained('screenings')
                ->cascadeOnDelete();

            $table->string('access_token_hash', 64)->unique();

            $table->string('customer_name', 80)->nullable();
            $table->string('customer_email', 254)->nullable();

            $table->string('status', 20)->default('pending');

            $table->timestampTz('expires_at');
            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();

            $table->timestampsTz();

            $table->index(
                ['screening_id', 'status', 'expires_at'],
                'reservations_screening_status_expires_index'
            );

            $table->index(
                ['status', 'expires_at'],
                'reservations_status_expires_index'
            );
        });

        DB::statement(
            "ALTER TABLE reservations
             ADD CONSTRAINT reservations_status_valid
             CHECK (status IN ('pending', 'paid', 'expired', 'cancelled'))"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
