<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('ticket_scans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->dateTime('scanned_at');
        $table->enum('result', ['valid', 'already_used', 'invalid', 'cancelled']);
        $table->string('device_info', 255)->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('ticket_scans');
}
};
