<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayGateService
{
    private string $baseUrl;

    private string $authToken;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.paygate.base_url'), '/');
        $this->authToken = (string) config('services.paygate.auth_token');
    }

    /**
     * Construit l'URL de paiement hébergée PayGateGlobal (Méthode 2 : redirection FLOOZ/T-Money)
     * vers laquelle rediriger le client. Aucun appel réseau ici : on assemble juste le lien.
     */
    public function paymentUrl(Order $order): string
    {
        if (! $this->authToken) {
            throw new RuntimeException('Clé API PayGateGlobal manquante (PAYGATE_AUTH_TOKEN dans .env).');
        }

        $params = [
            'token' => $this->authToken,
            'amount' => (int) $order->total_amount,
            'description' => "Billets {$order->event->title} - {$order->reference}",
            // "identifier" est l'identifiant unique CÔTÉ E-COMMERCE : on utilise la référence de
            // commande, qui sert ensuite à retrouver la commande dans le webhook et sur la page de retour.
            'identifier' => $order->reference,
            'url' => route('payment.success', ['ref' => $order->reference]),
            'phone' => preg_replace('/\D/', '', (string) $order->customer_phone),
        ];

        return $this->baseUrl.'/v1/page?'.http_build_query($params);
    }

    /**
     * Interroge PayGateGlobal (API v2/status) pour connaître le VRAI statut d'une commande (RM07).
     * On ne fait jamais confiance au seul retour navigateur ni au contenu brut du webhook : on revérifie
     * toujours auprès de PayGateGlobal avant de générer des billets.
     */
    public function checkStatus(string $identifier): array
    {
        $response = Http::asJson()->timeout(15)->post("{$this->baseUrl}/api/v2/status", [
            'auth_token' => $this->authToken,
            'identifier' => $identifier,
        ]);

        return $response->json() ?? [];
    }

    /**
     * Code de statut PayGateGlobal : 0 = paiement réussi. (2 = en cours, 4 = expiré, 6 = annulé)
     */
    public function isSuccessful(array $status): bool
    {
        return (int) ($status['status'] ?? -1) === 0;
    }
}
