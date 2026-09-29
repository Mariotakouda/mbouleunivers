<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * L'affiche est stockée en base (et non sur le disque) : sur un hébergeur gratuit comme Render,
 * le disque est effacé à chaque redémarrage. Table séparée pour ne pas alourdir les requêtes sur « events ».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_posters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->string('mime', 100);
            $table->longText('data'); // image encodée en base64
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_posters');
    }
};
