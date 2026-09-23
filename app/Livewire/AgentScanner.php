<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\TicketScan;
use App\Services\QrCodeService;
use Livewire\Component;

class AgentScanner extends Component
{
    public string $scannedCode = '';
    public ?string $resultStatus = null; // 'valid' | 'already_used' | 'invalid' | 'cancelled'
    public ?array $ticketInfo = null;

    public function verify(QrCodeService $qrCodeService): void
    {
        $this->scannedCode = trim($this->scannedCode);

        if ($this->scannedCode === '') {
            return;
        }

        $this->reset(['resultStatus', 'ticketInfo']);

        $ticket = $qrCodeService->resolveTicket($this->scannedCode);

        if (!$ticket) {
            $this->recordScan(null, 'invalid');
            $this->resultStatus = 'invalid';
            $this->scannedCode = '';
            $this->dispatch('scan-done');
            return;
        }

        // Vérifications RM10/RM11/RM12
        if ($ticket->status === 'cancelled') {
            $this->recordScan($ticket, 'cancelled');
            $this->resultStatus = 'cancelled';
        } elseif ($ticket->status === 'used') {
            $this->recordScan($ticket, 'already_used');
            $this->resultStatus = 'already_used';
        } elseif ($ticket->order->status !== 'paid') {
            $this->recordScan($ticket, 'invalid');
            $this->resultStatus = 'invalid';
        } else {
            $ticket->markAsUsed();
            $this->recordScan($ticket, 'valid');
            $this->resultStatus = 'valid';
        }

        $this->ticketInfo = [
            'ticket_number' => $ticket->ticket_number,
            'category' => $ticket->ticketType->name,
            'event' => $ticket->order->event->title,
        ];

        $this->scannedCode = '';
        $this->dispatch('scan-done');
    }

    /** Efface le résultat affiché pour scanner le billet suivant. */
    public function resetScan(): void
    {
        $this->reset(['resultStatus', 'ticketInfo', 'scannedCode']);
        $this->dispatch('scan-done');
    }

    private function recordScan(?Ticket $ticket, string $result): void
    {
        if (!$ticket) {
            return; // pas de ticket_id valide à enregistrer (contrainte FK), on pourrait logger ailleurs si besoin
        }

        TicketScan::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'scanned_at' => now(),
            'result' => $result,
            'device_info' => request()->userAgent(),
        ]);
    }

    public function render()
    {
        return view('livewire.agent-scanner', [
            'validToday' => TicketScan::where('user_id', auth()->id())
                ->where('result', 'valid')
                ->whereDate('scanned_at', today())
                ->count(),
        ]);
    }
}
