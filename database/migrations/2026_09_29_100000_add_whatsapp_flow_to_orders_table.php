<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commande via WhatsApp (sans agrégateur de paiement) :
 *  - l'email devient facultatif (beaucoup de clients n'en ont pas, WhatsApp suffit) ;
 *  - message libre du client ;
 *  - suivi du moment où le client a ouvert WhatsApp pour envoyer sa commande ;
 *  - date d'annulation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email', 150)->nullable()->change();
            $table->text('customer_note')->nullable()->after('customer_email');
            $table->dateTime('whatsapp_clicked_at')->nullable()->after('expires_at');
            $table->dateTime('cancelled_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_note', 'whatsapp_clicked_at', 'cancelled_at']);
        });
    }
};
