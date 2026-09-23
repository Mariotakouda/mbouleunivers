<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ScannerController extends Controller
{
    public function index(): View
    {
        return view('agent.scanner.index');
    }
}
