@php
    $event = $ticket->order->event;
    $time = $event->startTimeLabel() . ($event->endTimeLabel() ? ' – ' . $event->endTimeLabel() : '');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Billet {{ $ticket->ticket_number }}</title>
    <style>
        @page { margin: 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #221a4c; font-size: 12px; margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        .card { border: 1px solid #e3e0ee; }
        .head { background: #221a4c; color: #ffffff; padding: 22px 26px; }
        .title { font-size: 24px; font-weight: bold; margin: 0; }
        .sub { color: #cfcbe6; font-size: 12px; margin: 6px 0 0; }
        .pill { background: #f6a21e; color: #221a4c; font-weight: bold; font-size: 13px; padding: 6px 14px; text-align: center; }
        .body { padding: 26px; }
        .label { color: #625e80; font-size: 10px; margin: 0 0 2px; }
        .value { font-size: 14px; font-weight: bold; margin: 0 0 16px; }
        .qr-cell { text-align: center; vertical-align: middle; width: 220px; }
        .qr { border: 1px solid #e3e0ee; padding: 8px; }
        .code { color: #625e80; font-size: 11px; margin-top: 8px; letter-spacing: 1px; }
        .cut { border-top: 2px dashed #e3e0ee; }
        .foot { padding: 16px 26px; color: #625e80; font-size: 11px; line-height: 1.5; }
    </style>
</head>
<body>
    <table class="card">
        <tr>
            <td class="head">
                <table>
                    <tr>
                        <td>
                            <p class="title">{{ $event->title }}</p>
                            <p class="sub">Billet électronique · Commande {{ $ticket->order->reference }}</p>
                        </td>
                        <td style="width: 110px; vertical-align: top;"><div class="pill">{{ $ticket->ticketType->name }}</div></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="body">
                <table>
                    <tr>
                        <td style="vertical-align: top;">
                            <p class="label">Nom</p>
                            <p class="value">{{ $ticket->order->customer_name }}</p>
                            <p class="label">Date</p>
                            <p class="value">{{ $event->dateLong() }}</p>
                            <p class="label">Horaires</p>
                            <p class="value">{{ $time }}</p>
                            <p class="label">Lieu</p>
                            <p class="value">{{ $event->venue }}@if ($event->address)<br><span style="font-weight: normal; color: #625e80; font-size: 12px;">{{ $event->address }}</span>@endif</p>
                        </td>
                        <td class="qr-cell">
                            <img class="qr" src="{{ storage_path('app/public/qrcodes/' . $ticket->qr_code . '.svg') }}" width="180" height="180" alt="QR code">
                            <p class="code">{{ $ticket->ticket_number }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr><td class="cut"></td></tr>
        <tr>
            <td class="foot">
                Présentez ce QR code à l'entrée, sur votre téléphone ou imprimé. Ce billet n'est valable que pour une seule entrée : il ne peut être scanné qu'une fois.
            </td>
        </tr>
    </table>
</body>
</html>
