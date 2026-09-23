<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\TicketScan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(Request $request): View
    {
        $scans = TicketScan::with('ticket.ticketType', 'user')
            ->where('user_id', $request->user()->id)
            ->latest('scanned_at')
            ->paginate(30);

        return view('agent.scans.index', compact('scans'));
    }
}
