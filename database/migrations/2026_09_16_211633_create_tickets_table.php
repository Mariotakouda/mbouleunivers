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
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
        $table->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
        $table->string('ticket_number', 100)->unique();
        $table->string('qr_code', 255)->unique();
        $table->enum('status', ['valid', 'used', 'cancelled'])->default('valid');
        $table->dateTime('generated_at');
        $table->dateTime('used_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('tickets');
}
};
