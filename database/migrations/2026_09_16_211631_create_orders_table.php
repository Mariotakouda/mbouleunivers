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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
        $table->string('reference', 50)->unique();
        $table->string('customer_name', 150);
        $table->string('customer_phone', 20);
        $table->string('customer_email', 150);
        $table->decimal('total_amount', 10, 2);
        $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
        $table->dateTime('expires_at')->nullable();
        $table->dateTime('paid_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('orders');
}
};
