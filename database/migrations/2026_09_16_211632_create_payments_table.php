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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
        $table->string('transaction_id', 100)->unique()->nullable();
        $table->decimal('amount', 10, 2);
        $table->string('currency', 10)->default('XOF');
        $table->string('method', 50)->nullable();
        $table->enum('status', ['pending', 'successful', 'failed', 'cancelled'])->default('pending');
        $table->dateTime('paid_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('payments');
}
};
