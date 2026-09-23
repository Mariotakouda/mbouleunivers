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
    Schema::create('order_notifications', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
        $table->string('type', 50);
        $table->enum('channel', ['email', 'sms']);
        $table->string('recipient', 150);
        $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
        $table->dateTime('sent_at')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('order_notifications');
}
};
