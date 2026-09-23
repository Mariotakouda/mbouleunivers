<?php

namespace App\Services;

use App\Models\Ticket;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Génère l'image QR Code et la stocke sur le disque public.
     * Le QR ne contient PAS d'informations personnelles (juste un identifiant opaque).
     */
    public function generateForTicket(Ticket $ticket): string
    {
        $path = "qrcodes/{$ticket->qr_code}.svg";

        // outputBase64 = false : on veut du vrai SVG dans le fichier, pas une chaîne « data:image/svg+xml;base64,… ».
        $qrCode = new QRCode(new QROptions(['outputBase64' => false]));
        $svg = $qrCode->render($ticket->qr_code);

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    public function urlForTicket(Ticket $ticket): string
    {
        return Storage::disk('public')->url("qrcodes/{$ticket->qr_code}.svg");
    }

    /**
     * Vérifie et retourne le ticket correspondant à un contenu de QR scanné, ou null.
     */
    public function resolveTicket(string $scannedCode): ?Ticket
    {
        return Ticket::where('qr_code', $scannedCode)->first();
    }
}
