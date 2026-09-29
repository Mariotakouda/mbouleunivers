<?php

namespace App\Services;

use App\Models\Ticket;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    /**
     * QR code du billet, généré à la volée sous forme d'image « data: » (SVG en base64).
     * Rien n'est stocké sur le disque : sur un hébergeur gratuit, le disque est effacé à chaque redémarrage.
     * Le QR ne contient PAS d'informations personnelles (juste un identifiant opaque).
     */
    public function dataUri(Ticket $ticket): string
    {
        return (new QRCode(new QROptions(['outputBase64' => true])))->render($ticket->qr_code);
    }

    /**
     * Vérifie et retourne le ticket correspondant à un contenu de QR scanné, ou null.
     */
    public function resolveTicket(string $scannedCode): ?Ticket
    {
        return Ticket::where('qr_code', $scannedCode)->first();
    }
}
