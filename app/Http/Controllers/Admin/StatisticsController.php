<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(): View
    {
        return view('admin.statistics.index');
    }
}
